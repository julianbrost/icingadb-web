<?php

/* Icinga DB Web | (c) 2020 Icinga GmbH | GPLv2 */

namespace Icinga\Module\Icingadb\Widget;

use Icinga\Data\Filter\Filter;
use ipl\Html\BaseHtmlElement;
use ipl\Html\Html;

/**
 * Base class for list items
 */
abstract class BaseListItem extends BaseHtmlElement
{
    protected $baseAttributes = ['class' => 'list-item'];

    /** @var object The associated list item */
    protected $item;

    /** @var BaseItemList The list where the item is part of */
    protected $list;

    protected $tag = 'li';

    /**
     * Create a new list item
     *
     * @param object       $item
     * @param BaseItemList $list
     */
    public function __construct($item, BaseItemList $list)
    {
        $this->item = $item;
        $this->list = $list;

        $this->addAttributes($this->baseAttributes);

        $this->init();
    }

    abstract protected function assembleHeader(BaseHtmlElement $header);

    abstract protected function assembleMain(BaseHtmlElement $main);

    protected function assembleCaption(BaseHtmlElement $caption)
    {
    }

    protected function assembleTitle(BaseHtmlElement $title)
    {
    }

    protected function assembleVisual(BaseHtmlElement $visual)
    {
    }

    protected function createCaption()
    {
        $caption = Html::tag('section', ['class' => 'caption']);

        $this->assembleCaption($caption);

        return $caption;
    }

    protected function createHeader()
    {
        $header = Html::tag('header');

        $this->assembleHeader($header);

        return $header;
    }

    protected function createMain()
    {
        $main = Html::tag('div', ['class' => 'main']);

        $this->assembleMain($main);

        return $main;
    }


    protected function createTimestamp()
    {
    }

    protected function createTitle()
    {
        $title = HTML::tag('div', ['class' => 'title']);

        $this->assembleTitle($title);

        return $title;
    }

    protected function createVisual()
    {
        $visual = Html::tag('div', ['class' => 'visual']);

        $this->assembleVisual($visual);

        return $visual;
    }

    /**
     * Initialize the list item
     *
     * If you want to adjust the list item after construction, override this method.
     */
    protected function init()
    {
    }

    protected function setMultiselectFilter(Filter $filter)
    {
        $this->addAttributes(['data-icinga-multiselect-filter' => '(' . $filter->toQueryString() . ')']);

        return $this;
    }

    protected function setDetailFilter(Filter $filter)
    {
        $this->addAttributes(['data-icinga-detail-filter' => $filter->toQueryString()]);

        return $this;
    }

    protected function assemble()
    {
        $this->add([
            $this->createVisual(),
            $this->createMain(),
        ]);
    }
}
