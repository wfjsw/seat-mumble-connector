<?php

namespace WinterCo\Connector\Mumble\Helpers;

require_once 'Ice.php';
require_once __DIR__.'/../../Murmur.php';

use WinterCo\Connector\Mumble\Models\MumbleUserHistory;

class MumbleRpc {

    private $server;

    public function __constructor() {
        $ip = setting('winterco.mumble-connector.credentials.ice_endpoint_ip', true);
        $port = setting('winterco.mumble-connector.credentials.ice_endpoint_port', true) ?: 6502;

        $initData = new \Ice\InitializationData;
		$initData->properties = \Ice\createProperties();
		$initData->properties->setProperty('Ice.ImplicitContext', 'Shared');
		$initData->properties->setProperty('Ice.Default.EncodingVersion', '1.0');

        $ICE = \Ice\initialize($initData);

        $key = setting('winterco.mumble-connector.credentials.ice_key', true);
        if (!is_null($key)) {
            $ICE->getImplicitContext()->put('secret', $key);
        }

        $meta = \Murmur\MetaPrxHelper::checkedCast($ICE->stringToProxy('Meta:tcp -h '.$ip.' -p '.$port));
        
        $this->server = $meta->getServer(1);
    }

    public function kickUser(int $group_id) {
        $user_history = MumbleUserHistory::where('group_id', $group_id)->whereNull('logout_time')->first();
        if (is_null($user_history)) return;
        return $this->server->kickUser($user_history->session_id);
    }
}
