<?php

namespace Monext\Payline\Model\Method\WebPayment;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Payment\Model\MethodInterface;
use Monext\Payline\Helper\Constants as HelperConstants;
use Monext\Payline\Model\ContractManagement;
use Monext\Payline\Model\Method\AbstractMethodConfigProvider;

class NxConfigProvider extends AbstractMethodConfigProvider
{
    /**
     * @var ContractManagement
     */
    protected $contractManagement;

    /**
     * @var MethodInterface
     */
    protected $method;

    public function __construct(
        PaymentHelper $paymentHelper,
        AssetRepository $assetRepository,
        ContractManagement $contractManagement,
        UrlInterface $urlBuilder,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($paymentHelper, $assetRepository, $contractManagement, $urlBuilder, $scopeConfig);
        $this->method = $this->paymentHelper->getMethodInstance(HelperConstants::WEB_PAYMENT_NX);
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    public function getConfig()
    {
        $config = array();
        $config['payment']['paylineWebPaymentNx']['redirect_message'] = $this->getMethodConfigData('redirect_message');
        return $config;
    }
}
