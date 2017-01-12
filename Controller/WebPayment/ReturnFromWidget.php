<?php

namespace Monext\Payline\Controller\WebPayment;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\RawFactory as ResultRawFactory;
use Magento\Framework\View\Element\TemplateFactory;
use Monext\Payline\Model\CartManagement;
use Monext\Payline\Model\OrderIncrementIdTokenFactory as OrderIncrementIdTokenFactory;

class ReturnFromWidget extends Action
{
    /**
     * @var OrderIncrementIdTokenFactory
     */
    protected $orderIncrementIdTokenFactory;
    
    /**
     * @var CartManagement
     */
    protected $cartManagement;
    
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
        CartManagement $cartManagement,
        OrderIncrementIdTokenFactory $orderIncrementIdTokenFactory,
        ResultRawFactory $resultRawFactory,
        TemplateFactory $templateFactory
    )
    {
        parent::__construct($context);
        $this->orderIncrementIdTokenFactory = $orderIncrementIdTokenFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->cartManagement = $cartManagement;
        $this->templateFactory = $templateFactory;
    }
    
    public function execute() 
    {
        // TODO CatchException
        $this->cartManagement->placeOrderFromCartReservedOrderId(
            $this->orderIncrementIdTokenFactory->create()->getOrderIncrementIdFromToken($this->getRequest()->getParam('paylinetoken'))
        );
        
        $resultRaw = $this->resultRawFactory->create();
        
        $block = $this->templateFactory->create();
        $block->setTemplate('Monext_Payline::web_payment/widget_iframe_success.phtml');
        $resultRaw->setContents($block->toHtml());
        
        return $resultRaw;
    }
}

