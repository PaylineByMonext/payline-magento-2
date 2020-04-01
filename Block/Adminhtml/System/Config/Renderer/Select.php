<?php

namespace Monext\Payline\Block\Adminhtml\System\Config\Renderer;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\View\Element\Context;

class Select extends \Magento\Framework\View\Element\Html\Select
{
    protected $source;

    protected $config;

    public function __construct(
        Context $context,
        OptionSourceInterface $source,
        array $data = [],
        array $config = []
    )
    {
        $this->source = $source;
        $this->config = $config;
        parent::__construct($context, $data);
    }

    protected function _toHtml()
    {
        foreach ($this->source->toOptionArray() as $optionId => $option) {
            $label = $option['label'];
            if ($this->getConfigShowValueInLabel() === true && !is_array($option['label'])) {
                $label = addslashes($option['value'] . ' - ' . $option['label']);
            }
            $this->addOption($option['value'], $label);
        }
        return parent::_toHtml();
    }

    protected function getConfigShowValueInLabel()
    {
        return (bool)(isset($this->config['show_value_in_label']) ? $this->config['show_value_in_label'] : false);
    }

    /**
     * Sets name for input element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    public function setInputId($value)
    {
        return $this->setId($value);
    }
}
