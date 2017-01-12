<?php

namespace Monext\Payline\Model;

use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteFactory;

class CartManagement
{
    /**
     * @var CartRepositoryInterface 
     */
    protected $cartRepository;
    
    /**
     * @var CartManagementInterface 
     */
    protected $cartManagement;
    
    /**
     * @var QuoteFactory 
     */
    protected $quoteFactory;
    
    public function __construct(
        CartRepositoryInterface $cartRepository,
        CartManagementInterface $cartManagement,
        QuoteFactory $quoteFactory
    )
    {
        $this->cartRepository = $cartRepository;
        $this->cartManagement = $cartManagement;
        $this->quoteFactory = $quoteFactory;
    }
    
    public function reserveCartOrderId($cartId, $forceReserve = false)
    {
        $cart = $this->cartRepository->getActive($cartId);
        
        if($forceReserve) {
            $cart->setReservedOrderId(null);
        }
        
        if(!$cart->getReservedOrderId()) {
            $cart->reserveOrderId();
            $this->cartRepository->save($cart);
        }
        
        return $this;
    }
    
    public function placeOrderFromCartReservedOrderId($reservedOrderId)
    {
        // TODO Use QuoteRepository instead of quote::load
        $quote = $this->quoteFactory->create()->load($reservedOrderId, 'reserved_order_id');
        $this->cartManagement->placeOrder($quote->getId());
        return $this;
    }
}

