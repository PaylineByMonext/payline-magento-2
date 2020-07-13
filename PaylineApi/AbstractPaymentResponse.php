<?php

namespace Monext\Payline\PaylineApi;

use Monext\Payline\PaylineApi\Constants as PaylineApiConstants;

abstract class AbstractPaymentResponse extends AbstractResponse
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
        return in_array($this->getResultCode(), PaylineApiConstants::PAYMENT_BACK_CODES_RETURN_GET_WEB_PAYMENT_DETAILS_TRANSACTION_APPROVED);
    }

    public function isCanceled()
    {
        return in_array($this->getResultCode(), PaylineApiConstants::PAYMENT_BACK_CODES_RETURN_GET_WEB_PAYMENT_DETAILS_TRANSACTION_CANCELED);
    }

    public function isWaitingAcceptance()
    {
        return in_array($this->getResultCode(), PaylineApiConstants::PAYMENT_BACK_CODES_RETURN_GET_WEB_PAYMENT_DETAILS_TRANSACTION_WAITING_ACCEPTANCE);
    }

    public function isAbandoned()
    {
        return in_array($this->getResultCode(), PaylineApiConstants::PAYMENT_BACK_CODES_RETURN_GET_WEB_PAYMENT_DETAILS_TRANSACTION_ABANDONED);
    }

    public function isFraud()
    {
        return in_array($this->getResultCode(), PaylineApiConstants::PAYMENT_BACK_CODES_RETURN_GET_WEB_PAYMENT_DETAILS_TRANSACTION_FRAUD);
    }

    public function isRefused()
    {
        return !$this->isSuccess() && !$this->isCanceled() && !$this->isAbandoned() && !$this->isWaitingAcceptance() && !$this->isFraud();
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
