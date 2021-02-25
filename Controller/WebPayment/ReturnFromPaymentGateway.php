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
            $this->loggerPayline->critical(__METHOD__, ['token'=>$this->getToken(), 'exception'=>['message'=>$e->getMessage(), 'code'=>$e->getCode()]]);
            $this->messageManager->addErrorMessage($e->getMessage());
            $isSuccess = false;
        }

        return $this->getRedirect($isSuccess);
    }
}
