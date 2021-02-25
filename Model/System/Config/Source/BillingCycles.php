<?php

namespace Monext\Payline\Model\System\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class BillingCycles implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 10, 'label' => __('Daily')),
            array('value' => 20, 'label' => __('Weekly')),
            array('value' => 30, 'label' => __('Twice a month')),
            array('value' => 40, 'label' => __('Monthly'))
        );
    }
}
