<?php

namespace Monext\Payline\Controller\WebPayment;

use Monext\Payline\Controller\Action;

class ReturnFromPaymentGateway extends Action
{
    public function execute() 
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('checkout/onepage/success');
        return $resultRedirect;
    }
}

