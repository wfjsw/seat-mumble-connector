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
use Seat\Eveapi\Traits\HasCompositePrimaryKey;
use Seat\Web\Models\Group;

/**
 * Class MumbleRoleGroup
 * @package WinterCo\Connector\Mumble\Models
 */
class MumbleRoleGroup extends Model
{

    use HasCompositePrimaryKey;

    /**
     * @var string
     */
    protected $table = 'winterco_mumble_connector_role_groups';

    /**
     * @var array
     */
    protected $primaryKey = [
        'group_id', 'mumble_role',
    ];

    /**
     * @var array
     */
    protected $fillable = [
        'group_id', 'mumble_role', 'enabled',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id', 'id');
    }
}
