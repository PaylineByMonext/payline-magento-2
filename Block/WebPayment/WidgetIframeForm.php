<?php

namespace Monext\Payline\Block\WebPayment;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Quote\Model\QuoteFactory;
use Monext\Payline\Helper\Constants as HelperConstants;
use Monext\Payline\Model\OrderIncrementIdTokenFactory as OrderIncrementIdTokenFactory;
use Payline\PaylineSDK;

class WidgetIframeForm extends Template
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    
    /**
     * @var OrderIncrementIdTokenFactory
     */
    protected $orderIncrementIdTokenFactory;
    
    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;
    
    public function __construct(
        Context $context, 
        ScopeConfigInterface $scopeConfig,
        OrderIncrementIdTokenFactory $orderIncrementIdTokenFactory,
        QuoteFactory $quoteFactory,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->scopeConfig = $scopeConfig;
        $this->orderIncrementIdTokenFactory = $orderIncrementIdTokenFactory;
        $this->quoteFactory = $quoteFactory;
    }
    
    public function getWidgetJsUrl()
    {
        if($this->scopeConfig->getValue(HelperConstants::CONFIG_PATH_PAYLINE_GENERAL_ENVIRONMENT) == PaylineSDK::ENV_PROD) {
            return 'https://payment.payline.com/scripts/widget-min.js';
        } else {
            return 'https://homologation-payment.payline.com/scripts/widget-min.js';
        }
    }
    
    public function getWidgetCssUrl()
    {
        if($this->scopeConfig->getValue(HelperConstants::CONFIG_PATH_PAYLINE_GENERAL_ENVIRONMENT) == PaylineSDK::ENV_PROD) {
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
        $orderIncrementId = $this->orderIncrementIdTokenFactory->create()->getOrderIncrementIdByToken($this->getToken());
        $quote = $this->quoteFactory->create()->load($orderIncrementId, 'reserved_order_id');
        return $quote->getPayment()->getMethodInstance()->getConfigData('widget_display');
    }
}
