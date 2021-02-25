<?php

namespace Monext\Payline\Plugin\Model\ResourceModel\Order\Handler;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\Handler\State;
use Monext\Payline\Helper\Constants;
use Monext\Payline\Model\PaymentManagement;
use Psr\Log\LoggerInterface as Logger;

class StatePlugin
{
    /**
     * @var PaymentManagement
     */
    protected $paymentManagement;
    /**
     * @var Logger
     */
    public $paylineLogger;

    public function __construct(PaymentManagement $paymentManagement, Logger $paylineLogger)
    {
        $this->paymentManagement = $paymentManagement;
        $this->paylineLogger = $paylineLogger;
    }

    public function afterCheck(State $subject, $result, Order $order)
    {
        if (
            $order->getPayment()->getMethod() === Constants::WEB_PAYMENT_NX
            && $order->getState() === Order::STATE_COMPLETE
        ) {
            //Vérification de l'état des paiements dans le cas d'une livraison tardive
            if (is_null($order->getPaiementCompleted())) {
                //Ajout du flag save_mode pour ne pas sauvegarder plusieurs fois
                $order->addData(['save_mode' => true]);
                $this->paymentManagement->callPaylinePaymentRecordFacade($order->getPayment(), $order->getPayment()->getAdditionalInformation('payment_record_id'));
                $order->unsetData('save_mode');
            }

            if (
                !$order->isCanceled()
                && $order->getStatus() === $order->getConfig()->getStateDefaultStatus(Order::STATE_COMPLETE)
                && $order->getPaiementCompleted() === false
            ) {
                $order->setStatus(Constants::ORDER_STATUS_PAYLINE_CYCLE_PAYMENT_CAPTURE);
            }
        }

        return $result;
    }
}
