<?php

namespace Monext\Payline\Model\System\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Monext\Payline\PaylineApi\Constants as PaylineApiConstants;

class BillingOccurances implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 2, 'label' => __('2')),
            array('value' => 3, 'label' => __('3')),
            array('value' => 4, 'label' => __('4')),
            array('value' => 5, 'label' => __('5')),
            array('value' => 6, 'label' => __('6')),
            array('value' => 7, 'label' => __('7')),
            array('value' => 8, 'label' => __('8')),
            array('value' => 9, 'label' => __('9')),
            array('value' => 10, 'label' => __('10'))
        );
    }
}
