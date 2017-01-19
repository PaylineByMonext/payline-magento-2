<?php

namespace Monext\Payline\Model;

use Mage\Sales\Model\Order;

class OrderManagement
{
    /**
     * @var OrderIncrementIdTokenFactory
     */
    protected $orderIncrementIdTokenFactory;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    public function __construct(
        OrderIncrementIdTokenFactory $orderIncrementIdTokenFactory,
        OrderFactory $orderFactory
    )
    {
        $this->orderFactory = $orderFactory;
        $this->orderIncrementIdTokenFactory = $orderIncrementIdTokenFactory;
    }

    public function handleOrderCancellation(Order $order, $status)
    {
        if($order->canCancel()) {
            $order->cancel();
            $order->setStatus($status);
        } else {
            $order->setState(Order::STATE_CANCELED)->setStatus($status);
            // TODO check stock
        }
    }

    public function getOrderByToken($token)
    {
        $orderIncrementId = $this->orderIncrementIdTokenFactory->create()->getOrderIncrementIdByToken($token);
        return $this->orderFactory->create()->load($orderIncrementId, 'increment_id');
    }
}
