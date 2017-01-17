<?php

namespace Monext\Payline\PaylineApi\Response;

use Monext\Payline\PaylineApi\AbstractResponse;

class GetWebPaymentDetails extends AbstractResponse
{
    public function getTransactionData()
    {
        return $this->data['transaction'];
    }
    
    public function getPaymentData()
    {
        return $this->data['payment'];
    }
}

