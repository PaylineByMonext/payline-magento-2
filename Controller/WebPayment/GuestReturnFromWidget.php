<?php

namespace Monext\Payline\Controller\WebPayment;

use Magento\Framework\App\Action\Context;
use Monext\Payline\Controller\Action;
use Monext\Payline\Model\GuestPaymentManagement as PaylineGuestPaymentManagement;

class GuestReturnFromWidget extends Action
{
    /**
     * @var PaylineGuestPaymentManagement
     */
    protected $paylineGuestPaymentManagement;

    /**
     * @var ResultRawFactory
     */
    protected $resultRawFactory;

    /**
     * @var TemplateFactory
     */
    protected $templateFactory;

    public function __construct(
        Context $context,
        \Psr\Log\LoggerInterface $loggerPayline,
        PaylineGuestPaymentManagement $paylineGuestPaymentManagement
    )
    {
        parent::__construct($context, $loggerPayline);
        $this->paylineGuestPaymentManagement = $paylineGuestPaymentManagement;
    }

    public function execute()
    {
        $isSuccess = true;
        try {
            $this->paylineGuestPaymentManagement->synchronizePaymentWithPaymentGatewayFacade($this->getToken(), true);
        } catch (\Exception $e) {
            $this->loggerPayline->critical(__CLASS__. ' : ' .__FUNCTION__);
            $this->loggerPayline->critical('Token # '.$this->getToken());
            $this->loggerPayline->critical($e->getMessage());
            $isSuccess = false;
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($isSuccess ? 'checkout/onepage/success' : 'checkout');
        return $resultRedirect;
    }
}
