<?php

namespace Monext\Payline\Model\Method\WebPayment;

use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Model\Order\Payment as OrderPayment;
use Monext\Payline\Model\Method\AbstractMethod;
use Monext\Payline\Helper\Constants as HelperConstants;
use Monext\Payline\PaylineApi\Constants as PaylineApiConstants;

class Cpt extends AbstractMethod
{
    protected $_code = HelperConstants::WEB_PAYMENT_CPT;
    
    protected $_isInitializeNeeded = true;
    
    protected $_isGateway = true;

    protected $_canCapture = true;

    protected $_canRefund = true;

    protected $_canVoid = true;
    
    public function initialize($paymentAction, $stateObject)
    {
        $payment = $this->getInfoInstance();

        if($payment instanceof OrderPayment 
        && $this->getConfigData('payment_workflow') == PaylineApiConstants::PAYMENT_WORKFLOW_REDIRECT) {
            $quoteId = $payment->getOrder()->getQuoteId();
            $result = $this->paylinePaymentManagement->wrapCallPaylineApiDoWebPayment($quoteId);
        
            $additionalInformation = $payment->getAdditionalInformation();
            $additionalInformation['do_web_payment_response_data'] = $result;
            $payment->setAdditionalInformation($additionalInformation);
        }

        $stateObject->setData('status', HelperConstants::ORDER_STATUS_PAYLINE_PENDING);
        
        return $this;
    }
    
    public function capture(InfoInterface $payment, $amount)
    {
        parent::capture($payment, $amount);

        

        return $this;
    }
}