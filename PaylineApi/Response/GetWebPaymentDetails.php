<?php

namespace Monext\Payline\PaylineApi\Response;

use Monext\Payline\PaylineApi\AbstractPaymentResponse;

class GetWebPaymentDetails extends AbstractPaymentResponse
{
    public function getTransactionData()
    {
        return $this->data['transaction'];
    }

    public function getPaymentData()
    {
        return $this->data['payment'];
    }

    public function getContractNumber()
    {
        return $this->getPaymentData()['contractNumber'];
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

    // Cas NX / RC

    /**
     * @return string | null
     */
    public function getPaymentRecordId()
    {
        return $this->data['paymentRecordId'];
    }

    /**
     * @param array $filter
     * @return array | null
     */
    public function getBillingRecords($filter = array())
    {
        $billingRecords = $this->data['billingRecordList']['billingRecord'];
        if (!empty($filter)) {
            foreach ($billingRecords as $key => $billingRecord) {
                $billingRecords[$key] = array_intersect_key($billingRecord, array_flip($filter));
            }
        }
        return $billingRecords;
    }
}
