<?php

/* Icinga DB Web | (c) 2020 Icinga GmbH | GPLv2 */

namespace Icinga\Module\Icingadb\Model;

use Icinga\Module\Icingadb\Model\Behavior\BoolCast;
use Icinga\Module\Icingadb\Model\Behavior\Timestamp;
use ipl\Orm\Behaviors;
use ipl\Orm\Model;
use ipl\Orm\Relations;

/**
 * Model for table `downtime_history`
 *
 * Please note that using this model will fetch history entries for decommissioned services. To avoid this,
 * the query needs a `downtime_history.service_id IS NULL OR downtime_history_service.id IS NOT NULL` where.
 */
class DowntimeHistory extends Model
{
    public function getTableName()
    {
        return 'downtime_history';
    }

    public function getKeyName()
    {
        return 'downtime_id';
    }

    public function getColumns()
    {
        return [
            'environment_id',
            'endpoint_id',
            'triggered_by_id',
            'object_type',
            'host_id',
            'service_id',
            'entry_time',
            'author',
            'cancelled_by',
            'comment',
            'is_flexible',
            'flexible_duration',
            'scheduled_start_time',
            'scheduled_end_time',
            'start_time',
            'end_time',
            'has_been_cancelled',
            'trigger_time',
            'cancel_time'
        ];
    }

    public function createBehaviors(Behaviors $behaviors)
    {
        $behaviors->add(new BoolCast([
            'is_flexible',
            'has_been_cancelled'
        ]));

        $behaviors->add(new Timestamp([
            'entry_time',
            'flexible_duration',
            'scheduled_start_time',
            'scheduled_end_time',
            'start_time',
            'end_time',
            'trigger_time',
            'cancel_time'
        ]));
    }

    public function createRelations(Relations $relations)
    {
        // @TODO(el): Add relation for triggered_by_id
        $relations->belongsTo('endpoint', Endpoint::class);
        $relations->belongsTo('environment', Environment::class);
        $relations->belongsTo('host', Host::class);
        $relations->belongsTo('service', Service::class)->setJoinType('LEFT');
    }
}
