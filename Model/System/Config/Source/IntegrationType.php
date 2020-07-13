<?php

namespace Monext\Payline\Model\System\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Monext\Payline\PaylineApi\Constants as PaylineApiConstants;

class IntegrationType implements OptionSourceInterface
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
