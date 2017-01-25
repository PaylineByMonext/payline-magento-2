<?php

namespace Monext\Payline\Model\System\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Monext\Payline\PaylineApi\Constants as PaylineApiConstants;

class WidgetDisplay implements ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => PaylineApiConstants::PAYMENT_WIDGET_DISPLAY_COLUMN,
                'label' => __(PaylineApiConstants::PAYMENT_WIDGET_DISPLAY_COLUMN),
            ],
            [
                'value' => PaylineApiConstants::PAYMENT_WIDGET_DISPLAY_TAB,
                'label' => __(PaylineApiConstants::PAYMENT_WIDGET_DISPLAY_TAB),
            ],
            /*[
                'value' => PaylineSDK::PAYMENT_WIDGET_DISPLAY_LIGHTBOX,
                'label' => __(PaylineSDK::PAYMENT_WIDGET_DISPLAY_LIGHTBOX),
            ]*/
        ];
    }
}
