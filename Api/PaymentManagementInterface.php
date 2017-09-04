<?php

namespace Monext\Payline\Api;

use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;

/**
 * @api
 */
interface PaymentManagementInterface
{
    /**
     * @param int $cartId
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @param \Magento\Quote\Api\Data\AddressInterface|null $billingAddress
     * realReturnType array (Magento webapi does not accept it)
     * @return anyType
     */
    public function saveCheckoutPaymentInformationFacade(
        $cartId,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    );
}
