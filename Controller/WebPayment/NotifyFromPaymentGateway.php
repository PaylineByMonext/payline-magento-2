<?php

namespace Monext\Payline\Controller\WebPayment;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Monext\Payline\Controller\Action;
use Monext\Payline\Model\PaymentManagement as PaylinePaymentManagement;
use Psr\Log\LoggerInterface as Logger;

class NotifyFromPaymentGateway extends Action
{
    /**
     * @var PaylinePaymentManagement
     */
    protected $paylinePaymentManagement;

    /**
     * @var Logger
     */
    protected $paylineLogger;

    public function __construct(
        Context $context,
        PaylinePaymentManagement $paylinePaymentManagement,
        Logger $paylineLogger
    ) {
        parent::__construct($context, $paylineLogger);
        $this->paylinePaymentManagement = $paylinePaymentManagement;
        $this->paylineLogger = $paylineLogger;
    }

    public function execute()
    {
        try {
            $this->paylinePaymentManagement->synchronizePaymentWithPaymentGatewayFacade($this->getToken(), false);
        } catch (\Exception $e) {
            $this->loggerPayline->critical(__METHOD__, ['token'=>$this->getToken(), 'exception'=>['message'=>$e->getMessage(), 'code'=>$e->getCode()]]);
        }

        $resultRaw = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $resultRaw->setContents('');
        return $resultRaw;
    }
}
