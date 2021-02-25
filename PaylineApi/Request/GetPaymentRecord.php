<?php

namespace Monext\Payline\PaylineApi\Request;

use Monext\Payline\PaylineApi\AbstractRequest;

class GetPaymentRecord extends AbstractRequest
{
    /**
     * @var string
     */
    protected $contractNumber;

    protected $paymentRecordId;

    public function setContractNumber($contractNumber)
    {
        $this->contractNumber = $contractNumber;
        return $this;
    }

    public function setPaymentRecordId($paymentRecordId)
    {
        $this->paymentRecordId = $paymentRecordId;
        return $this;
    }

    public function getData()
    {
        $data = parent::getData();
        $data['contractNumber'] = $this->contractNumber;
        $data['paymentRecordId'] = $this->paymentRecordId;

        return $data;
    }
}
