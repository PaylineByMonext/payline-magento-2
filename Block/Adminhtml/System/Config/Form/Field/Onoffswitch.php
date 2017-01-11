<?php
namespace Monext\Payline\Block\Adminhtml\System\Config\Form\Field;

//class Onoffswitch extends \Magento\Framework\Data\Form\Element\Select
class Onoffswitch extends \Magento\Framework\View\Element\Html\Select
{


    /**
     * Render HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        $this->setOptions(['1' => __('X'), '0' => __('-')]);

        return parent::_toHtml();


        if (!$this->_beforeToHtml()) {
            return '';
        }


        $html = '<div class="onoffswitch">' .
//                 '<input type="checkbox" name="' . $this->getName() . '" class="onoffswitch-checkbox" id="' . preg_replace('/[\[\]]+/', '_', $this->getName()) . '" value="1" ' .
                '<input type="checkbox" name="' . $this->getName() . '" class="onoffswitch-checkbox" id="' . $this->getId() . '" value="1" ' .
//                 '#{option_extra_attr_' . self::calcOptionHash(1) . '}' .
                (($this->getValue()) ? ' checked="checked" ' : '') .
                '>' .
//                 '<label class="onoffswitch-label" for="' . preg_replace('/[\[\]]+/', '_', $this->getName()) . '">' .
//                 '<span class="onoffswitch-inner"></span>' .
//                 '<span class="onoffswitch-switch"></span>' .
//                 '</label>' .
                '</div>';

        return $html;
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


}
