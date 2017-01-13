<?php

namespace Monext\Payline\Model;

use Magento\Framework\App\CacheInterface;
use Monext\Payline\Helper\Constants as HelperConstants;
use Monext\Payline\Model\ContractFactory;
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
    
    public function __construct(
        CacheInterface $cache,
        ContractFactory $contractFactory,
        PaylineApiClient $paylineApiClient,
        RequestGetMerchantSettingsFactory $requestGetMerchantSettingsFactory,
        ContractCollectionFactory $contractCollectionFactory
    )
    {
        $this->cache = $cache;
        $this->contractFactory = $contractFactory;
        $this->paylineApiClient = $paylineApiClient;
        $this->requestGetMerchantSettingsFactory = $requestGetMerchantSettingsFactory;
        $this->contractCollectionFactory = $contractCollectionFactory;
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
            
            $contractCollection = $this->contractCollectionFactory->create();
            foreach($contractCollection as $contract) {
                $contract->delete();
            }
                        
            foreach($response->getContractsData() as $contractData) {
                // TODO Create a crontact repository class
                $contract = $this->contractFactory->create();
                $contract->setData($contractData);
                $contract->save();
            }
            
            $this->cache->save("1", HelperConstants::CACHE_KEY_MERCHANT_CONTRACT_IMPORT_FLAG);
        }
        
        return $this;
    }
}

