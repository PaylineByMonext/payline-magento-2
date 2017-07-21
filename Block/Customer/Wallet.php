<?php

namespace Monext\Payline\Block\Customer;

use Magento\Customer\Helper\Session\CurrentCustomer as CurrentCustomerHelper;
use Magento\Framework\View\Element\Template;
use Monext\Payline\Helper\Constants as HelperConstants;
use Monext\Payline\Model\WalletManagement;

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
    
    public function __construct(
        Template\Context $context,
        CurrentCustomerHelper $currentCustomerHelper,
        WalletManagement $walletManagement,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->walletManagement = $walletManagement;
        $this->currentCustomerHelper = $currentCustomerHelper;
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
            'environment' => $this->_scopeConfig->getValue(HelperConstants::CONFIG_PATH_PAYLINE_GENERAL_ENVIRONMENT),
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

