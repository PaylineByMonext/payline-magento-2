<?php

namespace Monext\Payline\Model;

use Magento\Checkout\Api\GuestPaymentInformationManagementInterface;
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
     * @var GuestPaymentInformationManagementInterface 
     */
    protected $guestPaymentInformationManagement;
    
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
        GuestPaymentInformationManagementInterface $guestPaymentInformationManagement,
        PaylinePaymentManagement $paylinePaymentManagement,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        PaylineCartManagement $paylineCartManagement
    )
    {
        $this->guestPaymentInformationManagement = $guestPaymentInformationManagement;
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
        $this->guestPaymentInformationManagement->savePaymentInformation($cartId, $email, $paymentMethod, $billingAddress);
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        $this->paylineCartManagement->reserveCartOrderId($quoteIdMask->getQuoteId());
        $result = $this->paylinePaymentManagement->wrapCallPaylineApiDoWebPaymentFacade($quoteIdMask->getQuoteId());
        return $result;
    }
}
