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

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateMumbleTables
 */
class CreateMumbleTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('winterco_mumble_connector_logs', function (Blueprint $table) {
            $table->increments('id');

            $table->string('event');
            $table->string('message');
            $table->timestamps();
        });

        Schema::create('winterco_mumble_connector_users', function (Blueprint $table) {
            $table->unsignedInteger('group_id');
            // $table->integer('mumble_id');
            // $table->string('nick');
            $table->string('password')->nullable();
            $table->timestamps();

            $table->primary('group_id', 'mumble_users_primary');
            // $table->unique('mumble_id', 'mumble_users_mumble_id_unique');

            $table->foreign('group_id', 'mumble_users_group_id_foreign')
                ->references('id')
                ->on('groups')
                ->onDelete('cascade');
        });

        Schema::create('winterco_mumble_connector_loginhistory', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('group_id');
            $table->bigInteger('session_id');
            $table->string('ip');
            $table->timestamp('login_time')->nullable();
            $table->timestamp('logout_time')->nullable();
            $table->integer('version')->nullable();
            $table->string('release')->nullable();
            $table->string('os')->nullable();
            $table->string('osversion')->nullable();
            $table->timestamps();

            $table->primary('id', 'mumble_loginhistory_primary');

            $table->foreign('group_id', 'mumble_users_group_id_foreign')
                ->references('id')
                ->on('groups')
                ->onDelete('cascade');
        });

        Schema::create('winterco_mumble_connector_temptokens', function (Blueprint $table) {
            $table->string('token');
            $table->unsignedInteger('created_by');
            $table->integer('mumble_id');
            $table->string('nick');
            $table->timestamps();

            $table->primary('token', 'mumble_temptokens_primary');
            $table->unique('mumble_id', 'mumble_temptokens_mumble_id_unique');
        });

        Schema::create('winterco_mumble_connector_role_alliances', function (Blueprint $table) {
            $table->integer('alliance_id');
            $table->string('mumble_role');
            $table->boolean('enabled');
            $table->timestamps();

            $table->primary(['alliance_id', 'mumble_role'], 'mumble_role_alliances_primary');

            $table->foreign('alliance_id', 'mumble_role_alliances_alliance_id_foreign')
                ->references('alliance_id')
                ->on('alliances')
                ->onDelete('cascade');
        });

        Schema::create('winterco_mumble_connector_role_corporations', function (Blueprint $table) {
            $table->integer('corporation_id');
            $table->string('mumble_role');
            $table->boolean('enabled');
            $table->timestamps();

            $table->primary(['corporation_id', 'mumble_role'], 'mumble_role_corporations_primary');
        });

        Schema::create('winterco_mumble_connector_role_titles', function (Blueprint $table) {
            $table->bigInteger('corporation_id');
            $table->bigInteger('title_id');
            $table->string('mumble_role');
            $table->boolean('enabled');
            $table->timestamps();

            $table->primary(['corporation_id', 'title_id', 'mumble_role'], 'mumble_role_titles_primary');

        });

        Schema::create('winterco_mumble_connector_role_roles', function (Blueprint $table) {
            $table->unsignedInteger('role_id');
            $table->string('mumble_role');
            $table->boolean('enabled');
            $table->timestamps();

            $table->primary(['role_id', 'mumble_role'], 'mumble_role_roles_primary');

            $table->foreign('role_id', 'mumble_role_roles_role_id_foreign')
                ->references('id')
                ->on('roles')
                ->onDelete('cascade');
        });

        Schema::create('winterco_mumble_connector_role_groups', function (Blueprint $table) {
            $table->unsignedInteger('group_id');
            $table->string('mumble_role');
            $table->boolean('enabled');
            $table->timestamps();

            $table->primary(['group_id', 'mumble_role'], 'mumble_role_groups_primary');

            $table->foreign('group_id', 'mumble_role_groups_group_id_foreign')
                ->references('id')
                ->on('groups')
                ->onDelete('cascade');
        });

        DB::table('winterco_mumble_connector_role_corporations')
            ->whereNotIn('corporation_id', DB::table('corporation_infos')->select('corporation_id'))
            ->delete();

        Schema::table('winterco_mumble_connector_role_corporations', function (Blueprint $table) {
            $table->bigInteger('corporation_id')->change();

            $table->foreign('corporation_id', 'mumble_role_corporations_corporation_id_foreign')
                ->references('corporation_id')
                ->on('corporation_infos')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('mumble_role_alliances');
        Schema::drop('mumble_role_corporations');
        Schema::drop('mumble_role_titles');
        Schema::drop('mumble_role_roles');
        Schema::drop('mumble_role_users');
        Schema::drop('mumble_users');
        Schema::drop('mumble_loginhistory');
        Schema::drop('mumble_temptokens');
        Schema::drop('mumble_logs');
    }
}
