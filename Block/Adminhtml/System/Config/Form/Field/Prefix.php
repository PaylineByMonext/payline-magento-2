<?php

namespace Monext\Payline\Block\Adminhtml\System\Config\Form\Field;

use Magento\Framework\Exception\LocalizedException;
use Monext\Payline\Block\Adminhtml\System\Config\Form\Field\FieldArray\AbstractFieldArray;

class Prefix extends AbstractFieldArray
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
            'customer_prefix',
            array(
                'label' => __('Prefix'),
                'type' => 'select',
                'renderer' => $this->_getSelectRenderer('customer_prefix')
            )
        );

        $this->addColumn(
            'customer_title',
            array(
                'label' => __('Title'),
                'type' => 'select',
                'renderer' => $this->_getSelectRenderer('customer_title')
            )
        );

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Configuration');
    }

    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $optionExtraAttr = [];
        $optionExtraAttr['option_' . $this->_getSelectRenderer('customer_title')->calcOptionHash($row->getData('customer_title'))] = 'selected="selected"';
        $optionExtraAttr['option_' . $this->_getSelectRenderer('customer_prefix')->calcOptionHash($row->getData('customer_prefix'))] = 'selected="selected"';
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
