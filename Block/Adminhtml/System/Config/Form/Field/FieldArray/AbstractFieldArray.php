<?php

namespace Monext\Payline\Block\Adminhtml\System\Config\Form\Field\FieldArray;

use Magento\Framework\Exception\LocalizedException;
use Monext\Payline\Block\Adminhtml\System\Config\Renderer\Select;
use Monext\Payline\Block\Adminhtml\System\Config\RendererSelectFactory;

abstract class AbstractFieldArray extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    /**
     * @var Select[]
     */
    protected $selectRenderer;

    /**
     * @var RendererSelectFactory
     */
    protected $rendererSelectFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        RendererSelectFactory $rendererSelectFactory,
        array $data = []
    )
    {
        $this->rendererSelectFactory = $rendererSelectFactory;
        parent::__construct($context, $data);
    }

    /**
     * @param $class
     * @return \Magento\Framework\View\Element\Html\Select
     * @throws LocalizedException
     */
    protected function _getSelectRenderer($class)
    {
        if (!isset($this->selectRenderer[$class])) {
            $this->selectRenderer[$class] = $this->getLayout()->createBlock(
                $this->rendererSelectFactory->create($class)->getClass(),
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->selectRenderer[$class]->setClass(implode(' ', ['required-entry', 'select', 'admin__control-select', $class]));
//            $this->selectRenderer[$class]->setId($class);
            $this->selectRenderer[$class]->setExtraParams('style="width:120px"');
        }
        return $this->selectRenderer[$class];
    }
}
