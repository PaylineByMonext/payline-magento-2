<?php

namespace Monext\Payline\Block\Customer;

use Magento\Customer\Helper\Session\CurrentCustomer as CurrentCustomerHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Template;
use Monext\Payline\Helper\Constants as HelperConstants;
use Monext\Payline\Model\WalletManagement;
use Monext\Payline\PaylineApi\Constants as PaylineApiConstants;

class Wallet extends Template
{
    /**
     * @var WalletManagement
     */
    protected $walletManagement;
    
    /**
     * @var CurrentCustomerHelper
     */
    protected $currentCustomerHelper;
    
    /**
     * @var array
     */
    protected $manageWebWalletResponse;
    
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    
    public function __construct(
        Template\Context $context,
        CurrentCustomerHelper $currentCustomerHelper,
        WalletManagement $walletManagement,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->walletManagement = $walletManagement;
        $this->currentCustomerHelper = $currentCustomerHelper;
        $this->scopeConfig = $scopeConfig;
    }

    public function hasCustomerWallet()
    {
        return $this->walletManagement->hasCustomerWallet($this->currentCustomerHelper->getCustomer());
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->pageConfig->getTitle()->set(__('My Wallet'));
    }

    protected function _beforeToHtml()
    {
        try {
            $this->manageWebWalletResponse = $this->walletManagement->wrapCallPaylineManageWebWallet(
                $this->currentCustomerHelper->getCustomer()
            );
        } catch(\Exception $e) {
            $this->manageWebWalletResponse = array(
                'error' => true,
                'message' => 'There was an issue for collecting your wallet informations.',
            );
        }

        return parent::_beforeToHtml();
    }

    public function getManageWebWalletJsConfig()
    {
        return [
            'token' => $this->manageWebWalletResponse['token'],
            'environment' => $this->scopeConfig->getValue(HelperConstants::CONFIG_PATH_PAYLINE_GENERAL_ENVIRONMENT),
        ];
    }

    public function isManageWebWalletInError()
    {
        return isset($this->manageWebWalletResponse['error']) && $this->manageWebWalletResponse['error'];
    }

    public function getManageWebWalletErrorMessage()
    {
        return $this->manageWebWalletResponse['message'];
    }
}

