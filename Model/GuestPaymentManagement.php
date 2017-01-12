<?php

namespace Monext\Payline\Model;

use Magento\Checkout\Api\GuestPaymentInformationManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Monext\Payline\Api\GuestPaymentManagementInterface as PaylineGuestPaymentManagementInterface;
use Monext\Payline\Api\PaymentManagementInterface as PaylinePaymentManagementInterface;
use Monext\Payline\Model\CartManagement as PaylineCartManagement;
use Monext\Payline\Model\OrderIncrementIdTokenFactory as OrderIncrementIdTokenFactory;

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
     * @var PaylinePaymentManagementInterface
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
    
    /**
     * @var OrderIncrementIdTokenFactory 
     */
    protected $orderIncrementIdTokenFactory;
    
    public function __construct(
        CartRepositoryInterface $cartRepository, 
        GuestPaymentInformationManagementInterface $guestPaymentInformationManagement,
        PaylinePaymentManagementInterface $paylinePaymentManagement,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        PaylineCartManagement $paylineCartManagement,
        OrderIncrementIdTokenFactory $orderIncrementIdTokenFactory
    )
    {
        $this->guestPaymentInformationManagement = $guestPaymentInformationManagement;
        $this->paylinePaymentManagement = $paylinePaymentManagement;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->paylineCartManagement = $paylineCartManagement;
        $this->orderIncrementIdTokenFactory = $orderIncrementIdTokenFactory;
        $this->cartRepository = $cartRepository;
    }
    
    public function savePaymentInformationFacade(
        $cartId,
        $email,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    )
    {
        $this->guestPaymentInformationManagement->savePaymentInformation($cartId, $email, $paymentMethod, $billingAddress);
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        $this->paylineCartManagement->reserveCartOrderId($quoteIdMask->getQuoteId());
        $result = $this->paylinePaymentManagement->wrapCallPaylineApiDoWebPayment($quoteIdMask->getQuoteId());
        $this->orderIncrementIdTokenFactory->create()->associateTokenToOrderIncrementId(
            $this->cartRepository->getActive($quoteIdMask->getQuoteId())->getReservedOrderId(), 
            $result['token']
        );
        return $result;
    }
}
