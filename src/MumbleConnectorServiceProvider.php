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

namespace WinterCo\Connector\Mumble;

use RestCord\MumbleClient;
use Seat\Services\AbstractSeatPlugin;
use WinterCo\Connector\Mumble\Commands\MumbleLogsClear;
use WinterCo\Connector\Mumble\Commands\MumbleRPCDaemon;

/**
 * Class MumbleConnectorServiceProvider
 * @package WinterCo\Connector\Mumble
 */
class MumbleConnectorServiceProvider extends AbstractSeatPlugin
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->addCommands();
        $this->addRoutes();
        $this->addViews();
        $this->addMigrations();
        $this->addPublications();
        $this->addTranslations();

        $this->configureApi();
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/Config/mumble-connector.config.php', 'mumble-connector.config');

        $this->mergeConfigFrom(
            __DIR__ . '/Config/mumble-connector.permissions.php', 'web.permissions');

        $this->mergeConfigFrom(
            __DIR__ . '/Config/package.sidebar.php', 'package.sidebar');
    }

    /**
     * Register cli commands
     */
    private function addCommands()
    {
        $this->commands([
        	MumbleLogsClear::class,
	        MumbleRPCDaemon::class,
        ]);
    }

    /**
     * Import migrations
     */
    private function addMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations/');
    }

    /**
     * Import translations
     */
    private function addTranslations()
    {
        $this->loadTranslationsFrom(__DIR__ . '/lang', 'mumble-connector');
    }

    /**
     * Import routes
     */
    private function addRoutes()
    {
        if (! $this->app->routesAreCached()) {
            include __DIR__ . '/Http/routes.php';
        }
    }

    /**
     * Register views
     */
    private function addViews()
    {
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'mumble-connector');
    }

    /**
     * Import migration and static content
     */
    private function addPublications()
    {
        $this->publishes([
            __DIR__ . '/resources/assets/css/' => public_path('web/css'),
        ]);
    }

    private function configureApi()
    {
        // ensure current annotations setting is an array of path or transform into it
        $current_annotations = config('l5-swagger.paths.annotations');
        if (! is_array($current_annotations))
            $current_annotations = [$current_annotations];

        // merge paths together and update config
        config([
            'l5-swagger.paths.annotations' => array_unique(array_merge($current_annotations, [
                __DIR__ . '/Models',
                __DIR__ . '/Http/Controllers/Api/v1',
            ])),
        ]);
    }

    /**
     * Return the plugin public name as it should be displayed into settings.
     *
     * @example SeAT Web
     *
     * @return string
     */
    public function getName(): string
    {
        return 'Mumble Connector';
    }

    /**
     * Return the plugin repository address.
     *
     * @example https://github.com/eveseat/web
     *
     * @return string
     */
    public function getPackageRepositoryUrl(): string
    {
        return 'https://gitlab.com/winterco/seat-mumble-connector';
    }

    /**
     * Return the plugin technical name as published on package manager.
     *
     * @example web
     *
     * @return string
     */
    public function getPackagistPackageName(): string
    {
        return 'seat-mumble-connector';
    }

    /**
     * Return the plugin vendor tag as published on package manager.
     *
     * @example eveseat
     *
     * @return string
     */
    public function getPackagistVendorName(): string
    {
        return 'winterco';
    }

    /**
     * Return the plugin installed version.
     *
     * @return string
     */
    public function getVersion(): string
    {
        return config('mumble-connector.config.version');
    }
}
