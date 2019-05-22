<?php

namespace Monext\Payline\PaylineApi\Response;

use Monext\Payline\PaylineApi\AbstractResponse;

class DoWebPayment extends AbstractResponse
{
    protected $token;
    
    protected $redirectUrl;
    
    public function getToken()
    {
        return $this->data['token'];
    }
    
    public function getRedirectUrl()
    {
        return $this->data['redirectURL'];
    }
}
