<?php

namespace WinterCo\Connector\Mumble\Http\Controllers;

use Seat\Web\Http\Controllers\Controller;
use WinterCo\Connector\Mumble\Models\MumbleUser;
use WinterCo\Connector\Mumble\Models\MumbleLoginHistory;
use WinterCo\Connector\Mumble\Helpers\Helper;

class MumbleAPIController extends Controller {
    public function authenticate() {
        $name = request()->input('name');
        $pw = request()->input('pw');

        if ($name == 'SuperUser') {
            return ['result' => -2];
        }

        $mumble_user = MumbleUser::find($name);
        
        if (is_null($mumble_user)) {
            return ['result' => -1];
        }

        if (is_null($mumble_user->password)) {
            return ['result' => -1];
        }

        if ($mumble_user->password != $pw) {
            return ['result' => -1];
        }

        $expected_nickname = Helper::buildNickname($mumble_user);

        $groups = Helper::allowedRoles($mumble_user);

        if (sizeof($groups) == 0) return ['result' => -1];
        
        return [
            'result' => $mumble_user->group_id,
            'newname' => $expected_nickname,
            'groups' => $groups
        ];
    }

    public function recordLogin() {
        $session_id = request()->input('session_id');
        $group_id = request()->input('group_id');
        $ip = request()->input('ip');
        $login_time = request()->input('login_time');
        $version = request()->input('version');
        $release = request()->input('release');
        $os = request()->input('os');
        $osversion = request()->input('osversion');
        
        $login_history = new MumbleLoginHistory;
        $login_history->session_id = $session_id;
        $login_history->group_id = $group_id;
        $login_history->ip = $ip;
        $login_history->login_time = $login_time;
        $login_history->version = $version;
        $login_history->release = $release;
        $login_history->os = $os;
        $login_history->osversion = $osversion;

        $login_history->save();
        return ['ok' => true];
    }

    public function recordLogout() {
        $session_id = request()->input('session_id');
        $group_id = request()->input('group_id');
        $logout_time = request()->input('logout_time');
        $login_history = MumbleLoginHistory::where('session_id', $session_id)->where('group_id', $group_id)->whereNull('logout_time')->first();
        if (is_null($login_history)) return ['ok' => false];
        $login_history->logout_time = $logout_time;
        $login_history->save();
        return ['ok' => true];
    }

    public function getAllUserData() {
        $user_data = array();
        $mumble_users = MumbleUser::all();
        foreach ($mumble_users as $mumble_user) {
            try {
                $groups = Helper::allowedRoles($mumble_user);
                if (sizeof($groups) <= 0) continue;
                $user_data[$mumble_user->group_id] = [
                    'pw' => $mumble_user->password,
                    'name' => Helper::buildNickname($mumble_user),
                    'groups' => $groups,
                ];
            } catch (\Exception $e) {
                report($e);
            }
        }
        return $user_data;
    }
}
