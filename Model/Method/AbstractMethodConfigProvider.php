<?php

namespace Monext\Payline\Model\Method;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Payment\Model\MethodInterface;
use Monext\Payline\Model\ContractManagement;
use Monext\Payline\PaylineApi\Constants as PaylineApiConstants;

abstract class AbstractMethodConfigProvider implements ConfigProviderInterface
{
    /**
     * @var PaymentHelper
     */
    protected $paymentHelper;

    /**
     * @var MethodInterface
     */
    protected $method;

    /**
     * @var AssetRepository;
     */
    protected $assetRepository;

    /**
     * @var ContractManagement
     */
    protected $contractManagement;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        PaymentHelper $paymentHelper,
        AssetRepository $assetRepository,
        ContractManagement $contractManagement,
        UrlInterface $urlBuilder,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->paymentHelper = $paymentHelper;
        $this->assetRepository = $assetRepository;
        $this->contractManagement = $contractManagement;
        $this->urlBuilder = $urlBuilder;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return array
     */
    abstract public function getConfig();

    /**
     * @param $fieldName
     * @return mixed
     * @throws \ReflectionException
     */
    protected function getMethodConfigData($fieldName)
    {
        if(!isset($this->method)) {
            throw new \ReflectionException('Property method not init');
        }
        return $this->method->getConfigData($fieldName);
    }

    /**
     * @return string[]
     */
    public function getCardTypeImageFileNames()
    {
        return [
            PaylineApiConstants::PAYMENT_CONTRACT_CARD_TYPE_CB => 'cb.png',
            PaylineApiConstants::PAYMENT_CONTRACT_CARD_TYPE_CB_3DS => 'cb.png',
            PaylineApiConstants::PAYMENT_CONTRACT_CARD_TYPE_PAYPAL => 'paypal.png',
            PaylineApiConstants::PAYMENT_CONTRACT_CARD_TYPE_AMEX => 'amex.gif',
            PaylineApiConstants::PAYMENT_CONTRACT_CARD_TYPE_ONEY => 'oney.png',
            PaylineApiConstants::PAYMENT_CONTRACT_CARD_TYPE_3XONEY => 'oney.png',
            PaylineApiConstants::PAYMENT_CONTRACT_CARD_TYPE_4XONEY => 'oney.png',
            PaylineApiConstants::PAYMENT_CONTRACT_CARD_TYPE_3XONEY_SF => 'oney.png',
            PaylineApiConstants::PAYMENT_CONTRACT_CARD_TYPE_4XONEY_SF => 'oney.png',
        ];
    }

    public function getCardTypeLogoUrl($cardType)
    {
        try {
            $fileNames = $this->getCardTypeImageFileNames();

            if (!isset($fileNames[$cardType])) {
                throw new \Exception(__('Payline card type logo url does not exists.'));
            }

            return $this->assetRepository->getUrlWithParams('Monext_Payline::images/'.$fileNames[$cardType], ['_secure' => true]);
        } catch (\Exception $e) {
            return $this->urlBuilder->getUrl('', ['_direct' => 'core/index/notFound']);
        }
    }
}
