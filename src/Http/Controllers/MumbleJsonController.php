<?php
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

namespace WinterCo\Connector\Mumble\Http\Controllers;

use RestCord\MumbleClient;
use Seat\Eveapi\Models\Alliances\Alliance;
use Seat\Eveapi\Models\Corporation\CorporationInfo;
use Seat\Eveapi\Models\Corporation\CorporationTitle;
use Seat\Web\Http\Controllers\Controller;
use Seat\Web\Models\Acl\Role;
use Seat\Web\Models\Group;
use WinterCo\Connector\Mumble\Helpers\Helper;
use WinterCo\Connector\Mumble\Http\Validation\AddRelation;
use WinterCo\Connector\Mumble\Http\Validation\MumbleUserShowModal;
use WinterCo\Connector\Mumble\Models\MumbleUser;
use WinterCo\Connector\Mumble\Models\MumbleLoginHistory;
use WinterCo\Connector\Mumble\Models\MumbleRole;
use WinterCo\Connector\Mumble\Models\MumbleRoleAlliance;
use WinterCo\Connector\Mumble\Models\MumbleRoleCorporation;
use WinterCo\Connector\Mumble\Models\MumbleRoleRole;
use WinterCo\Connector\Mumble\Models\MumbleRoleTitle;
use WinterCo\Connector\Mumble\Models\MumbleRoleGroup;

/**
 * Class MumbleJsonController
 * @package WinterCo\Connector\Mumble\Http\Controllers
 */
