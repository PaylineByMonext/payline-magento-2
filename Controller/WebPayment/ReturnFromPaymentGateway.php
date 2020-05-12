<?php

namespace Monext\Payline\Controller\WebPayment;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Element\Template;
use Monext\Payline\Controller\Action;
use Monext\Payline\Model\PaymentManagement as PaylinePaymentManagement;

class ReturnFromPaymentGateway extends Action
{
    /**
     * @var PaylinePaymentManagement
     */
    protected $paylinePaymentManagement;

    public function __construct(
        Context $context,
        \Psr\Log\LoggerInterface $loggerPayline,
        PaylinePaymentManagement $paylinePaymentManagement
    )
    {
        parent::__construct($context, $loggerPayline);
        $this->paylinePaymentManagement = $paylinePaymentManagement;
    }

    public function execute()
    {
        $isSuccess = true;
        try {
            $this->paylinePaymentManagement->synchronizePaymentWithPaymentGatewayFacade($this->getToken(), true);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->loggerPayline->critical(__CLASS__. ' : ' .__FUNCTION__);
            $this->loggerPayline->critical('Token # '.$this->getToken());
            $this->loggerPayline->critical($e->getMessage());
            $isSuccess = false;
        }

        return $this->getRedirect($isSuccess);
    }
}
