<?php

namespace Monext\Payline\Model\PaymentTypeManagement;

use Magento\Sales\Model\Order\Payment as OrderPayment;
use Monext\Payline\PaylineApi\Response\GetWebPaymentDetails as ResponseGetWebPaymentDetails;

class Nx extends AbstractPaymentTypeManagement
{
    public function validate(
        ResponseGetWebPaymentDetails $response,
        OrderPayment $payment
    )
    {
        //pas de validation possible $response->getAmount() !== $payment->getOrder()->getGrandTotal()
        return true;
    }

    /**
     * @param ResponseGetWebPaymentDetails $response
     * @param OrderPayment $payment
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function handlePaymentSuccess(ResponseGetWebPaymentDetails $response, OrderPayment $payment)
    {
        $payment->setAdditionalInformation('payment_cycling', $response->getBillingRecords(['date', 'amount', 'rank']));
        $payment->setAdditionalInformation('payment_record_id', $response->getPaymentRecordId());
        $payment->setAdditionalInformation('contract_number', $response->getContractNumber());
        parent::handlePaymentSuccess($response, $payment);
    }
}