class MumbleJsonController extends Controller
{
    /**
     * @param MumbleUserShowModal $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getJsonUserRolesData(MumbleUserShowModal $request)
    {
        // TODO

        // $mumble_id = $request->input('mumble_id');

        // if (is_null(setting('winterco.mumble-connector.credentials.bot_token', true)))
        //     return response()->json([]);

        // if (is_null(setting('winterco.mumble-connector.credentials.guild_id', true)))
        //     return response()->json([]);

        // $driver = new MumbleClient([
        //     'tokenType' => 'Bot',
        //     'token'     => setting('winterco.mumble-connector.credentials.bot_token', true),
        //     'rateLimitProvider' => new RedisRateLimitProvider(),
        // ]);

        // $guild_member = $driver->guild->getGuildMember([
        //     'guild.id' => intval(setting('winterco.mumble-connector.credentials.guild_id', true)),
        //     'user.id' => intval($mumble_id),
        // ]);

        // $roles = MumbleRole::whereIn('id', $guild_member->roles)->select('id', 'name')->get();
        
        $group_id = $request->input('id');
        $mumble_user = MumbleUser::findOrFail($group_id);

        $roles = Helper::allowedRoles($mumble_user);

        return response()->json($roles);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getJsonTitle()
    {
        $corporation_id = request()->input('corporation_id');

        if (!empty($corporation_id)) {
            $titles = CorporationTitle::where('corporation_id', $corporation_id)->select('title_id', 'name')
                ->get();

            return response()->json($titles->map(
                function($item){
                    return [
                        'title_id' => $item->title_id,
                        'name' => strip_tags($item->name)
                    ];
                })
            );
        }

        return response()->json([]);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getRelations()
    {
        $group_filters = MumbleRoleGroup::all();
        $role_filters = MumbleRoleRole::all();
        $corporation_filters = MumbleRoleCorporation::all();
        $title_filters = MumbleRoleTitle::all();
        $alliance_filters = MumbleRoleAlliance::all();

        // $groups = Group::all();
        $roles = Role::orderBy('title')->get();
        // $corporations = CorporationInfo::orderBy('name')->get();
        // $alliances = Alliance::orderBy('name')->get();
        // $mumble_roles = MumbleRole::orderBy('name')->get();

        return view('mumble-connector::access.list',
            compact('group_filters', 'role_filters', 'corporation_filters',
                'title_filters', 'alliance_filters'/* , 'groups' */, 'roles'/* , 'corporations', 'alliances', 'mumble_roles' */));
    }

    //
    // Remove access
    //

    /**
     * @param $group_id
     * @param $mumble_role
     * @return \Illuminate\Http\RedirectResponse
     */
    public function getRemoveUser($group_id, $mumble_role)
    {
        $channel_user = MumbleRoleGroup::where('group_id', $group_id)
            ->where('mumble_role', $mumble_role);

        if ($channel_user != null) {
            $channel_user->delete();
            return redirect()->back()
                ->with('success', 'The Mumble relation for the user has been removed');
        }

        return redirect()->back()
            ->with('error', 'An error occurs while trying to remove the Mumble relation for the user.');
    }

    /**
     * @param $role_id
     * @param $mumble_role
     * @return \Illuminate\Http\RedirectResponse
     */
    public function getRemoveRole($role_id, $mumble_role)
    {
        $channel_role = MumbleRoleRole::where('role_id', $role_id)
            ->where('mumble_role', $mumble_role);

        if ($channel_role != null) {
            $channel_role->delete();
            return redirect()->back()
                ->with('success', 'The Mumble relation for the role has been removed');
        }

        return redirect()->back()
            ->with('error', 'An error occurs while trying to remove the Mumble relation for the role.');
    }

    /**
     * @param $corporation_id
     * @param $mumble_role
     * @return \Illuminate\Http\RedirectResponse
     */
    public function getRemoveCorporation($corporation_id, $mumble_role)
    {
        $channel_corporation = MumbleRoleCorporation::where('corporation_id', $corporation_id)
            ->where('mumble_role', $mumble_role);

        if ($channel_corporation != null) {
            $channel_corporation->delete();
            return redirect()->back()
                ->with('success', 'The Mumble relation for the corporation has been removed');
        }

        return redirect()->back()
            ->with('error', 'An error occurs while trying to remove the Mumble relation for the corporation.');
    }

    /**
     * @param $corporation_id
     * @param $title_id
     * @param $mumble_role
     * @return \Illuminate\Http\RedirectResponse
     */
    public function getRemoveTitle($corporation_id, $title_id, $mumble_role)
    {
        $channel_title = MumbleRoleTitle::where('corporation_id', $corporation_id)
            ->where('title_id', $title_id)
            ->where('mumble_role', $mumble_role);

        if ($channel_title != null) {
            $channel_title->delete();
            return redirect()->back()
                ->with('success', 'The Mumble relation for the title has been removed');
        }

        return redirect()->back()
            ->with('error', 'An error occurred while trying to remove the Mumble relation for the title.');
    }

    /**
     * @param $alliance_id
     * @param $mumble_role
     * @return \Illuminate\Http\RedirectResponse
     */
    public function getRemoveAlliance($alliance_id, $mumble_role)
    {
        $channel_alliance = MumbleRoleAlliance::where('alliance_id', $alliance_id)
            ->where('mumble_role', $mumble_role);

        if ($channel_alliance != null) {
            $channel_alliance->delete();
            return redirect()->back()
                ->with('success', 'The Mumble relation for the alliance has been removed');
        }

        return redirect()->back()
            ->with('error', 'An error occurs while trying to remove the Mumble relation for the alliance.');
    }

    public function getUserLoginHistory() {
        $group_id = request()->input('id');
        $login_history = MumbleLoginHistory::where('group_id', $group_id);
        return app('DataTables')::of($login_history)
            ->make(true);
    }

    public function resetPassword() {
        $group_id = request()->input('id');
        $mumble_user = MumbleUser::findOrNew($group_id);
        $mumble_user->password = Helper::randomString(32);
        $mumble_user->save();
        Helper::kickUser($group_id);
        return ['ok' => true];
    }

    //
    // Grant access
    //

    /**
     * @param AddRelation $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postRelation(AddRelation $request)
    {
        $group_id = $request->input('mumble-group-id');
        $role_id = $request->input('mumble-role-id');
        $corporation_id = $request->input('mumble-corporation-id');
        $title_id = $request->input('mumble-title-id');
        $alliance_id = $request->input('mumble-alliance-id');
        $mumble_role = $request->input('mumble-mumble-role');

        // use a single post route in order to create any kind of relation
        // value are user, role, corporation or alliance
        switch ($request->input('mumble-type')) {
            case 'group':
                return $this->postGroupRelation($mumble_role, $group_id);
            case 'role':
                return $this->postRoleRelation($mumble_role, $role_id);
            case 'corporation':
                return $this->postCorporationRelation($mumble_role, $corporation_id);
            case 'title':
                return $this->postTitleRelation($mumble_role, $corporation_id, $title_id);
            case 'alliance':
                return $this->postAllianceRelation($mumble_role, $alliance_id);
            default:
                return redirect()->back()
                    ->with('error', 'Unknown relation type');
        }
    }

    //
    // Helper methods
    //

    /**
     * @param $mumble_role
     * @param $group_id
     * @return \Illuminate\Http\RedirectResponse
     */
    private function postGroupRelation($mumble_role, $group_id)
    {
        $relation = MumbleRoleGroup::where('mumble_role', '=', $mumble_role)
            ->where('group_id', '=', $group_id)
            ->get();

        if ($relation->count() == 0) {
            MumbleRoleGroup::create([
                'group_id' => $group_id,
                'mumble_role' => $mumble_role,
                'enabled' => true
            ]);

            return redirect()->back()
                ->with('success', 'New Mumble user relation has been created');
        }

        return redirect()->back()
            ->with('error', 'This relation already exists');
    }

    /**
     * @param $mumble_role
     * @param $role_id
     * @return \Illuminate\Http\RedirectResponse
     */
    private function postRoleRelation($mumble_role, $role_id)
    {
        $relation = MumbleRoleRole::where('role_id', '=', $role_id)
            ->where('mumble_role', '=', $mumble_role)
            ->get();

        if ($relation->count() == 0) {
            MumbleRoleRole::create([
                'role_id' => $role_id,
                'mumble_role' => $mumble_role,
                'enabled' => true
            ]);

            return redirect()->back()
                ->with('success', 'New Mumble role relation has been created');
        }

        return redirect()->back()
            ->with('error', 'This relation already exists');
    }

    /**
     * @param $mumble_role
     * @param $corporation_id
     * @return \Illuminate\Http\RedirectResponse
     */
    private function postCorporationRelation($mumble_role, $corporation_id)
    {
        $relation = MumbleRoleCorporation::where('corporation_id', '=', $corporation_id)
            ->where('mumble_role', '=', $mumble_role)
            ->get();

        if ($relation->count() == 0) {
            MumbleRoleCorporation::create([
                'corporation_id' => $corporation_id,
                'mumble_role' => $mumble_role,
                'enabled' => true
            ]);

            return redirect()->back()
                ->with('success', 'New Mumble corporation relation has been created');
        }

        return redirect()->back()
            ->with('error', 'This relation already exists');
    }

    /**
     * @param $mumble_role
     * @param $corporation_id
     * @param $title_id
     * @return \Illuminate\Http\RedirectResponse
     */
    private function postTitleRelation($mumble_role, $corporation_id, $title_id)
    {
        $relation = MumbleRoleTitle::where('corporation_id', '=', $corporation_id)
            ->where('title_id', '=', $title_id)
            ->where('mumble_role', '=', $mumble_role)
            ->get();

        if ($relation->count() == 0) {
            MumbleRoleTitle::create([
                'corporation_id' => $corporation_id,
                'title_id' => $title_id,
                'mumble_role' => $mumble_role,
                'enabled' => true
            ]);

            return redirect()->back()
                ->with('success', 'New Mumble title relation has been created');
        }

        return redirect()->back()
            ->with('error', 'This relation already exists');
    }

    /**
     * @param $mumble_role
     * @param $alliance_id
     * @return \Illuminate\Http\RedirectResponse
     */
    private function postAllianceRelation($mumble_role, $alliance_id)
    {
        $relation = MumbleRoleAlliance::where('alliance_id', '=', $alliance_id)
            ->where('mumble_role', '=', $mumble_role)
            ->get();

        if ($relation->count() == 0) {
            MumbleRoleAlliance::create([
                'alliance_id' => $alliance_id,
                'mumble_role' => $mumble_role,
                'enabled' => true
            ]);

            return redirect()->back()
                ->with('success', 'New Mumble alliance relation has been created');
        }

        return redirect()->back()
            ->with('error', 'This relation already exists');
    }
}
