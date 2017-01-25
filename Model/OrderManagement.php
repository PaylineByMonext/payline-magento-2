<?php

namespace Monext\Payline\Model;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use Monext\Payline\model\OrderIncrementIdTokenFactory;

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

    public function handleSetOrderStateStatus(Order $order, $state, $status, $message = null)
    {
        if($state == Order::STATE_CANCELED) {
            $this->handleOrderCancellation($order, $status);
        } else {
            if(!empty($state)) {
                $order->setState($state);
            }

            if(!empty($status)) {
                $order->setStatus($status);
            }
        }

        if(!empty($message)) {
            $order->addStatusHistoryComment($message);
        }
    }

    protected function handleOrderCancellation(Order $order, $status)
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
