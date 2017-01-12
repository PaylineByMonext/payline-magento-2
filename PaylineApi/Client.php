<?php

namespace Monext\Payline\PaylineApi;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Monext\Payline\Helper\Constants as HelperConstants;
use Monext\Payline\PaylineApi\Request\DoWebPayment as RequestDoWebPayment;
use Monext\Payline\PaylineApi\Response\DoWebPayment as ResponseDoWebPayment;
use Monext\Payline\PaylineApi\Response\DoWebPaymentFactory as ResponseDoWebPaymentFactory;
use Monext\Payline\PaylineApi\Request\GetMerchantSettings as RequestGetMerchantSettings;
use Monext\Payline\PaylineApi\Response\GetMerchantSettings as ResponseGetMerchantSettings;
use Monext\Payline\PaylineApi\Response\GetMerchantSettingsFactory as ResponseGetMerchantSettingsFactory;
use Monolog\Logger as LoggerConstants;
use Payline\PaylineSDK;
use Payline\PaylineSDKFactory;
use Psr\Log\LoggerInterface as Logger;

class Client
{
    /**
     * @var PaylineSDKFactory
     */
    protected $paylineSDKFactory;
    
    /**
     * @var PaylineSDK 
     */
    protected $paylineSDK;    
    
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    
    /**
     * @var ResponseDoWebPaymentFactory
     */
    protected $responseDoWebPaymentFactory;
    
    /**
     * @var ResponseGetMerchantSettingsFactory 
     */
    protected $responseGetMerchantSettingsFactory;
    
    /**
     * @var Logger
     */
    protected $logger;
    
    public function __construct(
        PaylineSDKFactory $paylineSDKFactory,
        ScopeConfigInterface $scopeConfig,
        ResponseDoWebPaymentFactory $responseDoWebPaymentFactory,
        ResponseGetMerchantSettingsFactory $responseGetMerchantSettingsFactory,
        Logger $logger
    )
    {
        $this->paylineSDKFactory = $paylineSDKFactory;
        $this->scopeConfig = $scopeConfig;
        $this->responseDoWebPaymentFactory = $responseDoWebPaymentFactory;
        $this->responseGetMerchantSettingsFactory = $responseGetMerchantSettingsFactory;
        $this->logger = $logger;
        $this->initPaylineSDK();
    }
    
    /**
     * @param RequestDoWebPayment $request
     * @return ResponseDoWebPayment
     */
    public function callDoWebPayment(RequestDoWebPayment $request)
    {
        $response = $this->responseDoWebPaymentFactory->create();
        $response->fromData(
            $this->paylineSDK->doWebPayment($request->getData())
        );

        $this->logger->log(LoggerConstants::DEBUG, print_r($request->getData(), true));
        $this->logger->log(LoggerConstants::DEBUG, print_r($response->getData(), true));
        
        if(!$response->isSuccess()) {
            throw new \Exception($response->getShortErrorMessage());
        }
        
        return $response;
    }
    
    /**
     * @param RequestGetMerchantSettings $request
     * @return ResponseGetMerchantSettings
     */
    public function callGetMerchantSettings(RequestGetMerchantSettings $request)
    {
        $response = $this->responseGetMerchantSettingsFactory->create();
        $response->fromData(
            $this->paylineSDK->getMerchantSettings($request->getData())
        );
        
        if(!$response->isSuccess()) {
            throw new \Exception($response->getShortErrorMessage());
        }
        
        return $response;
    }
    
    protected function initPaylineSDK()
    {
        if(!isset($this->paylineSDK)) {
            // TODO Handle Proxy
            $paylineSdkParams = array(
                'merchant_id' => $this->scopeConfig->getValue(HelperConstants::CONFIG_PATH_PAYMENT_PAYLINE_MERCHANT_ID), 
                'access_key' => $this->scopeConfig->getValue(HelperConstants::CONFIG_PATH_PAYMENT_PAYLINE_ACCESS_KEY),
                'proxy_host' => null,
                'proxy_port' => null,
                'proxy_login' => null,
                'proxy_password' => null,
                'environment' => $this->scopeConfig->getValue(HelperConstants::CONFIG_PATH_PAYMENT_PAYLINE_ENVIRONMENT),
            );
            
            $this->logger->log(LoggerConstants::DEBUG, print_r($paylineSdkParams, true));
            
            $this->paylineSDK = $this->paylineSDKFactory->create($paylineSdkParams);
        }
        
        return $this;
    }
}