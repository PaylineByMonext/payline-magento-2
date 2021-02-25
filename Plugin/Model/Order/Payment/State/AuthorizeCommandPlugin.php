<?php

namespace Monext\Payline\Plugin\Model\Order\Payment\State;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order as SalesOrder;
use Magento\Sales\Model\Order\Payment\State\AuthorizeCommand;
use Monext\Payline\Helper\Constants as HelperConstants;
use Monext\Payline\Model\OrderManagement as PaylineOrderManagement;

class AuthorizeCommandPlugin
{
    /**
     * @var PaylineOrderManagement
     */
    protected $paylineOrderManagement;

    /**
     * @var \Monext\Payline\Helper\Data
     */
    protected $helperData;

    public function __construct(
        PaylineOrderManagement $paylineOrderManagement,
        \Monext\Payline\Helper\Data $helperData
    ) {
        $this->paylineOrderManagement = $paylineOrderManagement;
        $this->helperData = $helperData;
    }

    public function afterExecute(
        AuthorizeCommand $subject,
        $result,
        OrderPaymentInterface $payment,
        $amount,
        OrderInterface $order
    ) {
        if ($order->getState() == SalesOrder::STATE_PROCESSING
        && $this->helperData->isPaymentFromPayline($order->getPayment())) {
            $this->paylineOrderManagement->handleSetOrderStateStatus(
                $order,
                SalesOrder::STATE_PROCESSING,
                HelperConstants::ORDER_STATUS_PAYLINE_WAITING_CAPTURE
            );
        }

        return $result;
    }
}
