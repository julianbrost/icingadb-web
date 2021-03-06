<?php

/* Icinga DB Web | (c) 2020 Icinga GmbH | GPLv2 */

namespace Icinga\Module\Icingadb\Controllers;

use Icinga\Data\Filter\Filter;
use Icinga\Data\Filter\FilterExpression;
use Icinga\Module\Icingadb\Common\CommandActions;
use Icinga\Module\Icingadb\Common\Links;
use Icinga\Module\Icingadb\Compat\FeatureStatus;
use Icinga\Module\Icingadb\Model\Host;
use Icinga\Module\Icingadb\Model\HoststateSummary;
use Icinga\Module\Icingadb\Web\Controller;
use Icinga\Module\Icingadb\Widget\ContinueWith;
use Icinga\Module\Icingadb\Widget\Detail\MultiselectQuickActions;
use Icinga\Module\Icingadb\Widget\Detail\ObjectsDetail;
use Icinga\Module\Icingadb\Widget\HostList;
use Icinga\Module\Icingadb\Widget\HostStatusBar;
use Icinga\Module\Icingadb\Widget\ShowMore;
use ipl\Orm\Compat\FilterProcessor;
use ipl\Web\Url;

class HostsController extends Controller
{
    use CommandActions;

    public function indexAction()
    {
        $this->setTitle(t('Hosts'));
        $compact = $this->view->compact;

        $db = $this->getDb();

        $hosts = Host::on($db)->with('state');

        $summary = null;
        if (! $compact) {
            $summary = HoststateSummary::on($db)->with('state');
        }

        $limitControl = $this->createLimitControl();
        $paginationControl = $this->createPaginationControl($hosts);
        $sortControl = $this->createSortControl(
            $hosts,
            [
                'host.display_name'                 => t('Name'),
                'host.state.severity desc'          => t('Severity'),
                'host.state.soft_state'             => t('Current State'),
                'host.state.last_state_change desc' => t('Last State Change')
            ]
        );
        $viewModeSwitcher = $this->createViewModeSwitcher();
        $filterControl = $this->createFilterControl($hosts, [
            $limitControl->getLimitParam(),
            $sortControl->getSortParam(),
            $viewModeSwitcher->getViewModeParam()
        ]);

        $hosts->peekAhead($compact);

        $this->filter($hosts);
        if (isset($summary)) {
            $this->filter($summary);
            yield $this->export($hosts, $summary);
        } else {
            yield $this->export($hosts);
        }

        $this->addControl($paginationControl);
        $this->addControl($sortControl);
        $this->addControl($limitControl);
        $this->addControl($viewModeSwitcher);
        $this->addControl($filterControl);
        $this->addControl(new ContinueWith($this->getFilter(), Links::hostsDetails()));

        $results = $hosts->execute();
        $hostList = (new HostList($results))
            ->setViewMode($viewModeSwitcher->getViewMode());

        $this->addContent($hostList);

        if ($compact) {
            $this->addContent(
                (new ShowMore($results, Url::fromRequest()->without(['showCompact', 'limit'])))
                    ->setAttribute('data-base-target', '_next')
                    ->setAttribute('title', sprintf(
                        t('Show all %d hosts'),
                        $hosts->count()
                    ))
            );
        }

        if (isset($summary)) {
            $this->addFooter(
                (new HostStatusBar($summary->first()))->setBaseFilter($this->getFilter())
            );
        }

        $this->setAutorefreshInterval(10);
    }

    public function detailsAction()
    {
        $this->setTitle(t('Hosts'));

        $db = $this->getDb();

        $hosts = Host::on($db)->with('state');
        $summary = HoststateSummary::on($db)->with(['state']);

        $this->filter($hosts);
        $this->filter($summary);

        $hosts->limit(3);
        $hosts->peekAhead();

        yield $this->export($hosts, $summary);

        $results = $hosts->execute();
        $summary = $summary->first();

        $downtimes = Host::on($db)->with(['downtime']);
        $downtimes->getWith()['host.downtime']->setJoinType('INNER');
        $this->filter($downtimes);
        $summary->downtimes_total = $downtimes->count();

        $comments = Host::on($db)->with(['comment']);
        $comments->getWith()['host.comment']->setJoinType('INNER');
        $this->filter($comments);
        $summary->comments_total = $comments->count();

        $this->addControl(
            (new HostList($results))
                ->setViewMode('minimal')
        );
        $this->addControl(new ShowMore(
            $results,
            Links::hosts()->setQueryString($this->getFilter()->toQueryString()),
            sprintf(t('Show all %d hosts'), $hosts->count())
        ));
        $this->addControl(
            (new MultiselectQuickActions('host', $summary))
                ->setBaseFilter($this->getFilter())
        );

        $this->addContent(
            (new ObjectsDetail('host', $summary))
                ->setBaseFilter($this->getFilter())
        );
    }

    public function fetchCommandTargets()
    {
        $db = $this->getDb();

        $hosts = Host::on($db)->with('state');

        switch ($this->getRequest()->getActionName()) {
            case 'acknowledge':
                FilterProcessor::apply(
                    Filter::matchAll([
                        new FilterExpression('state.is_problem', '=', 'y'),
                        new FilterExpression('state.is_acknowledged', '=', 'n')
                    ]),
                    $hosts
                );

                break;
        }

        $this->filter($hosts);

        return $hosts;
    }

    public function getCommandTargetsUrl()
    {
        return Links::hostsDetails()->setQueryString($this->getFilter()->toQueryString());
    }

    protected function getFeatureStatus()
    {
        return new FeatureStatus('host', HoststateSummary::on($this->getDb())->with(['state'])->first());
    }
}
