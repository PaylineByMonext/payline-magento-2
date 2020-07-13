<?php

namespace Monext\Payline\Controller\WebPayment;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NotFoundException;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment as OrderPayment;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\OrderRepositoryFactory;
use Monext\Payline\Controller\Action;
use Monext\Payline\Model\PaymentManagement as PaylinePaymentManagement;
use Psr\Log\LoggerInterface as Logger;
use Symfony\Component\Finder\Exception\AccessDeniedException;

class NotifyCyclingPaymentFromPaymentGateway extends Action
{
    /**
     * @var PaylinePaymentManagement
     */
    protected $paylinePaymentManagement;

    /**
     * @var Logger
     */
    protected $paylineLogger;


    /**
     * @var OrderRepositoryFactory
     */
    protected $orderRepositoryFactory;
    /**
     * @var OrderFactory
     */
    protected $orderFactory;


    public function __construct(
        Context $context,
        PaylinePaymentManagement $paylinePaymentManagement,
        Logger $paylineLogger,

        OrderRepositoryFactory $orderRepositoryFactory,
        OrderFactory $orderFactory

    )
    {
        parent::__construct($context, $paylineLogger);
        $this->paylinePaymentManagement = $paylinePaymentManagement;
        $this->paylineLogger = $paylineLogger;
        $this->orderRepositoryFactory = $orderRepositoryFactory;
        $this->orderFactory = $orderFactory;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Layout
     * @example https://magento2-app.payline.determined.fr/payline/webpayment/notifycyclingpaymentfrompaymentgateway?notificationType=BILL&paymentRecordId=474948&walletId=2DcebnXwA0afR8Ale2451602147102417&transactionId=11283230050198&billingRecordDate=20201011&orderRef=000000088
     */
    public function execute()
    {
        $notificationType = $this->getRequest()->getParam('notificationType');
        $paymentRecordId = $this->getRequest()->getParam('paymentRecordId');
        $walletId = $this->getRequest()->getParam('walletId');
        $transactionId = $this->getRequest()->getParam('transactionId');
        $orderRef = $this->getRequest()->getParam('orderRef');
        $billingRecordDate = $this->getRequest()->getParam('billingRecordDate');

        $this->loggerPayline->debug(__METHOD__, ['params' => $this->getRequest()->getParams()]);
        try {
            if (!($notificationType === 'BILL' && $paymentRecordId && $walletId && $transactionId && $orderRef && $billingRecordDate)) {
                throw new AccessDeniedException('Invalid PayLine request');
            }

            /** @var Order $order */
            $order = $this->orderFactory->create();
            $order->loadByIncrementId($orderRef);
            if (!$order->getEntityId()) {
                throw new NotFoundException(__('Order #%1 not exist', $orderRef));
            }

            /** @var OrderPayment $payment */
            $payment = $order->getPayment();
            if ($paymentRecordId !== $payment->getAdditionalInformation('payment_record_id')) {
                throw new AccessDeniedException('Invalid recordId');
            }

            $this->paylinePaymentManagement->callPaylinePaymentRecordFacade($payment, $paymentRecordId);
        } catch (\Exception $e) {
            $this->loggerPayline->critical(__METHOD__, ['token' => $this->getToken(), 'exception' => ['message' => $e->getMessage(), 'code' => $e->getCode()]]);
        }

        $resultRaw = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $resultRaw->setContents('');
        return $resultRaw;
    }
}
