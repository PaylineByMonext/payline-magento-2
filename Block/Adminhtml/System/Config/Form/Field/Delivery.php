<?php

namespace Monext\Payline\Block\Adminhtml\System\Config\Form\Field;

use Magento\Framework\Exception\LocalizedException;
use Monext\Payline\Block\Adminhtml\System\Config\Form\Field\FieldArray\AbstractFieldArray;

class Delivery extends AbstractFieldArray
{
    /**
     * Prepare to render
     *
     * @return void
     * @throws LocalizedException
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'shipping_method',
            array(
                'label' => __('Shipping method'),
                'style' => 'width:50px',
                'type' => 'select',
                'renderer' => $this->_getSelectRenderer('shipping_method'),
            )
        );

        $this->addColumn(
            'deliverytime',
            array(
                'label' => __('Delivery time'),
                'style' => 'width:100px',
                'type' => 'select',
                'renderer' => $this->_getSelectRenderer('deliverytime')
            )
        );

        $this->addColumn(
            'deliverymode',
            array(
                'label' => __('Delivery mode'),
                'style' => 'width:100px',
                'type' => 'select',
                'renderer' => $this->_getSelectRenderer('deliverymode')
            )
        );

        $this->addColumn(
            'delivery_expected_delay',
            array(
                'label' => __('Delivery expected delay'),
                'class' => 'required-entry validate-number input-text admin__control-text',
                'style' => 'width:100px',
            )
        );

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Configuration');
    }

    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $optionExtraAttr = [];
        $optionExtraAttr['option_' . $this->_getSelectRenderer('shipping_method')->calcOptionHash($row->getData('shipping_method'))] = 'selected="selected"';
        $optionExtraAttr['option_' . $this->_getSelectRenderer('deliverytime')->calcOptionHash($row->getData('deliverytime'))] = 'selected="selected"';
        $optionExtraAttr['option_' . $this->_getSelectRenderer('deliverymode')->calcOptionHash($row->getData('deliverymode'))] = 'selected="selected"';
        $row->setData(
            'option_extra_attrs',
            $optionExtraAttr
        );
    }

    /**
     * Render array cell for prototypeJS template
     *
     * @param string $columnName
     * @return string
     * @throws \Exception
     */
    public function renderCellTemplate($columnName)
    {
        if ($columnName == "active") {
            $this->_columns[$columnName]['class'] = 'input-text required-entry validate-number';
            $this->_columns[$columnName]['style'] = 'width:50px';
        }
        return parent::renderCellTemplate($columnName);
    }
}
