<?php

namespace Monext\Payline\PaylineApi\Request;

use Magento\Sales\Api\Data\TransactionInterface;
use Monext\Payline\PaylineApi\AbstractRequest;
use Monext\Payline\PaylineApi\Constants as PaylineApiConstants;

class DoCapture extends AbstractRequest
{
    /**
     * @var TransactionInterface 
     */
    protected $authorizationTransaction;
    
    /**
     * @var array 
     */
    protected $paymentData;
   
    public function setAuthorizationTransaction(TransactionInterface $authorizationTransaction)
    {
        $this->authorizationTransaction = $authorizationTransaction;
        return $this;
    }
    
    public function setPaymentData(array $paymentData)
    {
        $this->paymentData = $paymentData;
        return $this;
    }
    
    public function getData() 
    {
        $data = array();
        
        // PAYMENT
        $data['payment'] = $this->paymentData;
        $data['payment']['action'] = PaylineApiConstants::PAYMENT_ACTION_CAPTURE;
        
        // TRANSACTION ID
        $data['transactionID'] = $this->authorizationTransaction->getTxnId();
        
        // SEQUENCE NUMBER
        $data['sequenceNumber'] = '';
        
        return $data;
    }
}