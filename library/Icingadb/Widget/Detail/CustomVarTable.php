<?php

/* Icinga DB Web | (c) 2020 Icinga GmbH | GPLv2 */

namespace Icinga\Module\Icingadb\Widget\Detail;

use ipl\Html\BaseHtmlElement;
use ipl\Html\Html;

class CustomVarTable extends BaseHtmlElement
{
    protected $data;

    protected $level = 0;

    protected $tag = 'table';

    protected $defaultAttributes = [
        'data-visible-height'   => 200,
        'class'                 => 'custom-var-table name-value-table collapsible'
    ];

    public function __construct($data)
    {
        $this->data = $data;
    }

    protected function addRow($name, $value)
    {
        $this->add(Html::tag('tr', ['class' => "level-{$this->level}"], [
            Html::tag('th', $name),
            Html::tag('td', $value)
        ]));
    }

    protected function renderVar($name, $value)
    {
        switch (true) {
            case is_array($value):
                return $this->renderArray($name, $value);
            case is_object($value):
                return $this->renderObject($name, $value);
            case $value === null:
            default:
                return $this->renderScalar($name, $value);
        }
    }

    protected function renderArray($name, array $array)
    {
        $numItems = count($array);
        $this->addRow("$name (Array)", sprintf(tp('%d item', '%d items', $numItems), $numItems));

        ++$this->level;

        foreach ($array as $key => $value) {
            $this->renderVar("[$key]", $value);
        }

        --$this->level;
    }

    protected function renderObject($name, $object)
    {
        $numItems = count(get_object_vars($object));
        $this->addRow($name, sprintf(tp('%d item', '%d items', $numItems), $numItems));

        ++$this->level;

        foreach ($object as $key => $value) {
            $this->renderVar($key, $value);
        }

        --$this->level;
    }

    protected function renderScalar($name, $value)
    {
        $this->addRow($name, $value);
    }

    protected function assemble()
    {
        foreach ($this->data as $customvar) {
            $this->renderVar($customvar->name, $customvar->value);
        }
    }
}
