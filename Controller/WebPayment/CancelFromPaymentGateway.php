<?php

namespace Monext\Payline\Controller\WebPayment;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\RedirectFactory as ResultRedirectFactory;
use Monext\Payline\Controller\Action;
use Monext\Payline\Model\PaymentManagement as PaylinePaymentManagement;

class CancelFromPaymentGateway extends Action
{
    /**
     * @var ResultRedirectFactory 
     */
    protected $resultRedirectFactory;
    
    /**
     * @var PaylinePaymentManagement 
     */
    protected $paylinePaymentManagement;
    
    public function __construct(
        Context $context,
        ResultRedirectFactory $resultRedirectFactory,
        PaylinePaymentManagement $paylinePaymentManagement
    )
    {
        parent::__construct($context);
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->paylinePaymentManagement = $paylinePaymentManagement;
    }
    
    public function execute() 
    {
        // TODO handle exception
        $this->paylinePaymentManagement->handlePaymentGatewayCancelByToken($this->getToken());
        
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setUrl('checkout');
        return $resultRedirect;
    }
}