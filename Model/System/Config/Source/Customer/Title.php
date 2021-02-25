<?php

namespace Monext\Payline\Model\System\Config\Source\Customer;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class used as a datasource for deliveryTime
 * @from https://docs.payline.com/display/DT/Codes+-+Title
 * @note only codes 3 and 4 are currently accepted on Payline side
 * @note this is a discrepancy with the documentation and it will be fixed
 *
 */
class Title implements OptionSourceInterface
{
    public function toOptionArray()
    {
        return array(
//            array('value' => 1, 'label' => __('Mrs')),
//            array('value' => 2, 'label' => __('Ladies')),
            array('value' => 3, 'label' => __('Miss')),
            array('value' => 4, 'label' => __('Mr. / Mister')),
//            array('value' => 5, 'label' => __('Gentlemen')),
//            array('value' => 6, 'label' => __('Widow')),
//            array('value' => 7, 'label' => __('Dr. / Doctor')),
//            array('value' => 8, 'label' => __('Doctors')),
//            array('value' => 9, 'label' => __('Pr. / Professor')),
//            array('value' => 10, 'label' => __('Mr. or Mrs. (Lawyer)')),
//            array('value' => 11, 'label' => __('Mr. or Mrs. (Lawyers)')),
//            array('value' => 12, 'label' => __('His Eminence'))
        );
    }
}
