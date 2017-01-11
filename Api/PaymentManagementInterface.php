<?php

namespace Monext\Payline\Api;

use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\Data\TotalsInterface;

interface PaymentManagementInterface
{
    public function savePaymentInformationAndCallPaylineApiDoWebPayment(
        $cartId,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    );
    
    public function wrapCallPaylineApiDoWebPayment($cartId);
        
    public function callPaylineApiDoWebPayment(
        CartInterface $cart,
        TotalsInterface $totals,
        PaymentInterface $payment
    );
}
