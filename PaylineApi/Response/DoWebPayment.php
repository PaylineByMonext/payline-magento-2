<?php

namespace Monext\Payline\PaylineApi\Response;

use Monext\Payline\PaylineApi\Response;

class DoWebPayment extends Response
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