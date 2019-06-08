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

use Seat\Web\Http\Controllers\Controller;
use WinterCo\Connector\Mumble\Models\MumbleUser;
use WinterCo\Connector\Mumble\Models\MumbleLoginHistory;
use WinterCo\Connector\Mumble\Helpers\Helper;

/**
 * Class MumbleController
 * @package WinterCo\Connector\Mumble\Http\Controllers
 */
class MumbleController extends Controller
{

    public function join() {
        $server_address = setting('winterco.mumble_connector.credentials.server_addr', true);
        if (is_null($server_address)) {
            return redirect()->route('mumble-connector.history')->with('error', 'Plugin is not fully configured. Please contact administrator.');
        }
        $group_id = auth()->user()->group->id;
        $mumble_user = MumbleUser::findOrNew($group_id);
        if (is_null($mumble_user->group_id)) $mumble_user->group_id = $group_id;
        if (is_null($mumble_user->password)) {
            $mumble_user->password = Helper::randomString(32);
            $mumble_user->save();
        }
        $groups = Helper::allowedRoles($mumble_user);
        if (sizeof($groups) == 0) {
            return redirect()->route('mumble-connector.history')->with('error', 'You are not allowed to join. Please contact administrator.');
        }
        $server_url = 'mumble://'.$group_id.':'.urlencode($mumble_user->password).'@'.$server_address.'/?version=1.2.0';
        return redirect($server_url);
    }

    public function getCredentials() {
        $server_address = setting('winterco.mumble_connector.credentials.server_addr', true);
        $group_id = auth()->user()->group->id;
        $mumble_user = MumbleUser::findOrNew($group_id);
        if (is_null($mumble_user->group_id)) $mumble_user->group_id = $group_id;
        if (is_null($mumble_user->password)) {
            $mumble_user->password = Helper::randomString(32);
            $mumble_user->save();
        }
        return [
            'server_addr' => $server_address ?: '127.0.0.1:64738',
            'username' => $group_id,
            'password' => $mumble_user->password,
        ];
    }

    public function getHistory() {
        if (request()->ajax()) {
            $group_id = auth()->user()->group->id;
            $login_history = MumbleLoginHistory::where('group_id', $group_id);
            return app('DataTables')::of($login_history)
                ->make(true);
        } else {
            return view('mumble-connector::loginhistory');
        }
    }

    public function resetPassword() {
        $group_id = auth()->user()->group->id;
        $mumble_user = MumbleUser::findOrNew($group_id);
        if (is_null($mumble_user->group_id)) $mumble_user->group_id = $group_id;
        $mumble_user->password = Helper::randomString(32);
        $mumble_user->save();
        Helper::kickUser($group_id);
        return ['ok' => true];
    }


    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getUsers()
    {
        return view('mumble-connector::users.list');
    }

    /**
     * @return mixed
     * @throws \Seat\Services\Exceptions\SettingException
     */
    public function getUsersData()
    {
        // if (is_null(setting('winterco.mumble-connector.credentials.bot_token', true)))
        //    return app('DataTables')::of(collect([]))->make(true);

        $mumble_users = MumbleUser::with('group')->get();

        return app('DataTables')::of($mumble_users)
            ->editColumn('group_id', function($row){
                return $row->group_id;
            })
            ->addColumn('user_id', function($row){
                return $row->group->main_character_id;
            })
            ->addColumn('username', function($row){
                return optional($row->group->main_character)->name ?: 'Unknown Character';
            })
            ->removeColumn('password')
            ->make(true);
    }

}
