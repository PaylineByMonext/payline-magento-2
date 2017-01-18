<?php

namespace Monext\Payline\Model;

use Mage\Sales\Model\Order;

class OrderManagement
{
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
}
