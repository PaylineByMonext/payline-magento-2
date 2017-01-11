<?php

namespace Monext\Payline\Model\Method\WebPayment;

use Magento\Sales\Model\Order\Payment as OrderPayment;
use Monext\Payline\Model\Method\AbstractMethod;
use Monext\Payline\Helper\Constants as HelperConstants;

class Cpt extends AbstractMethod
{
    protected $_code = HelperConstants::WEB_PAYMENT_CPT;
    
    protected $_isInitializeNeeded = true;
    
    protected $_isGateway = true;

    protected $_canAuthorize = true;

    protected $_canCapture = true;

    protected $_canRefund = true;

    protected $_canVoid = true;
    
    public function initialize($paymentAction, $stateObject)
    {
        $payment = $this->getInfoInstance();
        
        if($payment instanceof OrderPayment) {
            $quoteId = $payment->getOrder()->getQuoteId();
            $result = $this->paymentManagement->wrapCallPaylineApiDoWebPayment($quoteId);
        
            $additionalInformation = $payment->getAdditionalInformation();
            $additionalInformation['do_web_payment_response_data'] = $result;
            $payment->setAdditionalInformation($additionalInformation);
        }

        return $this;
    }
}