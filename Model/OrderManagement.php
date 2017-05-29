<?php

namespace Monext\Payline\Model;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use Monext\Payline\Model\OrderIncrementIdTokenFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Psr\Log\LoggerInterface as Logger;


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
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        OrderIncrementIdTokenFactory $orderIncrementIdTokenFactory,
        OrderFactory $orderFactory,
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->orderFactory = $orderFactory;
        $this->orderIncrementIdTokenFactory = $orderIncrementIdTokenFactory;
        $this->scopeConfig = $scopeConfig;
    }

    public function handleSetOrderStateStatus(Order $order, $state, $status, $message = null)
    {
        $status = $this->getMatchingConfigurableStatus($order, $status);
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

    protected function getMatchingConfigurableStatus(Order $order, $status)
    {
        $path = 'payment/' . $order->getPayment()->getMethod() . '/order_status_' . $status;
        if($configurableStatus = $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $status = $configurableStatus;
        }
        return $status;
    }
}
