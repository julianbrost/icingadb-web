<?php

/* Icinga DB Web | (c) 2020 Icinga GmbH | GPLv2 */

namespace Icinga\Module\Icingadb\Common;

use Icinga\Module\Icingadb\Compat\CompatBackend;
use Icinga\Module\Icingadb\Compat\CompatHost;
use Icinga\Module\Icingadb\Compat\CompatObjects;
use Icinga\Module\Icingadb\Compat\CompatService;
use Icinga\Module\Monitoring\Forms\Command\Object\AcknowledgeProblemCommandForm;
use Icinga\Module\Monitoring\Forms\Command\Object\AddCommentCommandForm;
use Icinga\Module\Monitoring\Forms\Command\Object\CheckNowCommandForm;
use Icinga\Module\Monitoring\Forms\Command\Object\DeleteCommentCommandForm;
use Icinga\Module\Monitoring\Forms\Command\Object\DeleteCommentsCommandForm;
use Icinga\Module\Monitoring\Forms\Command\Object\DeleteDowntimeCommandForm;
use Icinga\Module\Monitoring\Forms\Command\Object\DeleteDowntimesCommandForm;
use Icinga\Module\Monitoring\Forms\Command\Object\ObjectsCommandForm;
use Icinga\Module\Monitoring\Forms\Command\Object\ProcessCheckResultCommandForm;
use Icinga\Module\Monitoring\Forms\Command\Object\RemoveAcknowledgementCommandForm;
use Icinga\Module\Monitoring\Forms\Command\Object\ScheduleHostCheckCommandForm;
use Icinga\Module\Monitoring\Forms\Command\Object\ScheduleHostDowntimeCommandForm;
use Icinga\Module\Monitoring\Forms\Command\Object\ScheduleServiceCheckCommandForm;
use Icinga\Module\Monitoring\Forms\Command\Object\ScheduleServiceDowntimeCommandForm;
use Icinga\Module\Monitoring\Forms\Command\Object\SendCustomNotificationCommandForm;
use Icinga\Module\Monitoring\Forms\Command\Object\ToggleObjectFeaturesCommandForm;
use ipl\Html\HtmlString;
use ipl\Orm\Model;
use ipl\Orm\Query;
use ipl\Web\Url;
use LogicException;

/**
 * Trait CommandActions
 *
 * @method mixed fetchCommandTargets() Fetch command targets, \ipl\Orm\Query or \ipl\Orm\Model[]
 * @method object getFeatureStatus() Get status of toggleable features
 * @method Url getCommandTargetsUrl() Get url to view command targets, used as redirection target
 */
trait CommandActions
{
    /** @var Query $commandTargets */
    protected $commandTargets;

    /** @var Model $commandTargetModel */
    protected $commandTargetModel;

    /**
     * Get command targets
     *
     * @return Query|Model[]
     */
    protected function getCommandTargets()
    {
        if (! isset($this->commandTargets)) {
            if (method_exists($this, 'fetchCommandTargets')) {
                $this->commandTargets = $this->fetchCommandTargets();
            } else {
                throw new LogicException('You must implement fetchCommandTargets() first');
            }
        }

        return $this->commandTargets;
    }

    /**
     * Get the model of the command targets
     *
     * @return Model
     */
    protected function getCommandTargetModel()
    {
        if (! isset($this->commandTargetModel)) {
            $commandTargets = $this->getCommandTargets();
            if (is_array($commandTargets) && !empty($commandTargets)) {
                $this->commandTargetModel = $commandTargets[0];
            } else {
                $this->commandTargetModel = $commandTargets->getModel();
            }
        }

        return $this->commandTargetModel;
    }

    /**
     * Get command objects
     *
     * @return CompatObjects
     */
    protected function getCommandObjects()
    {
        switch ($this->getCommandTargetModel()->getTableName()) {
            case 'host':
                $compatClass = CompatHost::class;
                break;
            case 'service':
                $compatClass = CompatService::class;
                break;
            default:
                throw new LogicException('Only hosts and services are supported');
        }

        return new CompatObjects($this->getCommandTargets(), $compatClass);
    }

