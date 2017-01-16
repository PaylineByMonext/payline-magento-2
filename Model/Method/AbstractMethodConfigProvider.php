<?php

namespace Monext\Payline\Model\Method;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Payment\Helper\Data as PaymentHelper;

abstract class AbstractMethodConfigProvider implements ConfigProviderInterface
{
    protected $paymentHelper;

    protected $method;
    
    /**
     * @var AssetRepository;
     */
    protected $assetRepository;
    
    public function __construct(
        PaymentHelper $paymentHelper,
        AssetRepository $assetRepository
    ) {
        $this->paymentHelper = $paymentHelper;
        $this->assetRepository = $assetRepository;
    }

    public function getConfig()
    {
        $config = [];
        
        $config['payment']['payline']['ccLogoSrc'] = $this->assetRepository->getUrlWithParams('Monext_Payline::images/cc_logo.png', ['_secure' => true]);

        return $config;
    }

    protected function getMethodConfigData($fieldName)
    {
        return $this->method->getConfigData($fieldName);
    }
}