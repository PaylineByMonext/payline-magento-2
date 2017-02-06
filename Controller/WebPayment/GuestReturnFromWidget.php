<?php

namespace Monext\Payline\Controller\WebPayment;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\RawFactory as ResultRawFactory;
use Magento\Framework\View\Element\TemplateFactory;
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
        PaylineGuestPaymentManagement $paylineGuestPaymentManagement,
        ResultRawFactory $resultRawFactory,
        TemplateFactory $templateFactory
    )
    {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->paylineGuestPaymentManagement = $paylineGuestPaymentManagement;
        $this->templateFactory = $templateFactory;
    }
    
    public function execute() 
    {
        $isSuccess = true;

        try {
            $this->paylineGuestPaymentManagement->applyPaymentReturnStrategyFromToken($this->getToken());
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

