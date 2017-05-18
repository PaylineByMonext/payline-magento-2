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
        
        // MEDIA
        $data['media'] = '';
        
        return $data;
    }
}