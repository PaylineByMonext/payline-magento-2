<?php

namespace Monext\Payline\Model;

use Magento\Quote\Api\GuestCartManagementInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteIdMaskFactory;

class GuestCartManagement
{
    /**
     * @var GuestCartManagementInterface 
     */
    protected $guestCartManagement;
    
    /**
     * @var QuoteFactory 
     */
    protected $quoteFactory;
    
    /**
     * @var QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;
    
    public function __construct(
        GuestCartManagementInterface $guestCartManagement,
        QuoteFactory $quoteFactory,
        QuoteIdMaskFactory $quoteIdMaskFactory
    )
    {
        $this->guestCartManagement = $guestCartManagement;
        $this->quoteFactory = $quoteFactory;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
    }
    
    public function placeOrderFromCartReservedOrderId($reservedOrderId)
    {
        // TODO Use QuoteRepository instead of quote::load
        $quote = $this->quoteFactory->create()->load($reservedOrderId, 'reserved_order_id');
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($quote->getId(), 'quote_id');
        $this->guestCartManagement->placeOrder($quoteIdMask->getMaskedId());
        return $this;
    }
}

