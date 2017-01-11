<?php

namespace Monext\Payline\Model\System\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Payment\Model\Method\AbstractMethod;
use Monext\Payline\PaylineApi\Constants as PaylineApiConstants;

class PaymentAction implements ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => PaylineApiConstants::PAYMENT_ACTION_AUTHORIZATION,
                'label' => __('Authorize'),
            ],
            [
                'value' => PaylineApiConstants::PAYMENT_ACTION_AUTHORIZATION_CAPTURE,
                'label' => __('Authorize and Capture'),
            ]
        ];
    }
}
