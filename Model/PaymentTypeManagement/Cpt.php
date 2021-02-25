<?php

namespace Monext\Payline\Model\PaymentTypeManagement;

use Magento\Sales\Model\Order\Payment as OrderPayment;
use Monext\Payline\PaylineApi\Response\GetWebPaymentDetails as ResponseGetWebPaymentDetails;

class Cpt extends AbstractPaymentTypeManagement
{
    public function validate(
        ResponseGetWebPaymentDetails $response,
        OrderPayment $payment
    ) {
        $orderAmount = $this->helperData->mapMagentoAmountToPaylineAmount($payment->getOrder()->getGrandTotal());
        $responseAmount = $response->getAmount();

        if ($responseAmount == $orderAmount) {
            return true;
        } else {
            $message = __(
                'ERROR for order ; payment gateway amount %1 does not match order amount %2.',
                $response->getAmount(),
                $this->helperData->mapMagentoAmountToPaylineAmount($payment->getOrder()->getGrandTotal())
            );
            $payment->setAmountToCancel($response->getAmount());
            $this->handlePaymentCanceled($payment->setData('payline_in_error', true), $message);
            return false;
        }
    }
}
