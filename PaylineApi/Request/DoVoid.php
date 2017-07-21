<?php

namespace Monext\Payline\PaylineApi\Request;

use Monext\Payline\PaylineApi\AbstractRequest;
use Monext\Payline\PaylineApi\Constants as PaylineApiConstants;

class DoVoid extends AbstractRequest
{

    /**
     * @var array 
     */
    protected $paymentData;
   
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

        // Transaction ID
        // Remove -void part added by Magento to keep original transactionID
        // @see Magento\Sales\Model\Order\Payment\Transaction generateTransactionId
        $data['transactionID'] = str_replace('-void','',$data['payment']['transactionID']);
        
        // Same for comment
        $data['comment'] = $data['payment']['comment'];
        
        unset($data['payment']);
        
        // MEDIA
        $data['media'] = '';
        
        return $data;
    }
}