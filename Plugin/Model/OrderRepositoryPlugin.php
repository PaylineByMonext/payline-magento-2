<?php

namespace Monext\Payline\Plugin\Model;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Monext\Payline\Model\WalletManagement;

class OrderRepositoryPlugin
{
    /**
     * @var WalletManagement
     */
    protected $walletManagement;

    public function __construct(WalletManagement $walletManagement)
    {
        $this->walletManagement = $walletManagement;
    }

    public function aroundSave(OrderRepositoryInterface $subject, \Closure $proceed, OrderInterface $entity)
    {
        $result = $proceed($entity);
        $this->walletManagement->handleSaveWallet($result);
        return $result;
    }
}
