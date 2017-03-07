<?php

namespace Monext\Payline\Model;

use Magento\Checkout\Api\GuestPaymentInformationManagementInterface as CheckoutGuestPaymentInformationManagementInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Monext\Payline\Api\GuestPaymentManagementInterface as PaylineGuestPaymentManagementInterface;
use Monext\Payline\Model\GuestCartManagement as PaylineGuestCartManagement;
use Monext\Payline\Model\OrderManagement as PaylineOrderManagement;
use Monext\Payline\Model\PaymentManagement as PaylinePaymentManagement;

class GuestPaymentManagement implements PaylineGuestPaymentManagementInterface
{
    /**
     * @var CheckoutGuestPaymentInformationManagementInterface 
     */
    protected $checkoutGuestPaymentInformationManagement;
    
    /**
     * @var PaylinePaymentManagement
     */
    protected $paylinePaymentManagement;

    /**
     * @var QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * @var PaylineGuestCartManagement 
     */
    protected $paylineGuestCartManagement;
    
    /**
     * @var PaylineOrderManagement
     */
    protected $paylineOrderManagement;
    
    public function __construct(
        CheckoutGuestPaymentInformationManagementInterface $checkoutGuestPaymentInformationManagement,
        PaylinePaymentManagement $paylinePaymentManagement,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        PaylineGuestCartManagement $paylineGuestCartManagement,
        PaylineOrderManagement $paylineOrderManagement
    )
    {
        $this->checkoutGuestPaymentInformationManagement = $checkoutGuestPaymentInformationManagement;
        $this->paylinePaymentManagement = $paylinePaymentManagement;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->paylineGuestCartManagement = $paylineGuestCartManagement;
        $this->paylineOrderManagement = $paylineOrderManagement;
    }

    public function saveCheckoutPaymentInformationFacade(
        $cartId,
        $email,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    )
    {
        $this->checkoutGuestPaymentInformationManagement->savePaymentInformation($cartId, $email, $paymentMethod, $billingAddress);
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        $result = $this->paylinePaymentManagement->wrapCallPaylineApiDoWebPaymentFacade($quoteIdMask->getQuoteId());
        return $result;
    }

    public function synchronizePaymentWithPaymentGatewayFacade($token, $restoreCartOnError = false)
    {
        $order = $this->paylineOrderManagement->getOrderByToken($token);

        if(!$order->getId()) {
            $this->paylineGuestCartManagement->placeOrderByToken($token);
        }

        $this->paylinePaymentManagement->synchronizePaymentWithPaymentGatewayFacade($token, $restoreCartOnError);
        return $this;
    }
}
