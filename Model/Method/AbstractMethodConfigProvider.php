<?php

namespace Monext\Payline\Model\Method;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Payment\Model\MethodInterface;
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
    
    public function __construct(
        PaymentHelper $paymentHelper,
        AssetRepository $assetRepository,
        ContractManagement $contractManagement,
        UrlInterface $urlBuilder
    ) {
        $this->paymentHelper = $paymentHelper;
        $this->assetRepository = $assetRepository;
        $this->contractManagement = $contractManagement;
        $this->urlBuilder = $urlBuilder;
    }

    public function getConfig()
    {
        $config = [];
        $config['payment']['paylineContracts']['contracts'] = [];
        
        $contractCollection = $this->contractManagement->getUsedContracts();
                
        foreach($contractCollection as $contract) {
            $config['payment']['paylineContracts']['contracts'][] = [
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
            
            if(!isset($fileNames[$cardType])) {
                throw new \Exception(__('Payline card type logo url does not exists.'));
            }

            return $this->assetRepository->getUrlWithParams('Monext_Payline::images/'.$fileNames[$cardType], ['_secure' => true]);
        } catch (\Exception $e) {
            return $this->urlBuilder->getUrl('', ['_direct' => 'core/index/notFound']);
        }
    }
    
    abstract public function getCardTypeImageFileNames();
}