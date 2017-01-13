<?php

namespace Monext\Payline\Model;

use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteFactory;
use Monext\Payline\Model\OrderIncrementIdTokenFactory as OrderIncrementIdTokenFactory;

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
    
    /**
     * @var OrderIncrementIdTokenFactory
     */
    protected $orderIncrementIdTokenFactory;
    
    public function __construct(
        CartRepositoryInterface $cartRepository,
        CartManagementInterface $cartManagement,
        OrderIncrementIdTokenFactory $orderIncrementIdTokenFactory,
        QuoteFactory $quoteFactory
    )
    {
        $this->cartRepository = $cartRepository;
        $this->cartManagement = $cartManagement;
        $this->quoteFactory = $quoteFactory;
        $this->orderIncrementIdTokenFactory = $orderIncrementIdTokenFactory;
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
    
    public function placeOrderByToken($token)
    {
        $orderIncrementId = $this->orderIncrementIdTokenFactory->create()->getOrderIncrementIdByToken($token);
        // TODO Use QuoteRepository instead of quote::load
        $quote = $this->quoteFactory->create()->load($orderIncrementId, 'reserved_order_id');
        $this->cartManagement->placeOrder($quote->getId());
        return $this;
    }
}

