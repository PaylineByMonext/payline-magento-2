<?php

namespace Monext\Payline\Model\System\Config\Source\Delivery;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class used as a datasource for deliveryTime
 * @from https://payline.atlassian.net/wiki/spaces/DT/pages/28901416/Codes+-+deliveryMode
 *
 */
class Mode implements OptionSourceInterface
{
    public function toOptionArray()
    {
        return array(
            array('value' => 1, 'label' => __('Collect goods from the merchant')),
            array('value' => 2, 'label' => __('Use a network of third-party pick-up points (such as Kiala, Alveol, etc.)')),
            array('value' => 3, 'label' => __('Collect from an airport, a train station or a travel agent')),
            array('value' => 4, 'label' => __('Mail (Colissimo, UPS, DHL, etc., or any private courier)')),
            array('value' => 5, 'label' => __('Issuing an electronic ticket, downloads')),
            array('value' => 6, 'label' => __('Ship to cardholder’s billing address')),
            array('value' => 7, 'label' => __('Ship to another verified address on file with merchant')),
            array('value' => 8, 'label' => __('Ship to address that is different than the cardholder’s billing address')),
            array('value' => 9, 'label' => __('Travel and Event tickets, not shipped')),
            array('value' => 10, 'label' => __('Locker delivery (or other automated pick-up)')),
            array('value' => 999, 'label' => __('Other')),
        );
    }
}
