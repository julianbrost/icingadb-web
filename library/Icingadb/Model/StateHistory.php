<?php

/* Icinga DB Web | (c) 2020 Icinga GmbH | GPLv2 */

namespace Icinga\Module\Icingadb\Model;

use Icinga\Module\Icingadb\Model\Behavior\Timestamp;
use ipl\Orm\Behaviors;
use ipl\Orm\Model;
use ipl\Orm\Relations;

/**
 * Model for table `state_history`
 *
 * Please note that using this model will fetch history entries for decommissioned services. To avoid this,
 * the query needs a `state_history.service_id IS NULL OR state_history_service.id IS NOT NULL` where.
 */
class StateHistory extends Model
{
    public function getTableName()
    {
        return 'state_history';
    }

    public function getKeyName()
    {
        return 'id';
    }

    public function getColumns()
    {
        return [
            'environment_id',
            'endpoint_id',
            'object_type',
            'host_id',
            'service_id',
            'event_time',
            'state_type',
            'soft_state',
            'hard_state',
            'attempt',
            'previous_soft_state',
            'previous_hard_state',
            'output',
            'long_output',
            'max_check_attempts',
            'check_source'
        ];
    }

    public function createBehaviors(Behaviors $behaviors)
    {
        $behaviors->add(new Timestamp([
            'event_time'
        ]));
    }

    public function createRelations(Relations $relations)
    {
        $relations->belongsTo('endpoint', Endpoint::class);
        $relations->belongsTo('environment', Environment::class);
        $relations->belongsTo('host', Host::class);
        $relations->belongsTo('service', Service::class)->setJoinType('LEFT');
    }
}
