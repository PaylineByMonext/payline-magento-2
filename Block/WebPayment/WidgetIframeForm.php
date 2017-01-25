<?php

namespace Monext\Payline\Block\WebPayment;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Monext\Payline\Helper\Constants as HelperConstants;
use Monext\Payline\Model\CartManagement as PaylineCartManagement;
use Payline\PaylineSDK;

class WidgetIframeForm extends Template
{
    /**
     * @var PaylineCartManagement
     */
    protected $paylineCartManagement;
    
    public function __construct(
        Context $context, 
        PaylineCartManagement $paylineCartManagement,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->paylineCartManagement = $paylineCartManagement;
    }
    
    public function getWidgetJsUrl()
    {
        if($this->_scopeConfig->getValue(HelperConstants::CONFIG_PATH_PAYLINE_GENERAL_ENVIRONMENT) == PaylineSDK::ENV_PROD) {
            return 'https://payment.payline.com/scripts/widget-min.js';
        } else {
            return 'https://homologation-payment.payline.com/scripts/widget-min.js';
        }
    }
    
    public function getWidgetCssUrl()
    {
        if($this->_scopeConfig->getValue(HelperConstants::CONFIG_PATH_PAYLINE_GENERAL_ENVIRONMENT) == PaylineSDK::ENV_PROD) {
            return 'https://payment.payline.com/styles/widget-min.css';
        } else {
            return 'https://homologation-payment.payline.com/styles/widget-min.css';
        }
    }
    
    public function getToken()
    {
        return $this->getRequest()->getParam('token');
    }
    
    public function getWidgetDisplay()
    {
        $quote = $this->paylineCartManagement->getCartByToken($this->getToken());
        return $quote->getPayment()->getMethodInstance()->getConfigData('widget_display');
    }
}
