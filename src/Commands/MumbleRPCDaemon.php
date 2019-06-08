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

require_once 'Ice.php';
require_once __DIR__.'/../../Murmur.php';

namespace WinterCo\Connector\Mumble\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use WinterCo\Connector\Mumble\Models\MumbleUser;
use WinterCo\Connector\Mumble\Helpers\Helper;
use Illuminate\Support\Facades\Redis;

class Authenticator {
    public function authenticate(string $name, string $pw, $certificates, string $certhash, bool $certstrong, string &$newname, &$groups) : int {
        if ($name == 'SuperUser') {
            return -2;
        }

        $mumble_user = MumbleUser::find($name);
        
        if (is_null($mumble_user)) {
            return -1;
        }

        if (is_null($mumble_user->password)) {
            return -3;
        }

        if ($mumble_user->password != $pw) {
            return -1;
        }

        $expected_nickname = Helper::buildNickname($mumble_user);

        $groups = Helper::allowedGroups($mumble_user);
        
        return $mumble_user->group_id;
    }

    public function getInfo(int $id, &$info) : bool {
        return false;
    }

    public function nameToId(string $name) : int {
        return -2;
    }

    public function idToName(int $id) : string {
        return '';
    }
    
    public function idToTexture(int $id) {
        return [];
    }
}

/**
 * Class MumbleRPCDaemon
 * @package WinterCo\Connector\Mumble\Commands
 */
class MumbleRPCDaemon extends Command
{

    /**
     * @var string
     */
    protected $signature = 'mumble:daemon';

    /**
     * @var string
     */
    protected $description = 'Run a ICE client used to provide authentication service for Mumble server.';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        // SyncRole::dispatch();

        // $this->info('A synchronization job has been queued in order to update mumble roles.');

        // Run ICE Daemon here.

        if (is_null(setting('winterco.mumble-connector.credentials.ice_endpoint_ip', true))) {
            return $this->info('Please fill ICE Endpoint in Settings page.');
        }

        $ip = setting('winterco.mumble-connector.credentials.ice_endpoint_ip', true);
        $port = setting('winterco.mumble-connector.credentials.ice_endpoint_port', true) ?: 6502;

        $initData = new \Ice\InitializationData;
		$initData->properties = \Ice::createProperties();
		$initData->properties->setProperty('Ice.ImplicitContext', 'Shared');
		$initData->properties->setProperty('Ice.Default.EncodingVersion', '1.0');

        $ICE = \Ice::initialize($initData);

        $key = setting('winterco.mumble-connector.credentials.ice_key', true);
        if (!is_null($key)) {
            $ICE->getImplicitContext()->put('secret', $key);
        }

        $meta = \Murmur\MetaPrxHelper::checkedCast($ICE->stringToProxy('Meta:tcp -h '.$ip.' -p '.$port));

        $adapter = \Ice\Communicator::createObjectAdapterWithEndpoints('Callback.Client', 'tcp -h '.$ip);
        $adapter->activate();
        
        $servers = $meta->getBootServers();

        foreach ($servers as $server) {
            $authenticator = new Authenticator();
            $authenticatorprx = \Murmur\ServerAuthenticatorPrxHelper::uncheckedCast($adapter->addWithUUID($authenticator));
            $server->setAuthenticator($authenticatorprx);
        }

        Redis::subscribe(['winterco.mumble-connector.kick'], function ($message) use ($servers) {
            foreach ($servers as $server) {
                try {
                    // Do Nothing
                } catch (\Exception $e) {
                    report($e);
                }
            }
        });

        $ICE->waitForShutdown();
        $ICE->destroy();
    }

}
