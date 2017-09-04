<?php

namespace Monext\Payline\Api;

use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;

/**
 * @api
 */
interface GuestPaymentManagementInterface
{
    /**
     * @param string $cartId
     * @param string $email
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @param \Magento\Quote\Api\Data\AddressInterface|null $billingAddress
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * realReturnType array (Magento webapi does not accept it)
     * @return anyType
     */
    public function saveCheckoutPaymentInformationFacade(
        $cartId,
        $email,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    );
}
