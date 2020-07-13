<?php

namespace Monext\Payline\Model\System\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Payline\PaylineSDK;

class Environment implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            /*[
                'value' => PaylineSDK::ENV_DEV,
                'label' => __(PaylineSDK::ENV_DEV),
            ],*/
            [
                'value' => PaylineSDK::ENV_HOMO,
                'label' => __('payline_test'),
            ],
            [
                'value' => PaylineSDK::ENV_PROD,
                'label' => __('payline_production'),
            ]
        ];
    }
}
