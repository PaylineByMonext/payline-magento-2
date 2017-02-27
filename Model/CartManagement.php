<?php

namespace Monext\Payline\Model;

use Magento\Checkout\Model\Cart as CheckoutCart;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Model\Order;
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

    /**
     * @var CheckoutCart 
     */
    protected $checkoutCart;

    public function __construct(
        CartRepositoryInterface $cartRepository,
        CartManagementInterface $cartManagement,
        OrderIncrementIdTokenFactory $orderIncrementIdTokenFactory,
        QuoteFactory $quoteFactory,
        CheckoutCart $checkoutCart
    )
    {
        $this->cartRepository = $cartRepository;
        $this->cartManagement = $cartManagement;
        $this->quoteFactory = $quoteFactory;
        $this->orderIncrementIdTokenFactory = $orderIncrementIdTokenFactory;
        $this->checkoutCart = $checkoutCart;
    }

    public function handleReserveCartOrderId($cartId, $forceReserve = false)
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
        $quote = $this->getCartByToken($token);
        $this->cartManagement->placeOrder($quote->getId());
        return $this;
    }

    public function restoreCartFromOrder(Order $order)
    {
        foreach($order->getItemsCollection() as $orderItem) {
            $this->checkoutCart->addOrderItem($orderItem);
        }

        // TODO Handle couponCode

        $this->checkoutCart->save();
        return $this;
    }

    public function getCartByToken($token)
    {
        $orderIncrementId = $this->orderIncrementIdTokenFactory->create()->getOrderIncrementIdByToken($token);
        // TODO Use QuoteRepository instead of quote::load
        return $this->quoteFactory->create()->load($orderIncrementId, 'reserved_order_id');
    }
}

