<?php

namespace Monext\Payline\PaylineApi\Response;

use Monext\Payline\PaylineApi\AbstractResponse;
use Monext\Payline\PaylineApi\Constants as PaylineApiConstants;

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

    public function getWalletData()
    {
        return isset($this->data['wallet']) ? $this->data['wallet'] : null;
    }

    public function getAmount()
    {
        $paymentData = $this->getPaymentData();
        return isset($paymentData['amount']) ? $paymentData['amount'] : null;
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
}

