<?php

namespace Monext\Payline\Model\System\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Monext\Payline\Helper\Constants;

class CostType implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => Constants::COST_TYPE_NO_CHARGES,
                'label' => __('No costs'),
            ],
            [
                'value' => Constants::COST_TYPE_FIXE,
                'label' => __('Fixed'),
            ],
            [
                'value' => Constants::COST_TYPE_PERCENT,
                'label' => __('Percentage'),
            ],
        ];
    }
}
