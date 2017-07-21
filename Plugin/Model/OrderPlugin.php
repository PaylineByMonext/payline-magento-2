<?php

namespace Monext\Payline\Plugin\Model;

use Magento\Sales\Model\Order;
use Monext\Payline\Model\WalletManagement;

class OrderPlugin
{
    /**
     * @var WalletManagement 
     */
    protected $walletManagement;

    public function __construct(WalletManagement $walletManagement)
    {
        $this->walletManagement = $walletManagement;
    }

    public function aroundSave(Order $subject, \Closure $proceed)
    {
        $result = $proceed();
        $this->walletManagement->handleSaveWallet($result);
        return $result;
    }
}