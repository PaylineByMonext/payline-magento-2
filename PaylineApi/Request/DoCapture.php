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
        
        // TRANSACTION ID
        $data['transactionID'] = $this->authorizationTransaction->getTxnId();
        
        // SEQUENCE NUMBER
        $data['sequenceNumber'] = '';
        
        return $data;
    }
}