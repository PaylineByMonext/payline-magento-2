<?php

namespace Monext\Payline\PaylineApi\Request;

use Monext\Payline\PaylineApi\AbstractRequest;
use Monext\Payline\PaylineApi\Constants as PaylineApiConstants;

class GetWebPaymentDetails extends AbstractRequest
{
    /**
     * @var string 
     */
    protected $token;
    
    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }
    
    public function getData() 
    {
        $data = array();
        
        $data['token'] = $this->token;
        $data['version'] = PaylineApiConstants::LASTEST_API_VERSION;
                
        return $data;
    }
}

