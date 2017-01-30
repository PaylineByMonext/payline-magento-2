<?php

namespace Monext\Payline\PaylineApi;

use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Monext\Payline\Helper\Constants as HelperConstants;
use Monext\Payline\PaylineApi\PaylineSDKFactory;
use Monext\Payline\PaylineApi\Request\DoCapture as RequestDoCapture;
use Monext\Payline\PaylineApi\Request\DoWebPayment as RequestDoWebPayment;
use Monext\Payline\PaylineApi\Request\GetMerchantSettings as RequestGetMerchantSettings;
use Monext\Payline\PaylineApi\Request\GetWebPaymentDetails as RequestGetWebPaymentDetails;
use Monext\Payline\PaylineApi\Response\DoCapture as ResponseDoCapture;
use Monext\Payline\PaylineApi\Response\DoCaptureFactory as ResponseDoCaptureFactory;
use Monext\Payline\PaylineApi\Response\DoWebPayment as ResponseDoWebPayment;
use Monext\Payline\PaylineApi\Response\DoWebPaymentFactory as ResponseDoWebPaymentFactory;
use Monext\Payline\PaylineApi\Response\GetMerchantSettings as ResponseGetMerchantSettings;
use Monext\Payline\PaylineApi\Response\GetMerchantSettingsFactory as ResponseGetMerchantSettingsFactory;
use Monext\Payline\PaylineApi\Response\GetWebPaymentDetails as ResponseGetWebPaymentDetails;
use Monext\Payline\PaylineApi\Response\GetWebPaymentDetailsFactory as ResponseGetWebPaymentDetailsFactory;
use Monolog\Logger as LoggerConstants;
use Payline\PaylineSDK;
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
     * @var ResponseDoCaptureFactory
     */
    protected $responseDoCaptureFactory;
    
    /**
     * @var ResponseGetMerchantSettingsFactory 
     */
    protected $responseGetMerchantSettingsFactory;
    
    /**
     * @var ResponseGetWebPaymentDetailsFactory 
     */
    protected $responseGetWebPaymentDetailsFactory;
    
    /**
     * @var Logger
     */
    protected $logger;
    
    /**
     * @var EncryptorInterface
     */
    protected $encryptor;
    
    public function __construct(
        PaylineSDKFactory $paylineSDKFactory,
        ScopeConfigInterface $scopeConfig,
        ResponseDoWebPaymentFactory $responseDoWebPaymentFactory,
        ResponseDoCaptureFactory $responseDoCaptureFactory,
        ResponseGetMerchantSettingsFactory $responseGetMerchantSettingsFactory,
        ResponseGetWebPaymentDetailsFactory $responseGetWebPaymentDetailsFactory,
        Logger $logger,
        EncryptorInterface $encryptor
    )
    {
        $this->paylineSDKFactory = $paylineSDKFactory;
        $this->scopeConfig = $scopeConfig;
        $this->responseDoWebPaymentFactory = $responseDoWebPaymentFactory;
        $this->responseDoCaptureFactory = $responseDoCaptureFactory;
        $this->responseGetMerchantSettingsFactory = $responseGetMerchantSettingsFactory;
        $this->responseGetWebPaymentDetailsFactory = $responseGetWebPaymentDetailsFactory;
        $this->logger = $logger;
        $this->encryptor = $encryptor;
    }
    
    /**
     * @param RequestDoWebPayment $request
     * @return ResponseDoWebPayment
     */
    public function callDoWebPayment(RequestDoWebPayment $request)
    {
        $this->initPaylineSDK();

        $response = $this->responseDoWebPaymentFactory->create();
        $response->fromData(
            $this->paylineSDK->doWebPayment($request->getData())
        );

        if($this->scopeConfig->getValue(HelperConstants::CONFIG_PATH_PAYLINE_GENERAL_DEBUG)) {
            $this->logger->log(LoggerConstants::DEBUG, print_r($request->getData(), true));
            $this->logger->log(LoggerConstants::DEBUG, print_r($response->getData(), true));
        }

        return $response;
    }
    
    /**
     * @param RequestDoCapture $request
     * @return ResponseDoCapture
     */
    public function callDoCapture(RequestDoCapture $request)
    {
        $this->initPaylineSDK();

        $response = $this->responseDoCaptureFactory->create();
        $response->fromData(
            $this->paylineSDK->doCapture($request->getData())
        );

        if($this->scopeConfig->getValue(HelperConstants::CONFIG_PATH_PAYLINE_GENERAL_DEBUG)) {
            $this->logger->log(LoggerConstants::DEBUG, print_r($request->getData(), true));
            $this->logger->log(LoggerConstants::DEBUG, print_r($response->getData(), true));
        }

        return $response;
    }
    
    /**
     * @param RequestGetMerchantSettings $request
     * @return ResponseGetMerchantSettings
     */
    public function callGetMerchantSettings(RequestGetMerchantSettings $request)
    {
        $this->initPaylineSDK();

        $response = $this->responseGetMerchantSettingsFactory->create();
        $response->fromData(
            $this->paylineSDK->getMerchantSettings($request->getData())
        );

        return $response;
    }
    
    /**
     * @param RequestGetWebPaymentDetails $request
     * @return ResponseGetWebPaymentDetails
     */
    public function callGetWebPaymentDetails(RequestGetWebPaymentDetails $request)
    {
        $this->initPaylineSDK();

        $response = $this->responseGetWebPaymentDetailsFactory->create();
        $response->fromData(
            $this->paylineSDK->getWebPaymentDetails($request->getData())
        );

        return $response;
    }
    
    protected function initPaylineSDK()
    {
        if(!isset($this->paylineSDK)) {
            // TODO Handle Proxy
            $paylineSdkParams = array(
                'merchant_id' => $this->scopeConfig->getValue(HelperConstants::CONFIG_PATH_PAYLINE_GENERAL_MERCHANT_ID),
                'access_key' => $this->encryptor->decrypt($this->scopeConfig->getValue(HelperConstants::CONFIG_PATH_PAYLINE_GENERAL_ACCESS_KEY)),
                'proxy_host' => null,
                'proxy_port' => null,
                'proxy_login' => null,
                'proxy_password' => null,
                'environment' => $this->scopeConfig->getValue(HelperConstants::CONFIG_PATH_PAYLINE_GENERAL_ENVIRONMENT),
                'pathLog' => BP . '/var/log/payline_sdk/',
                'logLevel' => LoggerConstants::INFO,
            );
            
            if($this->scopeConfig->getValue(HelperConstants::CONFIG_PATH_PAYLINE_GENERAL_DEBUG)) {
                $this->logger->log(LoggerConstants::DEBUG, print_r($paylineSdkParams, true));
            }
            
            $this->paylineSDK = $this->paylineSDKFactory->create($paylineSdkParams);
        }
        
        return $this;
    }
}