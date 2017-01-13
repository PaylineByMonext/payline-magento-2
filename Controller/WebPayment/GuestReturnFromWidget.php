<?php

namespace Monext\Payline\Controller\WebPayment;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\RawFactory as ResultRawFactory;
use Magento\Framework\View\Element\TemplateFactory;
use Monext\Payline\Model\GuestCartManagement as PaylineGuestCartManagement;

class GuestReturnFromWidget extends Action
{
    /**
     * @var PaylineGuestCartManagement
     */
    protected $paylineGuestCartManagement;
    
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
        PaylineGuestCartManagement $paylineGuestCartManagement,
        ResultRawFactory $resultRawFactory,
        TemplateFactory $templateFactory
    )
    {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->paylineGuestCartManagement = $paylineGuestCartManagement;
        $this->templateFactory = $templateFactory;
    }
    
    public function execute() 
    {
        // TODO CatchException
        $this->paylineGuestCartManagement->placeOrderByToken($this->getRequest()->getParam('paylinetoken'));
        
        $resultRaw = $this->resultRawFactory->create();
        
        $block = $this->templateFactory->create();
        $block->setTemplate('Monext_Payline::web_payment/widget_iframe_success.phtml');
        $resultRaw->setContents($block->toHtml());
        
        return $resultRaw;
    }
}

