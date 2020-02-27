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
        Logger $loggerPayline
    ) {
        parent::__construct($context, $paylineLogger);
        $this->paylinePaymentManagement = $paylinePaymentManagement;
        $this->loggerPayline = $loggerPayline;
    }

    public function execute()
    {
        try {
            $this->paylinePaymentManagement->synchronizePaymentWithPaymentGatewayFacade($this->getToken(), false);
        } catch (\Exception $e) {
            $this->loggerPayline->critical(__CLASS__. ' : ' .__FUNCTION__);
            $this->loggerPayline->critical('Token # '.$this->getToken());
            $this->loggerPayline->critical($e->getMessage());
            $this->loggerPayline->debug($e->getMessage());
        }

        $resultRaw = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $resultRaw->setContents('');
        return $resultRaw;
    }
}
