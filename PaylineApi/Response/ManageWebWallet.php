<?php

namespace Monext\Payline\PaylineApi\Response;

use Monext\Payline\PaylineApi\AbstractResponse;
use Monext\Payline\PaylineApi\Constants as PaylineApiConstants;

class ManageWebWallet extends AbstractResponse
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
    
    public function isSuccess()
    {
        return in_array($this->getResultCode(), array(PaylineApiConstants::PAYMENT_BACK_CODE_RETURN_OK, PaylineApiConstants::PAYMENT_BACK_CODES_RETURN_MANAGE_WEB_WALLET_OK));
    }
}