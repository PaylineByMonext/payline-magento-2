<?php

namespace Monext\Payline\Block\WebPayment;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Monext\Payline\Helper\Constants as HelperConstants;
use Payline\PaylineSDK;

class WidgetIframeForm extends Template
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    
    /**
     * @var Registry 
     */
    protected $registry;
    
    public function __construct(
        Context $context, 
        ScopeConfigInterface $scopeConfig,
        Registry $registry,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->scopeConfig = $scopeConfig;
    }
    
    public function getWidgetJsUrl()
    {
        if($this->scopeConfig->getValue(HelperConstants::CONFIG_PATH_PAYMENT_PAYLINE_ENVIRONMENT) == PaylineSDK::ENV_PROD) {
            return 'https://payment.payline.com/scripts/widget-min.js';
        } else {
            return 'https://homologation-payment.payline.com/scripts/widget-min.js';
        }
    }
    
    public function getWidgetCssUrl()
    {
        if($this->scopeConfig->getValue(HelperConstants::CONFIG_PATH_PAYMENT_PAYLINE_ENVIRONMENT) == PaylineSDK::ENV_PROD) {
            return 'https://payment.payline.com/styles/widget-min.css';
        } else {
            return 'https://homologation-payment.payline.com/styles/widget-min.css';
        }
    }
    
    public function getToken()
    {
        $lastResponseDoWebPaymentData = $this->registry->registry(HelperConstants::REGISTRY_KEY_LAST_RESPONSE_DO_WEB_PAYMENT_DATA);
        return $lastResponseDoWebPaymentData['token'];
    }
}
