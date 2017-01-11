<?php

namespace Monext\Payline\Model\Method\WebPayment;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Helper\Data as PaymentHelper;
use Monext\Payline\Helper\Constants as HelperConstants;

class CptConfigProvider implements ConfigProviderInterface
{
    protected $paymentHelper;

    protected $method;

    public function __construct(
        PaymentHelper $paymentHelper
    ) {
        $this->paymentHelper = $paymentHelper;
        $this->method = $this->paymentHelper->getMethodInstance(HelperConstants::WEB_PAYMENT_CPT);
    }

    public function getConfig()
    {
        $config = [
            'payment' => [
                'paylineWebPaymentCpt' => [
                    'paymentWorkflow' => $this->getMethodConfigData('payment_workflow')
                ]
            ]
        ];

        return $config;
    }

    protected function getMethodConfigData($fieldName)
    {
        return $this->method->getConfigData($fieldName);
    }
}