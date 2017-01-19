<?php

namespace Monext\Payline\Model;

use Magento\Quote\Api\GuestCartManagementInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Monext\Payline\Model\CartManagement as PaylineCartManagement;

class GuestCartManagement
{
    /**
     * @var GuestCartManagementInterface 
     */
    protected $guestCartManagement;
    
    /**
     * @var QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;
    
    /**
     * @var PaylineCartManagement
     */
    protected $paylineCartManagement;
    
    public function __construct(
        GuestCartManagementInterface $guestCartManagement,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        PaylineCartManagement $paylineCartManagement
    )
    {
        $this->guestCartManagement = $guestCartManagement;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->paylineCartManagement = $paylineCartManagement;
    }
    
    public function placeOrderByToken($token)
    {
        $quote = $this->paylineCartManagement->getCartByToken($token);
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($quote->getId(), 'quote_id');
        $this->guestCartManagement->placeOrder($quoteIdMask->getMaskedId());
        return $this;
    }
}

