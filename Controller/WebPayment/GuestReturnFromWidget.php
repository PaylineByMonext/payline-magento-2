<?php

namespace Monext\Payline\Controller\WebPayment;

use Magento\Framework\App\Action\Context;
use Monext\Payline\Controller\Action;
use Monext\Payline\Model\GuestPaymentManagement as PaylineGuestPaymentManagement;

class GuestReturnFromWidget extends Action
{
    /**
     * @var PaylineGuestPaymentManagement
     */
    protected $paylineGuestPaymentManagement;
    
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
        PaylineGuestPaymentManagement $paylineGuestPaymentManagement
    )
    {
        parent::__construct($context);
        $this->paylineGuestPaymentManagement = $paylineGuestPaymentManagement;
    }
    
    public function execute() 
    {
        $isSuccess = true;

        try {
            $this->paylineGuestPaymentManagement->synchronizePaymentWithPaymentGatewayFacade($this->getToken(), true);
        } catch(\Exception $e) {
            $isSuccess = false;
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($isSuccess ? 'checkout/onepage/success' : 'checkout');
        return $resultRedirect;
    }
}

