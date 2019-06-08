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
use WinterCo\Connector\Mumble\Models\MumbleLog;

/**
 * Class MumbleLogsController
 * @package WinterCo\Connector\Mumble\Http\Controllers
 */
class MumbleLogsController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getLogs()
    {
        $log_count = MumbleLog::count();
        return view('mumble-connector::logs.list', compact('log_count'));
    }

    /**
     * @return mixed
     */
    public function getJsonLogData()
    {
        $logs = MumbleLog::orderBy('created_at', 'desc')->get();

        return app('DataTables')::of($logs)
            ->editColumn('created_at', function($row){
                return view('mumble-connector::logs.partial.date', compact('row'));
            })
            ->rawColumns(['created_at'])
            ->make(true);
    }
}
