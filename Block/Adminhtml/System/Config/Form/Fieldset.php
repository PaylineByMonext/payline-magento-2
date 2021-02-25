<?php

namespace Monext\Payline\Block\Adminhtml\System\Config\Form;

use Magento\Config\Block\System\Config\Form\Fieldset as BaseFieldset;

class Fieldset extends BaseFieldset
{
    /**
     * Return header comment part of html for fieldset
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getHeaderCommentHtml($element)
    {
        $groupConfig = $element->getGroup();

        if (empty($groupConfig['help_url']) || !$element->getComment()) {
            return parent::_getHeaderCommentHtml($element);
        }

        $html = '<div class="comment">' .
            __($element->getComment(), $groupConfig['help_url'])
        . '</div>';

        return $html;
    }
}
