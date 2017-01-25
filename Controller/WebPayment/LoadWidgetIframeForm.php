<?php

namespace Monext\Payline\Controller\WebPayment;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\RawFactory as ResultRawFactory;
use Monext\Payline\Controller\Action;
use Monext\Payline\Block\WebPayment\WidgetIframeFormFactory;

class LoadWidgetIframeForm extends Action
{
    /**
     * @var ResultRawFactory 
     */
    protected $resultRawFactory;
    
    /**
     * @var WidgetIframeFormFactory 
     */
    protected $widgetIframeFormFactory;
    
    public function __construct(
        Context $context,
        ResultRawFactory $resultRawFactory,
        WidgetIframeFormFactory $widgetIframeFormFactory
    )
    {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->widgetIframeFormFactory = $widgetIframeFormFactory;
    }
    
    public function execute()
    {
        $resultRaw = $this->resultRawFactory->create();
        
        $block = $this->widgetIframeFormFactory->create();
        $block->setTemplate('Monext_Payline::web_payment/widget_iframe_form.phtml');
        $resultRaw->setContents($block->toHtml());
        
        return $resultRaw;
    }
}