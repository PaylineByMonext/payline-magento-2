<?php

namespace Monext\Payline\Controller\WebPayment;

use Magento\Framework\App\Action\Context;
use Monext\Payline\Controller\Action;
use Monext\Payline\Model\PaymentManagement as PaylinePaymentManagement;

class ReturnFromPaymentGateway extends Action
{
    /**
     * @var PaylinePaymentManagement
     */
    protected $paylinePaymentManagement;
    
    public function __construct(
        Context $context,
        PaylinePaymentManagement $paylinePaymentManagement
    ) {
        parent::__construct($context);
        $this->paylinePaymentManagement = $paylinePaymentManagement;
    }
    
    public function execute()
    {
        $isSuccess = true;

        try {
            $this->paylinePaymentManagement->synchronizePaymentWithPaymentGatewayFacade($this->getToken(), true);
        } catch (\Exception $e) {
            $isSuccess = false;
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($isSuccess ? 'checkout/onepage/success' : 'checkout');
        return $resultRedirect;
    }
}
