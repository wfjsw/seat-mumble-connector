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

namespace WinterCo\Connector\Mumble\Models;

use Illuminate\Database\Eloquent\Model;
use Seat\Web\Models\Group;
use WinterCo\Connector\Mumble\Helpers\Helper;

/**
 * Class MumbleUser
 * @package WinterCo\Connector\Mumble\Models
 *
 * @SWG\Definition(
 *     description="SeAT to Mumble User mapping model",
 *     title="Mumble User model",
 *     type="object"
 * )
 *
 * @SWG\Property(
 *     format="int",
 *     description="ID",
 *     property="group_id",
 * )
 *
 * @SWG\Property(
 *     format="int",
 *     description="Mumble Unique ID",
 *     property="mumble_id",
 * )
 *
 * @SWG\Property(
 *     format="string",
 *     description="Mumble user nickname",
 *     property="nick",
 * )
 */
class MumbleUser extends Model
{
    /**
     * @var string
     */
    protected $table = 'winterco_mumble_connector_users';

    /**
     * @var string
     */
    protected $primaryKey = 'group_id';

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var array
     */
    protected $fillable = [
        'group_id', 'password', 
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id', 'id');
    }

    public function nick() {
        return Helper::buildNickname($this);
    }
}
