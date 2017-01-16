<?php

namespace Monext\Payline\Model\Method\WebPayment;

use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Payment\Helper\Data as PaymentHelper;
use Monext\Payline\Helper\Constants as HelperConstants;
use Monext\Payline\Model\Method\AbstractMethodConfigProvider;

class CptConfigProvider extends AbstractMethodConfigProvider
{
    protected $method;

    public function __construct(
        PaymentHelper $paymentHelper,
        AssetRepository $assetRepository
    ) {
        parent::__construct($paymentHelper, $assetRepository);
        $this->method = $this->paymentHelper->getMethodInstance(HelperConstants::WEB_PAYMENT_CPT);
    }

    public function getConfig()
    {
        $config = parent::getConfig();
        
        $config['payment']['paylineWebPaymentCpt']['paymentWorkflow'] = $this->getMethodConfigData('payment_workflow');

        return $config;
    }
}