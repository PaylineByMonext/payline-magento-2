<?php

namespace Monext\Payline\Block\Adminhtml\System\Config\Form\Field;

use Magento\Framework\Exception\LocalizedException;
use Monext\Payline\Block\Adminhtml\System\Config\Form\Field\FieldArray\AbstractFieldArray;

class Exception extends AbstractFieldArray
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
            'regexp',
            array(
                'label' => __('Matched Expression'),
                'type' => 'text'
            )
        );

        $this->addColumn(
            'value',
            array(
                'label' => __('Value'),
                'type' => 'text'
            )
        );

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Exception');
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
