<?php

namespace Monext\Payline\Plugin\Model\ResourceModel\Order\Handler;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\Handler\State;
use Monext\Payline\Helper\Constants;

class StatePlugin
{
    public function afterCheck(State $subject, $result, Order $order)
    {
        if (
            !$order->isCanceled()
            && $order->getState() === Order::STATE_COMPLETE
            && $order->getStatus() === $order->getConfig()->getStateDefaultStatus(Order::STATE_COMPLETE)
            && $order->getPayment()->getMethod() === Constants::WEB_PAYMENT_NX
            && $order->getPayment()->getPaiementCompleted() === false
        ) {
            $order->setStatus(Constants::ORDER_STATUS_PAYLINE_CYCLE_PAYMENT_CAPTURE);
        }
        return $result;
    }
}
