<?php

namespace Monext\Payline\Model;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Monext\Payline\Helper\Constants as HelperConstants;
use Monext\Payline\Model\ContractFactory;
use Monext\Payline\Model\ResourceModel\Contract\Collection as ContractCollection;
use Monext\Payline\Model\ResourceModel\Contract\CollectionFactory as ContractCollectionFactory;
use Monext\Payline\PaylineApi\Client as PaylineApiClient;
use Monext\Payline\PaylineApi\Request\GetMerchantSettingsFactory as RequestGetMerchantSettingsFactory;

class ContractManagement
{
    /**
     * @var CacheInterface 
     */
    protected $cache;
    
    /**
     * @var ContractFactory 
     */
    protected $contractFactory;
    
    /**
     * @var PaylineApiClient
     */
    protected $paylineApiClient;
    
    /**
     * @var RequestGetMerchantSettingsFactory
     */
    protected $requestGetMerchantSettingsFactory;
    
    /**
     * @var ContractCollectionFactory 
     */
    protected $contractCollectionFactory;
    
    /**
     * @var ScopeConfigInterface 
     */
    protected $scopeConfig;
    
    /**
     * @var ContractCollection 
     */
    protected $usedContracts;
    
    public function __construct(
        CacheInterface $cache,
        ContractFactory $contractFactory,
        PaylineApiClient $paylineApiClient,
        RequestGetMerchantSettingsFactory $requestGetMerchantSettingsFactory,
        ContractCollectionFactory $contractCollectionFactory,
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->cache = $cache;
        $this->contractFactory = $contractFactory;
        $this->paylineApiClient = $paylineApiClient;
        $this->requestGetMerchantSettingsFactory = $requestGetMerchantSettingsFactory;
        $this->contractCollectionFactory = $contractCollectionFactory;
        $this->scopeConfig = $scopeConfig;
    }
    
    public function refreshContracts()
    {
        $this->cache->remove(HelperConstants::CACHE_KEY_MERCHANT_CONTRACT_IMPORT_FLAG);
        return $this;
    }
    
    public function importContracts()
    {
        $contractsFlag = $this->cache->load(HelperConstants::CACHE_KEY_MERCHANT_CONTRACT_IMPORT_FLAG);
        
        if(!$contractsFlag) {
            $request = $this->requestGetMerchantSettingsFactory->create();
            $response = $this->paylineApiClient->callGetMerchantSettings($request);
            
            if($response->isSuccess()) {
                // TODO Create a contract repository class
                $contractCollection = $this->contractCollectionFactory->create();

                foreach($response->getContractsData() as $contractData) {
                    $contract = $contractCollection->getItemByColumnValue('number', $contractData['number']);
                    if(!$contract || !$contract->getId()) {
                        $contract = $this->contractFactory->create();
                    }
                                        
                    $contract->addData($contractData);
                    $contract->setIsUpdated(1);
                    $contract->save();
                }

                foreach($contractCollection as $contract) {
                    if(!$contract->getIsUpdated()) {
                        $contract->delete();
                    }
                }
                
                $this->cache->save("1", HelperConstants::CACHE_KEY_MERCHANT_CONTRACT_IMPORT_FLAG);
            }
        }
        
        return $this;
    }
    
    public function getUsedContracts()
    {
        if(!isset($this->usedContracts)) {
            $this->usedContracts = $this->contractCollectionFactory->create()
                ->addFieldToFilter('id', ['in' => $this->scopeConfig->getValue(HelperConstants::CONFIG_PATH_PAYLINE_GENERAL_CONTRACTS)]);
        }
        
        return $this->usedContracts;
    }
}

