<?php

namespace Monext\Payline\Model\System\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Monext\Payline\PaylineApi\Constants as PaylineApiConstants;

class IntegrationType implements ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => PaylineApiConstants::INTEGRATION_TYPE_REDIRECT,
                'label' => __('payline_redirect'),
            ],
            [
                'value' => PaylineApiConstants::INTEGRATION_TYPE_WIDGET,
                'label' => __('payline_widget'),
            ],
        ];
    }
}
