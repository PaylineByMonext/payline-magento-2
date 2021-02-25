<?php

namespace Monext\Payline\Model\System\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Monext\Payline\PaylineApi\Constants as PaylineApiConstants;

class PaymentAction implements OptionSourceInterface
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
