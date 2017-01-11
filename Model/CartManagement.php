<?php

namespace Monext\Payline\Model;

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;

class CartManagement
{
    /**
     * @var CartRepositoryInterface 
     */
    protected $cartRepository;
    
    public function __construct(CartRepositoryInterface $cartRepository)
    {
        $this->cartRepository = $cartRepository;
    }
    
    public function reserveOrderIdForCart(Quote $cart, $forceReserve = false)
    {
        if($forceReserve) {
            $cart->setReservedOrderId(null);
        }
        
        $cart->reserveOrderId();
        $this->cartRepository->save($cart);
        
        return $this;
    }
}

