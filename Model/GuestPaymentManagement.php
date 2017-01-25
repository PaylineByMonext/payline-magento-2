<?php

namespace Monext\Payline\Model;

use Magento\Checkout\Api\GuestPaymentInformationManagementInterface as CheckoutGuestPaymentInformationManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Monext\Payline\Api\GuestPaymentManagementInterface as PaylineGuestPaymentManagementInterface;
use Monext\Payline\Model\CartManagement as PaylineCartManagement;
use Monext\Payline\Model\PaymentManagement as PaylinePaymentManagement;

class GuestPaymentManagement implements PaylineGuestPaymentManagementInterface
{
    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;
    
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
     * @var PaylineCartManagement 
     */
    protected $paylineCartManagement;
    
    public function __construct(
        CartRepositoryInterface $cartRepository, 
        CheckoutGuestPaymentInformationManagementInterface $checkoutGuestPaymentInformationManagement,
        PaylinePaymentManagement $paylinePaymentManagement,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        PaylineCartManagement $paylineCartManagement
    )
    {
        $this->checkoutGuestPaymentInformationManagement = $checkoutGuestPaymentInformationManagement;
        $this->paylinePaymentManagement = $paylinePaymentManagement;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->paylineCartManagement = $paylineCartManagement;
        $this->cartRepository = $cartRepository;
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
        $this->paylineCartManagement->handleReserveCartOrderId($quoteIdMask->getQuoteId());
        $result = $this->paylinePaymentManagement->wrapCallPaylineApiDoWebPaymentFacade($quoteIdMask->getQuoteId());
        return $result;
    }
}
