<?php

namespace Monext\Payline\Model\System\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Monext\Payline\PaylineApi\Constants as PaylineApiConstants;

class PaymentWorkflow implements ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => PaylineApiConstants::PAYMENT_WORKFLOW_REDIRECT,
                'label' => __('Redirect'),
            ],
            [
                'value' => PaylineApiConstants::PAYMENT_WORKFLOW_WIDGET,
                'label' => __('Widget'),
            ]
        ];
    }
}
