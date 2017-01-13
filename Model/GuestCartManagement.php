<?php

namespace Monext\Payline\Model;

use Magento\Quote\Api\GuestCartManagementInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Monext\Payline\Model\OrderIncrementIdTokenFactory as OrderIncrementIdTokenFactory;

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
    
    /**
     * @var OrderIncrementIdTokenFactory
     */
    protected $orderIncrementIdTokenFactory;
    
    public function __construct(
        GuestCartManagementInterface $guestCartManagement,
        QuoteFactory $quoteFactory,
        OrderIncrementIdTokenFactory $orderIncrementIdTokenFactory,
        QuoteIdMaskFactory $quoteIdMaskFactory
    )
    {
        $this->guestCartManagement = $guestCartManagement;
        $this->quoteFactory = $quoteFactory;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->orderIncrementIdTokenFactory = $orderIncrementIdTokenFactory;
    }
    
    public function placeOrderByToken($token)
    {
        $orderIncrementId = $this->orderIncrementIdTokenFactory->create()->getOrderIncrementIdByToken($token);
        // TODO Use QuoteRepository instead of quote::load
        $quote = $this->quoteFactory->create()->load($orderIncrementId, 'reserved_order_id');
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($quote->getId(), 'quote_id');
        $this->guestCartManagement->placeOrder($quoteIdMask->getMaskedId());
        return $this;
    }
}

