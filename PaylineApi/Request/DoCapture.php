<?php

namespace Monext\Payline\PaylineApi\Request;

use Magento\Sales\Api\Data\TransactionInterface;
use Monext\Payline\PaylineApi\AbstractRequest;

class DoCapture extends AbstractRequest
{
    /**
     * @var TransactionInterface 
     */
    protected $authorizationTransaction;
    
    public function setAuthorizationTransaction(TransactionInterface $authorizationTransaction)
    {
        $this->authorizationTransaction = $authorizationTransaction;
        return $this;
    }
    
    public function getData() 
    {
        $data = array();
        
        $paymentMethod = $this->payment->getMethod();
        $paymentAdditionalInformation = $this->payment->getAdditionalInformation();
        $paymentWorkflow = $this->scopeConfig->getValue('payment/'.$paymentMethod.'/payment_workflow');
        
        // PAYMENT
        $data['payment']['amount'] = round($this->totals->getGrandTotal() * 100, 0);
        $data['payment']['currency'] = $this->helperCurrency->getNumericCurrencyCode($this->totals->getBaseCurrencyCode());
        $data['payment']['action'] = $this->scopeConfig->getValue('payment/'.$paymentMethod.'/payment_action');
        $data['payment']['mode'] = $paymentAdditionalInformation['payment_mode'];
        $data['payment']['contractNumber'] = $this->scopeConfig->getValue('payment/'.$paymentMethod.'/contract');
        
        // ORDER
        $data['order']['ref'] = $this->cart->getReservedOrderId();
        $data['order']['amount'] = round($this->totals->getGrandTotal() * 100, 0);
        $data['order']['currency'] = $this->helperCurrency->getNumericCurrencyCode($this->totals->getBaseCurrencyCode());
        $data['order']['date'] = $this->formatDateTime($this->cart->getCreatedAt());
        
        if($paymentWorkflow == PaylineApiConstants::PAYMENT_WORKFLOW_REDIRECT) {
            $this->_prepareUrlsForPaymentWorkflowRedirect($data);
        } elseif($paymentWorkflow == PaylineApiConstants::PAYMENT_WORKFLOW_WIDGET) {
            $this->_prepareUrlsForPaymentWorkflowWidget($data);
        }
        
        return $data;
    }
}