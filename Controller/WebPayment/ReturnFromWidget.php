<?php

namespace Monext\Payline\Controller\WebPayment;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\RawFactory as ResultRawFactory;
use Magento\Framework\View\Element\TemplateFactory;
use Monext\Payline\Controller\Action;
use Monext\Payline\Model\PaymentManagement as PaylinePaymentManagement;

class ReturnFromWidget extends Action
{
    /**
     * @var PaylinePaymentManagement
     */
    protected $paylinePaymentManagement;

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
        PaylinePaymentManagement $paylinePaymentManagement,
        ResultRawFactory $resultRawFactory,
        TemplateFactory $templateFactory
    )
    {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->paylinePaymentManagement = $paylinePaymentManagement;
        $this->templateFactory = $templateFactory;
    }

    public function execute() 
    {
        $isSuccess = true;

        try {
            $this->paylinePaymentManagement->synchronizePaymentWithPaymentGatewayFacade($this->getToken(), true);
        } catch(\Exception $e) {
            $isSuccess = false;
        }

        $resultRaw = $this->resultRawFactory->create();

        $block = $this->templateFactory->create();
        $block->setTemplate($isSuccess ? 'Monext_Payline::web_payment/widget_iframe_success.phtml' : 'Monext_Payline::web_payment/widget_iframe_failure.phtml');
        $resultRaw->setContents($block->toHtml());

        return $resultRaw;
    }
}

