<?php

namespace Monext\Payline\Model\System\Config\Source\Delivery;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class used as a datasource for deliveryTime
 * @from https://docs.payline.com/display/DT/Codes+-+deliveryTime
 *
 */
class Time implements OptionSourceInterface
{
    public function toOptionArray()
    {
        return array(
            array('value' => 1, 'label' => __('Express')),
            array('value' => 2, 'label' => __('Standard')),
            array('value' => 3, 'label' => __('Electronic Delivery')),
            array('value' => 4, 'label' => __('Same day shipping')),
            array('value' => 5, 'label' => __('Overnight shipping')),
            array('value' => 6, 'label' => __('Two-day or more shipping')),
        );
    }
}
