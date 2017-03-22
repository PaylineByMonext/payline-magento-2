<?php

namespace Monext\Payline\Plugin\Model\Order\Payment\State;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order as SalesOrder;
use Magento\Sales\Model\Order\Payment\State\CaptureCommand;
use Monext\Payline\Helper\Constants as HelperConstants;
use Monext\Payline\Model\OrderManagement as PaylineOrderManagement;

class CaptureCommandPlugin
{
    /**
     * @var PaylineOrderManagement 
     */
    protected $paylineOrderManagement;

    public function __construct(PaylineOrderManagement $paylineOrderManagement)
    {
        $this->paylineOrderManagement = $paylineOrderManagement;
    }

    public function aroundExecute(
        CaptureCommand $subject, 
        \Closure $proceed, 
        OrderPaymentInterface $payment, 
        $amount, 
        OrderInterface $order)
    {
        $result = $proceed($payment, $amount, $order);

        if($order->getState() == SalesOrder::STATE_PROCESSING
        && $order->getPayment()->getMethod() == HelperConstants::WEB_PAYMENT_CPT) {
            $this->paylineOrderManagement->handleSetOrderStateStatus(
                $order, SalesOrder::STATE_PROCESSING, HelperConstants::ORDER_STATUS_PAYLINE_CAPTURED
            );
        }

        return $result;
    }
}
