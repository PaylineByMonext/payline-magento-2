<?php

namespace Monext\Payline\PaylineApi\Response;

use Monext\Payline\PaylineApi\AbstractResponse;

class DoRefund extends AbstractResponse
{
    public function getTransactionId()
    {
        return $this->data['transaction']['id'];
    }
}
