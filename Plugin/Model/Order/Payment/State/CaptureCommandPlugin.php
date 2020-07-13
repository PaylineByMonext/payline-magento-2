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

    /**
     * @var \Monext\Payline\Helper\Data
     */
    protected $helperData;

    public function __construct(
        PaylineOrderManagement $paylineOrderManagement,
        \Monext\Payline\Helper\Data $helperData
    )
    {
        $this->paylineOrderManagement = $paylineOrderManagement;
        $this->helperData = $helperData;
    }

    public function aroundExecute(
        CaptureCommand $subject,
        \Closure $proceed,
        OrderPaymentInterface $payment,
        $amount,
        OrderInterface $order
    )
    {
        $orderStateBeforeProceed = $order->getState();
        $orderStatusBeforeProceed = $order->getStatus();

        if ($payment->getMethod() === HelperConstants::WEB_PAYMENT_NX) {
            $paylineNxFirstCapture = (int)$payment->getAdditionalInformation()['payment_cycling'][0]['amount'] ?? null;
            if ($paylineNxFirstCapture !== null) {
                $amount = $this->helperData->mapPaylineAmountToMagentoAmount($paylineNxFirstCapture);
            }
        }

        $result = $proceed($payment, $amount, $order);

        if (
            $payment->getMethod() === HelperConstants::WEB_PAYMENT_NX
            && !$payment->getIsTransactionPending()
            && !$payment->getIsFraudDetected()
        ) {
            $methodTitle = $payment->getAdditionalInformation()['method_title'] ?? '';
            $result = __('%1: Captured amount of %2 online.', [$methodTitle, $order->getBaseCurrency()->formatTxt($amount)]);
        }

        if (
            $orderStateBeforeProceed == SalesOrder::STATE_COMPLETE
            && $orderStatusBeforeProceed == HelperConstants::ORDER_STATUS_PAYLINE_CYCLE_PAYMENT_CAPTURE
            && $payment->getMethod() === HelperConstants::WEB_PAYMENT_NX
        ) {
            $order->setState($orderStateBeforeProceed);
            $order->setStatus($orderStatusBeforeProceed);
        } else if (
            $order->getState() == SalesOrder::STATE_PROCESSING
            && $payment->getMethod() === HelperConstants::WEB_PAYMENT_CPT
        ) {
            $this->paylineOrderManagement->handleSetOrderStateStatus(
                $order,
                SalesOrder::STATE_PROCESSING,
                HelperConstants::ORDER_STATUS_PAYLINE_CAPTURED
            );
        }

        return $result;
    }
}
