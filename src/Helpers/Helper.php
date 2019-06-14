<?php

// Check Pass

/**
 * This file is part of mumble-connector and provides user synchronization between both SeAT and a Mumble Guild
 *
 * Copyright (C) 2016, 2017, 2018  Loïc Leuilliot <loic.leuilliot@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace WinterCo\Connector\Mumble\Helpers;

use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Seat\Eveapi\Models\Character\CharacterInfo;
use Seat\Eveapi\Models\Corporation\CorporationInfo;
use Seat\Eveapi\Models\RefreshToken;
use Seat\Web\Models\Group;
use WinterCo\Connector\Mumble\Models\MumbleUser;
use Illuminate\Support\Facades\Redis;

/**
 * Class Helper
 * @package WinterCo\Connector\Mumble\Helpers
 */
class Helper
{

    public const NICKNAME_LENGTH_LIMIT = 64;

    /**
     * Return true if considered as active when both mail has been confirmed in case of mail activation,
     * and no adminis account is active
     *
     * An account istrator disabled it
     *
     * @param Group $group
     * @return bool
     */
    public static function isEnabledAccount(Group $group) : bool
    {
        return ($group->users->count() == $group->users->where('active', true)->count());
    }

    /**
     * Filter character id that have a valid refresh token.
     *
     * @param Collection $characterIDs
     * @return array
     */
    public static function getEnabledKey(Collection $users) : array
    {
        // retrieve character ids with a valid refresh token
        return RefreshToken::whereIn('character_id', $users->pluck('id')->toArray())->pluck('character_id')->toArray();
    }

    /**
     * Determine all channels into which an user is allowed to be
     *
     * @param MumbleUser $mumble_user
     * @return array
     */
    public static function allowedRoles(MumbleUser $mumble_user) : array
    {
        $channels = [];

        if (! Helper::isEnabledAccount($mumble_user->group))
            return $channels;

        $enabled_character_ids = Helper::getEnabledKey($mumble_user->group->users);
        
        if (empty($enabled_character_ids))
            return $channels;

        $strict_mode = setting('winterco.mumble-connector.strict', true);
        $all_token_valid = sizeof($enabled_character_ids) == $mumble_user->group->users->count();
        if ($strict_mode && ! $all_token_valid) 
            return $channels;

        $rows = Group::join('winterco_mumble_connector_role_groups', 'winterco_mumble_connector_role_groups.group_id', '=', 'groups.id')
                    ->select('mumble_role')
                    ->where('groups.id', $mumble_user->group_id)
                    ->union(
                        // fix model declaration calling the table directly
                        DB::table('group_role')->join('winterco_mumble_connector_role_roles', 'winterco_mumble_connector_role_roles.role_id', '=',
                                        'group_role.role_id')
                                 ->where('group_role.group_id', $mumble_user->group_id)
                                 ->select('mumble_role')
                    )->union(
                        CharacterInfo::join('winterco_mumble_connector_role_corporations', 'winterco_mumble_connector_role_corporations.corporation_id', '=',
                                            'character_infos.corporation_id')
                                     ->whereIn('character_infos.character_id', $mumble_user->group->users->pluck('id')->toArray())
                                     ->select('mumble_role')
                    )->union(
                        CharacterInfo::join('character_titles', 'character_infos.character_id', '=', 'character_titles.character_id')
                                     ->join('winterco_mumble_connector_role_titles', function ($join) {
                                         $join->on('winterco_mumble_connector_role_titles.corporation_id', '=',
                                             'character_infos.corporation_id');
                                         $join->on('winterco_mumble_connector_role_titles.title_id', '=',
                                             'character_titles.title_id');
                                     })
                                     ->whereIn('character_infos.character_id', $mumble_user->group->users->pluck('id')->toArray())
                                     ->select('mumble_role')
                    )->union(
                        CharacterInfo::join('winterco_mumble_connector_role_alliances', 'winterco_mumble_connector_role_alliances.alliance_id', '=',
                                            'character_infos.alliance_id')
                                     ->whereIn('character_infos.character_id', $mumble_user->group->users->pluck('id')->toArray())
                                     ->select('mumble_role')
                    )->get();

        $channels = $rows->unique('mumble_role')->pluck('mumble_role')->toArray();

        return $channels;
    }

    /**
     * @param string $role_id
     * @param MumbleUser $mumble_user
     * @return bool
     */
    public static function isAllowedRole(int $role_id, MumbleUser $mumble_user)
    {
        return in_array($role_id, self::allowedRoles($mumble_user));
    }

    /**
     * Return a string which will be used as a Discord Guild Member Nickname
     *
     * @param MumbleUser $mumble_user
     * @return string
     * @throws \Seat\Services\Exceptions\SettingException
     */
    public static function buildNickname(MumbleUser $mumble_user): string
    {
        // retrieve a character related to the Discord relationship
        $character = $mumble_user->group->main_character;
        if (is_null($character))
            $character = $mumble_user->group->users->first()->character;

        // init the discord nickname to the character name
        $expected_nickname = $mumble_user->group->main_character->name;

        $user_nickname = \Seat\Services\Settings\Profile::get('nickname', $mumble_user->group->id);

        $expected_nickname = is_null($user_nickname) ? $expected_nickname : $user_nickname . '/' . $expected_nickname;

        // in case ticker prefix is enabled, retrieve the corporation and prepend the ticker to the nickname
        if (setting('winterco.mumble-connector.ticker', true)) {
            $corporation = CorporationInfo::find($character->corporation_id);
            $nickfmt = setting('winterco.mumble-connector.nickfmt', true) ?: '[%s] %s';
            $expected_nickname = sprintf($nickfmt, $corporation ? $corporation->ticker : '????', $expected_nickname);
        }

        return Str::limit($expected_nickname, Helper::NICKNAME_LENGTH_LIMIT, '');
    }

    public static function randomString(
        $length,
        $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
    ) {
        $str = '';
        $max = mb_strlen($keyspace, '8bit') - 1;
        if ($max < 1) {
            throw new Exception('$keyspace must be at least two characters long');
        }
        for ($i = 0; $i < $length; ++$i) {
            $str .= $keyspace[random_int(0, $max)];
        }
        return $str;
    }

    public static function kickUser(int $group_id) {
        // Redis::publish('winterco.mumble-connector.kick', $group_id);
        // app('mumble')->kickUser($group_id);
        return;
    }
}
