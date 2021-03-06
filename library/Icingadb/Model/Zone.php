<?php

/* Icinga DB Web | (c) 2020 Icinga GmbH | GPLv2 */

namespace Icinga\Module\Icingadb\Model;

use ipl\Orm\Model;
use ipl\Orm\Relations;

class Zone extends Model
{
    public function getTableName()
    {
        return 'zone';
    }

    public function getKeyName()
    {
        return 'id';
    }

    public function getColumns()
    {
        return [
            'environment_id',
            'name_checksum',
            'properties_checksum',
            'name',
            'name_ci',
            'is_global',
            'parent_id',
            'depth'
        ];
    }

    public function createRelations(Relations $relations)
    {
        $relations->belongsTo('environment', Environment::class);

        $relations->hasMany('comment', Comment::class);
        $relations->hasMany('downtime', Downtime::class);
        $relations->hasMany('endpoint', Endpoint::class);
        $relations->hasMany('eventcommand', Eventcommand::class);
        $relations->hasMany('host', Host::class);
        $relations->hasMany('hostgroup', Hostgroup::class);
        $relations->hasMany('notification', Notification::class);
        $relations->hasMany('service', Service::class);
        $relations->hasMany('servicegroup', Servicegroup::class);
        $relations->hasMany('timeperiod', Timeperiod::class);
        $relations->hasMany('user', User::class);
        $relations->hasMany('usergroup', Usergroup::class);

        // TODO: Decide how to establish recursive relations
    }
}
