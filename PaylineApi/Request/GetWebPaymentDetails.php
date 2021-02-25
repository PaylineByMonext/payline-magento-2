<?php

namespace Monext\Payline\PaylineApi\Request;

use Monext\Payline\PaylineApi\AbstractRequest;

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
        $data = parent::getData();

        $data['token'] = $this->token;

        return $data;
    }
}
