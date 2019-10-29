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

return [
    'mumble-connect' => [
        'name'  => 'Mumble',
        # 'label' => 'mumble-connector::seat.login_history',
        'icon'  => 'fa-headphones',
        'route_segment' => 'mumble-connector',
        # 'permission' => 'mumble-connector.view',
        'entries' => [
            [
                'name'  => 'Credentials',
                'label' => 'web::seat.accounts_and_credentials',
                'icon'  => 'fa-key',
                'route' => 'mumble-connector.credentials',
            ],
            [
                'name'  => 'Access Management',
                'label' => 'web::seat.access',
                'icon'  => 'fa-shield',
                'route' => 'mumble-connector.list',
                'permission' => 'mumble-connector.security'
            ],
            [
                'name'  => 'User Mapping',
                'label' => 'mumble-connector::seat.user_mapping',
                'icon'  => 'fa-exchange',
                'route' => 'mumble-connector.users',
                'permission' => 'mumble-connector.security'
            ],
            [
                'name'       => 'Settings',
                'label'      => 'web::seat.configuration',
                'icon'       => 'fa-cogs',
                'route'      => 'mumble-connector.configuration',
                'permission' => 'mumble-connector.setup'
            ],
            [
                'name'   => 'Logs',
                'label'  => 'web::seat.log',
                'plural' => true,
                'icon'   => 'fa-list',
                'route'  => 'mumble-connector.logs',
                'permission' => 'mumble-connector.security'
            ],
        ],
    ],
];
