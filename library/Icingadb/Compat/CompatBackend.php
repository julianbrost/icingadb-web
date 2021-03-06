<?php

/* Icinga DB Web | (c) 2020 Icinga GmbH | GPLv2 */

namespace Icinga\Module\Icingadb\Compat;

use Icinga\Module\Monitoring\Backend\MonitoringBackend;

class CompatBackend extends MonitoringBackend
{
    /**
     * @param string $name Ignored
     * @param mixed $config Ignored
     */
    public function __construct()
    {
    }

    /**
     * @param string $programVersion Ignored
     * @return true
     */
    public function isIcinga2($_ = null)
    {
        return true;
    }

    /**
     * @return string "2.12", hardcoded
     */
    public function getProgramVersion()
    {
        return '2.12';
    }
}
