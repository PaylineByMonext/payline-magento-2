<?php

namespace Monext\Payline\Model\Method;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Payment\Model\MethodInterface;
use Monext\Payline\Helper\Constants as HelperConstants;
use Monext\Payline\Model\ContractManagement;

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

    public function getConfig()
    {
        $config = [];
        $config['payline']['general']['environment'] = $this->scopeConfig->getValue(HelperConstants::CONFIG_PATH_PAYLINE_GENERAL_ENVIRONMENT);
        $config['payline']['general']['contracts'] = [];
        
        $contractCollection = $this->contractManagement->getUsedContracts();
                
        foreach ($contractCollection as $contract) {
            $config['payline']['general']['contracts'][] = [
                'id' => $contract->getId(),
                'cardType' => $contract->getCardType(),
                'logo' => $this->getCardTypeLogoUrl($contract->getCardType()),
                'label' => $contract->getLabel(),
            ];
        }
        
        return $config;
    }

    protected function getMethodConfigData($fieldName)
    {
        return $this->method->getConfigData($fieldName);
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
    
    abstract public function getCardTypeImageFileNames();
}
