<?php
namespace Monext\Payline\Block\Adminhtml\System\Config\Form\Field;

use Magento\Framework\View\Element\Html\Select;

class ContractList extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    /**
     * @var string
     */
    protected $_template = 'Monext_Payline::system/config/form/field/contract_list.phtml';

    protected $_yesNoRenderer=[];

    protected function _getYesnoRenderer($input)
    {
        if (empty($this->_yesNoRenderer[$input])) {
            $this->_yesNoRenderer[$input] =  $this->getLayout()->createBlock(
                    Onoffswitch::class,
                    '',
                    ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->_yesNoRenderer[$input];
    }



    /**
     * Prepare to render
     */
    protected function _prepareToRender()
    {
        $this->addColumn('name', [
                'label'    => __('Name'),
                'type' => 'readonly'
        ]);

        $this->addColumn('number', [
                'label'    => __('Number'),
                'type' => 'readonly'
        ]);

        $this->addColumn('pos', [
                'label'    => __('Point Of Sell'),
                'type' => 'readonly'
        ]);

        $this->addColumn('type', [
                'label'    => __('Type'),
                'type' => 'readonly'
        ]);

        $this->addColumn('primary', [
                'label'    => __('Primary'),
                'renderer' => $this->_getYesnoRenderer('primary')
        ]);

        $this->addColumn('secondary', [
                'label'    => __('Secondary'),
                'renderer' => $this->_getYesnoRenderer('secondary'),
        ]);

        $this->addColumn('secure', [
                'label'    => __('Secure'),
                'renderer' => $this->_getYesnoRenderer('secure'),
        ]);

        $this->addColumn('wallet', [
                'label'    => __('Wallet'),
                'renderer' => $this->_getYesnoRenderer('wallet'),
        ]);

        $this->_addAfter = false;
    }

    /**
     * Add type property to column
     *
     * @param string $name
     * @param array $params
     */
    public function addColumn($name, $params)
    {
        parent::addColumn($name, $params);
        if(array_key_exists($name,$this->_columns) && !empty($params['type']))
            $this->_columns[$name]['type'] = $params['type'];
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
        if (empty($this->_columns[$columnName])) {
            throw new \Exception('Wrong column name specified.');
        }
        $column = $this->_columns[$columnName];
        $inputName = $this->_getCellInputElementName($columnName);

        if (!empty($column['type']) and $column['type']=='readonly') {
                    return '<input type="hidden" id="' . $this->_getCellInputElementId(
                '<%- _id %>',
                $columnName
            ) .
            '"' .
            ' name="' .
            $inputName .
            '" value="<%- ' .
            $columnName .
            ' %>" ' .
            '/><%- ' .
            $columnName .
            ' %>';
        }


        return parent::renderCellTemplate($columnName);
    }




    /**
     * Prepare existing row data object
     *
     * @param DataObject $row
     * @return void
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $primary = $row->getPrimary() ? $row->getPrimary() : 0;
        $secondary = $row->getSecondary() ? $row->getSecondary() : 0;
        $secure = $row->getSecure() ? $row->getSecure() : 0;
        $wallet = $row->getWallet() ? $row->getWallet() : 0;

        $options = [];
        $options['option_' . $this->_getYesnoRenderer('primary')->calcOptionHash($primary)]
            = 'selected="selected"';
        $options['option_' . $this->_getYesnoRenderer('secondary')->calcOptionHash($secondary)]
            = 'selected="selected"';
        $options['option_' . $this->_getYesnoRenderer('secure')->calcOptionHash($secure)]
            = 'selected="selected"';
        $options['option_' . $this->_getYesnoRenderer('wallet')->calcOptionHash($wallet)]
            = 'selected="selected"';

        $row->setData('option_extra_attrs', $options);
    }

    /**
     * Get url to retrieve payment method nonce
     * @return string
     */
    public function getContractImportUrl()
    {
        //return $this->_urlBuilder->getUrl('payline/contract/import', ['_secure' => true]);
        return $this->_urlBuilder->getUrl('payline/contract/import');
    }
}
