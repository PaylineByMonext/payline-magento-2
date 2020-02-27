<?php

namespace Monext\Payline\Controller\WebPayment;

use Magento\Framework\App\Action\Context;
use Monext\Payline\Controller\Action;
use Monext\Payline\Model\PaymentManagement as PaylinePaymentManagement;

class ReturnFromWidget extends Action
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
            $this->loggerPayline->critical(__CLASS__. ' : ' .__FUNCTION__);
            $this->loggerPayline->critical('Token # '.$this->getToken());
            $this->loggerPayline->critical($e->getMessage());
            $this->messageManager->addErrorMessage($e->getMessage());
            $isSuccess = false;
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($isSuccess ? 'checkout/onepage/success' : 'checkout/cart');
        return $resultRedirect;
    }
}
