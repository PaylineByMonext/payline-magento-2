<?php

namespace Monext\Payline\PaylineApi\Response;

use Monext\Payline\PaylineApi\AbstractResponse;

class GetWebPaymentDetails extends AbstractResponse
{
    public function getTransaction()
    {
        return $this->data['transaction'];
    }
    
    public function getPayment()
    {
        return $this->data['payment'];
    }
}

