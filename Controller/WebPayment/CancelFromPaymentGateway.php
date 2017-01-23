<?php

namespace Monext\Payline\Controller\WebPayment;

use Magento\Framework\App\Action\Context;
use Monext\Payline\Controller\Action;
use Monext\Payline\Model\PaymentManagement as PaylinePaymentManagement;

class CancelFromPaymentGateway extends Action
{
    /**
     * @var PaylinePaymentManagement 
     */
    protected $paylinePaymentManagement;
    
    public function __construct(
        Context $context,
        PaylinePaymentManagement $paylinePaymentManagement
    )
    {
        parent::__construct($context);
        $this->paylinePaymentManagement = $paylinePaymentManagement;
    }
    
    public function execute() 
    {
        // TODO handle exception
        $this->paylinePaymentManagement->handlePaymentGatewayCancelByToken($this->getToken());
        
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('checkout');
        return $resultRedirect;
    }
}