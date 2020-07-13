<?php

namespace Monext\Payline\PaylineApi\Response;

use Monext\Payline\PaylineApi\AbstractPaymentResponse;

class GetPaymentRecord extends AbstractPaymentResponse
{
    /**
     * @return Array
     * @description possible key [firstAmount] [amount] [billingCycle] [billingLeft] [billingDay] [startDate] [endDate] [newAmount] [amountModificationDate]
     */
    public function getRecurringData()
    {
        return $this->data['recurring'];
    }

    /**
     * @return bool
     */
    public function isDisabled()
    {
        return (bool)$this->data['recurring'];
    }

    /**
     * @return \DateTime | null
     * @throws \Exception
     */
    public function getDisableData()
    {
        $date = null;
        if($this->isDisabled()) {
            $date = new \DateTime($this->data['disableDate']);
        }
        return $date;
    }

    public function getBillingRecordsList()
    {
        return $this->data['billingRecordList'];
    }

    /**
     * @param array $filter
     * @return array | null
     */
    public function getBillingRecords($filter = array())
    {
        $billingRecords = $this->getBillingRecordsList()['billingRecord'];
        if (!empty($filter)) {
            foreach ($billingRecords as $key => $billingRecord) {
                $billingRecords[$key] = array_intersect_key($billingRecord, array_flip($filter));
            }
        }
        return $billingRecords;
    }

    public function getPrivateDataList()
    {
        return $this->data['privateDataList'];
    }

    public function getPaylineOrderInfo()
    {
        return $this->data['order'];
    }

    public function getWalletId()
    {
        return isset($this->data['walletId']) ? $this->data['walletId'] : null;
    }
}
