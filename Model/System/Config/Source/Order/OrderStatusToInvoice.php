<?php

namespace Monext\Payline\Model\System\Config\Source\Order;

use Magento\Sales\Model\Config\Source\Order\Status;

class OrderStatusToInvoice extends Status
{
    private $options;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options === null) {
            $options = parent::toOptionArray();
            array_shift($options);

            foreach ($options as $key => $option) {
                $options[$key]['label'] = __('When order status is "%1"', $option['label']);
            }

            array_unshift($options,array(
                'value' => '',
                'label' => __('No')
            ));

            array_unshift($options,array(
                'value' => 'return',
                'label' => __('Back to the shop')
            ));

            $this->options = $options;
        }

        return $this->options;
    }
}
