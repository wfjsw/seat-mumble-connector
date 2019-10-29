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

// Route::group([
//     'namespace' => 'WinterCo\Connector\Mumble\Http\Controllers\Api\v1',
//     'prefix' => 'api',
//     'middleware' => 'api.auth'
// ], function() {
//     Route::group(['prefix' => 'v2'], function () {
//         Route::group(['prefix' => 'mumble-connector'], function () {
//                 Route::get('/mapping', 'MumbleApiController@getMumbleMappings');
//         });
//     });
// });

Route::group([
    'namespace' => 'WinterCo\Connector\Mumble\Http\Controllers',
    'prefix' => 'mumble-connector'
], function() {

    Route::group([
        'middleware' => ['api.auth'],
        'prefix' => 'api'
    ], function () {
        Route::post('/authenticate', [
            'as' => 'mumble-connector.api.authenticate',
            'uses' => 'MumbleAPIController@authenticate',
        ]);

        Route::post('/login', [
            'as' => 'mumble-connector.api.login',
            'uses' => 'MumbleAPIController@recordLogin',
        ]);

        Route::post('/logout', [
            'as' => 'mumble-connector.api.logout',
            'uses' => 'MumbleAPIController@recordLogout',
        ]);

        Route::post('/data', [
            'as' => 'mumble-connector.api.data',
            'uses' => 'MumbleAPIController@getAllUserData',
        ]);

        Route::post('/postfp', [
            'as' => 'mumble-connector.api.postfingerprint',
            'uses' => 'MumbleAPIController@recordFingerprint',
        ]);
    });

    Route::group([
        'middleware' => ['web', 'auth', 'locale'],
    ], function() {

        Route::group([
            'middleware' => 'bouncer:mumble-connector.view',
        ], function () {

            Route::get('/server/join', [
                'as' => 'mumble-connector.server.join',
                'uses' => 'MumbleController@join',
            ]);

            Route::post('/server/credentials', [
                'as' => 'mumble-connector.server.getcredentials',
                'uses' => 'MumbleController@getCredentials',
            ]);

            Route::get('/credentials', [
                'as' => 'mumble-connector.credentials',
                'uses' => 'MumbleController@getCredentialPage',
            ]);

            Route::post('/reset', [
                'as' => 'mumble-connector.reset',
                'uses' => 'MumbleController@resetPassword',
            ]);

        });

        // Endpoints with Configuration Permission
        Route::group([
            'middleware' => 'bouncer:mumble-connector.setup',
        ], function() {

            Route::get('/configuration', [
                'as' => 'mumble-connector.configuration',
                'uses' => 'MumbleSettingsController@getConfiguration',
            ]);

            Route::get('/run/{commandName}', [
                'as' => 'mumble-connector.command.run',
                'uses' => 'MumbleSettingsController@getSubmitJob',
            ]);


            Route::post('/configuration', [
                'as' => 'mumble-connector.configuration.post',
                'uses' => 'MumbleSettingsController@postConfiguration',
            ]);

        });

        Route::group([
            'middleware' => 'bouncer:mumble-connector.create',
        ], function() {

            Route::get('/public/{mumble_role}/remove', [
                'as' => 'mumble-connector.public.remove',
                'uses' => 'MumbleJsonController@getRemovePublic',
            ]);

            Route::get('/users/{group_id}/{mumble_role}/remove', [
                'as' => 'mumble-connector.user.remove',
                'uses' => 'MumbleJsonController@getRemoveUser',
            ]);

            Route::get('/roles/{role_id}/{mumble_role}/remove', [
                'as' => 'mumble-connector.role.remove',
                'uses' => 'MumbleJsonController@getRemoveRole',
            ]);

            Route::get('/corporations/{corporation_id}/{mumble_role}/remove', [
                'as' => 'mumble-connector.corporation.remove',
                'uses' => 'MumbleJsonController@getRemoveCorporation',
            ]);

            Route::get('/corporation/{corporation_id}/{title_id}/{mumble_role}/remove', [
                'as' => 'mumble-connector.title.remove',
                'uses' => 'MumbleJsonController@getRemoveTitle',
            ]);

            Route::get('/alliances/{alliance_id}/{mumble_role}/remove', [
                'as' => 'mumble-connector.alliance.remove',
                'uses' => 'MumbleJsonController@getRemoveAlliance',
            ]);

            Route::post('/', [
                'as' => 'mumble-connector.add',
                'uses' => 'MumbleJsonController@postRelation',
            ]);

        });

        Route::group([
            'middleware' => 'bouncer:mumble-connector.security',
        ], function() {

            Route::get('/', [
                'as' => 'mumble-connector.list',
                'uses' => 'MumbleJsonController@getRelations',
            ]);

            Route::get('/logs', [
                'as' => 'mumble-connector.logs',
                'uses' => 'MumbleLogsController@getLogs',
            ]);

            Route::get('/users', [
                'as' => 'mumble-connector.users',
                'uses' => 'MumbleController@getUsers',
            ]);

            Route::group([
                'prefix' => 'json',
            ], function () {

                Route::get('/logs', [
                    'as' => 'mumble-connector.json.logs',
                    'uses' => 'MumbleLogsController@getJsonLogData',
                ]);

                Route::get('/users', [
                    'as' => 'mumble-connector.json.users',
                    'uses' => 'MumbleController@getUsersData',
                ]);

                Route::get('/users/groups', [
                    'as' => 'mumble-connector.json.user.groups',
                    'uses' => 'MumbleJsonController@getJsonUserRolesData',
                ]);

                Route::get('/users/history', [
                    'as' => 'mumble-connector.json.user.history',
                    'uses' => 'MumbleJsonController@getUserLoginHistory',
                ]);

                Route::post('/users/reset', [
                    'as' => 'mumble-connector.json.user.reset',
                    'uses' => 'MumbleJsonController@resetPassword',
                ]);

            });
        });

        Route::get('/titles', [
            'as' => 'mumble-connector.json.titles',
            'uses' => 'MumbleJsonController@getJsonTitle',
            'middleware' => 'bouncer:mumble-connector.create',
        ]);

    });

});
