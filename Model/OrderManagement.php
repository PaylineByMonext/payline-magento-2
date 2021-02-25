<?php

namespace Monext\Payline\Model;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use Monext\Payline\Model\OrderIncrementIdTokenFactory;
use Monext\Payline\Helper\Data as HelperData;

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

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var OrderSender
     */
    protected $orderSender;

    public function __construct(
        OrderIncrementIdTokenFactory $orderIncrementIdTokenFactory,
        OrderFactory $orderFactory,
        HelperData $helperData,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender
    ) {
        $this->orderFactory = $orderFactory;
        $this->orderIncrementIdTokenFactory = $orderIncrementIdTokenFactory;
        $this->helperData = $helperData;
        $this->orderSender = $orderSender;
    }

    public function handleSetOrderStateStatus(Order $order, $state, $status, $message = null)
    {
        $status = $this->helperData->getMatchingConfigurableStatus($order, $status);
        if ($state == Order::STATE_CANCELED) {
            $this->handleOrderCancellation($order, $status);
        } else {
            if (!empty($state)) {
                $order->setState($state);
            }

            if (!empty($status)) {
                $order->setStatus($status);
            }
        }

        if (!empty($message)) {
            $order->addStatusHistoryComment($message);
        }
    }

    protected function handleOrderCancellation(Order $order, $status)
    {
        if ($order->canCancel()) {
            $order->cancel();
        } else {
            $order->setState(Order::STATE_CANCELED);
            // TODO check stock
        }

        if (!empty($status)) {
            $order->setStatus($status);
        }
    }

    /**
     * @param $token
     * @return Order
     */
    public function getOrderByToken($token)
    {
        $orderIncrementId = $this->orderIncrementIdTokenFactory->create()->getOrderIncrementIdByToken($token);
        return $this->orderFactory->create()->load($orderIncrementId, 'increment_id');
    }

    public function checkOrderPaymentFromPayline(\Magento\Sales\Model\Order $order)
    {
        if (!$this->helperData->isPaymentFromPayline($order->getPayment())) {
            throw new \Exception('Invalid Payment Method');
        }

        return $this;
    }

    public function sendNewOrderEmail(\Magento\Sales\Model\Order $order)
    {
        if ($order->getCanSendNewEmailFlag()) {
            try {
                $this->orderSender->send($order);
            } catch (\Exception $e) {
                // TODO Log
            }
        }
    }
}
