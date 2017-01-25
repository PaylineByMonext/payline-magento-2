<?php

namespace Monext\Payline\Controller\WebPayment;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\RawFactory as ResultRawFactory;
use Magento\Framework\View\Element\TemplateFactory;
use Monext\Payline\Controller\Action;
use Monext\Payline\Model\CartManagement as PaylineCartManagement;

class ReturnFromWidget extends Action
{
    /**
     * @var PaylineCartManagement
     */
    protected $paylineCartManagement;
    
    /**
     * @var ResultRawFactory 
     */
    protected $resultRawFactory;
    
    /**
     * @var TemplateFactory 
     */
    protected $templateFactory;
    
    public function __construct(
        Context $context,
        PaylineCartManagement $paylineCartManagement,
        ResultRawFactory $resultRawFactory,
        TemplateFactory $templateFactory
    )
    {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->paylineCartManagement = $paylineCartManagement;
        $this->templateFactory = $templateFactory;
    }
    
    public function execute() 
    {
        // TODO CatchException
        $this->paylineCartManagement->placeOrderByToken($this->getToken());
        
        $resultRaw = $this->resultRawFactory->create();
        
        $block = $this->templateFactory->create();
        $block->setTemplate('Monext_Payline::web_payment/widget_iframe_success.phtml');
        $resultRaw->setContents($block->toHtml());
        
        return $resultRaw;
    }
}

