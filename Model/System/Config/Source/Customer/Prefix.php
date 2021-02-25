<?php

namespace Monext\Payline\Model\System\Config\Source\Customer;

use Magento\Customer\Model\Options;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class used as a datasource for deliveryTime
 * @from https://docs.payline.com/display/DT/Codes+-+Title
 * @note only codes 3 and 4 are currently accepted on Payline side
 * @note this is a discrepancy with the documentation and it will be fixed
 *
 */
class Prefix implements OptionSourceInterface
{
    /**
     * @var Options
     */
    protected $options;

    public function __construct(Options $options)
    {
        $this->options = $options;
    }

    public function toOptionArray()
    {
        $options = $this->options->getNamePrefixOptions();
        if ($options === false) {
            $options = array(array('value' => 0, 'label' => __('Please configure field customer/address/prefix_options')));
        } else {
            $options = array_map(function ($value, $key) {
                return array('value' => $key, 'label' => $value);
            }, array_values($options), array_keys($options));
        }
        return $options;
    }
}
