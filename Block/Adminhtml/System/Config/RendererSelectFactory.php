<?php

namespace Monext\Payline\Block\Adminhtml\System\Config;

use Exception;
use Magento\Framework\View\Element\Html\Select;

class RendererSelectFactory
{
    protected $sources;

    /**
     * @var Select
     */
    private $class = null;

    public function __construct(
        array $sources = array()
    )
    {
        $this->sources = $sources;
    }

    /**
     * @param $type
     * @return $this
     * @throws Exception
     */
    public function create($type)
    {
        if (!in_array($type, array_keys($this->sources))) {
            throw new Exception(__('Select type not found'));
        }
        $this->class = $this->sources[$type];
        return $this;
    }

    /**
     * @return Select
     * @throws Exception
     */
    public function getClass()
    {
        if (is_null($this->class)) {
            throw new Exception(__('Class not init with create'));
        }
        return $this->class;
    }
}
