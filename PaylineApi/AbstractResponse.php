<?php

namespace Monext\Payline\PaylineApi;

use Monext\Payline\PaylineApi\Constants as PaylineApiConstants;

abstract class AbstractResponse
{
    protected $data;
    
    public function fromData($data)
    {
        $this->data = $data;
    }
    
    public function getData()
    {
        return $this->data;
    }
    
    public function isSuccess()
    {
        return $this->getResultCode() == PaylineApiConstants::PAYMENT_BACK_CODE_RETURN_OK;
    }
    
    public function getShortErrorMessage()
    {
        return $this->data['result']['shortMessage'];
    }
    
    public function getLongErrorMessage()
    {
        return $this->data['result']['longMessage'];
    }
    
    public function getResultCode()
    {
        return $this->data['result']['code'];
    }
}
