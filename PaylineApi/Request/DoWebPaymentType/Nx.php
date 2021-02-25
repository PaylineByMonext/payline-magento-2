<?php

namespace Monext\Payline\PaylineApi\Request\DoWebPaymentType;

use Monext\Payline\Helper\Constants;

class Nx extends AbstractDoWebPaymentType
{
    const PAYMENT_METHOD = Constants::WEB_PAYMENT_NX;

    /**
     * @param array $data
     * @return array
     * @throws \Exception
     * @see https://docs.payline.com/display/DT/Paiement+n+fois
     * @see https://docs.payline.com/display/DT/DP+-+Paiement+N+fois
     */
    public function getData(&$data)
    {
        $usedContracts = $this->contractManagement->getUsedContracts();
        $data['payment']['contractNumber'] = $usedContracts->getFirstItem()->getNumber();
        $data['contracts'] = $usedContracts->getColumnValues('number');
        $this->addBillingCycle($data);
        $this->addRecurringAmount($data);
//        $this->addCostRecurringPayment($data); @todo a finir en attente de spec sur les frais de port
        $this->prepareUrls($data);
        return $data;
    }

    protected function prepareUrls(&$data)
    {
        $data['returnURL'] = $this->urlBuilder->getUrl('payline/webpayment/returnfrompaymentgateway');
        $data['cancelURL'] = $this->urlBuilder->getUrl('payline/webpayment/returnfrompaymentgateway');
        $data['notificationURL'] = $this->urlBuilder->getUrl('payline/webpayment/notifycyclingpaymentfrompaymentgateway');
    }

    protected function addBillingCycle(&$data) {
        $billingCycle = $this->scopeConfig->getValue('payment/' . static::PAYMENT_METHOD . '/billing_cycle');
        $data['recurring']['billingCycle'] = $billingCycle;
    }

    /**
     * @param $data
     * @throws \Exception
     */
    protected function addRecurringAmount(&$data)
    {
        $billingOccurrences = (int)$this->scopeConfig->getValue('payment/' . static::PAYMENT_METHOD . '/billing_occurrences');
        if($billingOccurrences === 0) {
            throw new \Exception('Config error : Billing occurrences can not be zero');
        }
        $data['recurring']['billingLeft'] = $billingOccurrences;

        $amount = $this->paylineHelper->mapPaylineAmountToMagentoAmount($data['payment']['amount']);
        $amountFloor = floor($amount);
        $recurringAmount = floor($amountFloor / $billingOccurrences);
        $firstAmount = $amount - ($recurringAmount * ($billingOccurrences - 1));

        $data['recurring']['firstAmount'] = $this->paylineHelper->mapMagentoAmountToPaylineAmount($firstAmount);
        $data['recurring']['amount'] = $this->paylineHelper->mapMagentoAmountToPaylineAmount($recurringAmount);
    }

    protected function addCostRecurringPayment(&$data)
    {
        $costType = (int)$this->scopeConfig->getValue('payment/' . static::PAYMENT_METHOD . '/cost_type');
        if ($firstAmount = $data['recurring']['firstAmount'] && $costType !== Constants::COST_TYPE_NO_CHARGES) {
            $costAmount = (int)$this->scopeConfig->getValue('payment/' . static::PAYMENT_METHOD . '/cost_amount');
            if($costAmount > 0) {
                $costAmount = $this->paylineHelper->mapMagentoAmountToPaylineAmount($costAmount);
                switch ($costType) {
                    case Constants::COST_TYPE_FIXE:
                        $this->addCostRecurringPaymentTypeFixe($data, $costAmount);
                        break;
                    case Constants::COST_TYPE_PERCENT:
                        $this->addCostRecurringPaymentTypePercent($data, $costAmount);
                        break;
                    default:break;
                }
            }
        }
    }

    protected function addCostRecurringPaymentTypeFixe(&$data, $costAmount) {
        $data['recurring']['firstAmount'] = $data['recurring']['firstAmount'] + $costAmount;
    }

    protected function addCostRecurringPaymentTypePercent(&$data, $costAmount) {
        //On calcule les frais sur le montant de la commande sans les frais de ports
        //@todo a finir
        $this->getPayment()->getQuote()->getGrandTotal();
        $data['recurring']['firstAmount'] = $data['recurring']['firstAmount'] + $costAmount;
    }
}