    /**
     * Handle and register the given command form
     *
     * @param string|ObjectsCommandForm $form
     */
    protected function handleCommandForm($form)
    {
        if (is_string($form)) {
            $form = new $form([
                'backend'   => new CompatBackend(),
                'objects'   => $this->getCommandObjects()
            ]);
        }

        $form->setRedirectUrl($this->getCommandTargetsUrl());

        $form->handleRequest();
        $this->addContent(HtmlString::create($form->render()));
    }

    public function acknowledgeAction()
    {
        $this->assertPermission('monitoring/command/acknowledge-problem');
        $this->setTitle(t('Acknowledge Problem'));
        $this->handleCommandForm(AcknowledgeProblemCommandForm::class);
    }

    public function addCommentAction()
    {
        $this->assertPermission('monitoring/command/comment/add');
        $this->setTitle(t('Add Comment'));
        $this->handleCommandForm(AddCommentCommandForm::class);
    }

    public function checkNowAction()
    {
        $this->assertPermission('monitoring/command/schedule-check');
        $this->handleCommandForm(CheckNowCommandForm::class);
    }

    public function deleteCommentAction()
    {
        $this->assertPermission('monitoring/command/comment/delete');
        $this->handleCommandForm(DeleteCommentCommandForm::class);
    }

    public function deleteCommentsAction()
    {
        $this->assertPermission('monitoring/command/comment/delete');
        $this->handleCommandForm(DeleteCommentsCommandForm::class);
    }

    public function deleteDowntimeAction()
    {
        $this->assertPermission('monitoring/command/downtime/delete');
        $this->handleCommandForm(DeleteDowntimeCommandForm::class);
    }

    public function deleteDowntimesAction()
    {
        $this->assertPermission('monitoring/command/downtime/delete');
        $this->handleCommandForm(DeleteDowntimesCommandForm::class);
    }

    public function processCheckresultAction()
    {
        $this->assertPermission('monitoring/command/process-check-result');
        $this->setTitle(t('Submit Passive Check Result'));
        $this->handleCommandForm(ProcessCheckResultCommandForm::class);
    }

    public function removeAcknowledgementAction()
    {
        $this->assertPermission('monitoring/command/remove-acknowledgement');
        $this->handleCommandForm(RemoveAcknowledgementCommandForm::class);
    }

    public function scheduleCheckAction()
    {
        $this->assertPermission('monitoring/command/schedule-check');

        switch ($this->getCommandTargetModel()->getTableName()) {
            case 'host':
                $this->setTitle(t('Reschedule Host Check'));
                $this->handleCommandForm(ScheduleHostCheckCommandForm::class);
                break;
            case 'service':
                $this->setTitle(t('Reschedule Service Check'));
                $this->handleCommandForm(ScheduleServiceCheckCommandForm::class);
                break;
        }
    }

    public function scheduleDowntimeAction()
    {
        $this->assertPermission('monitoring/command/downtime/schedule');

        switch ($this->getCommandTargetModel()->getTableName()) {
            case 'host':
                $this->setTitle(t('Schedule Host Downtime'));
                $this->handleCommandForm(ScheduleHostDowntimeCommandForm::class);
                break;
            case 'service':
                $this->setTitle(t('Schedule Service Downtime'));
                $this->handleCommandForm(ScheduleServiceDowntimeCommandForm::class);
                break;
        }
    }

    public function sendCustomNotificationAction()
    {
        $this->assertPermission('monitoring/command/send-custom-notification');
        $this->setTitle(t('Send Custom Notification'));
        $this->handleCommandForm(SendCustomNotificationCommandForm::class);
    }

    public function toggleFeaturesAction()
    {
        $commandObjects = $this->getCommandObjects();
        $form = new ToggleObjectFeaturesCommandForm([
            'backend'   => new CompatBackend(),
            'objects'   => $commandObjects
        ]);

        if (count($commandObjects) > 1) {
            if (! method_exists($this, 'getFeatureStatus')) {
                throw new LogicException('You must implement getFeatureStatus() first');
            }

            $form->load($this->getFeatureStatus());
        } else {
            foreach ($commandObjects as $object) {
                $form->load($object);
            }
        }

        $this->handleCommandForm($form);
    }
}
