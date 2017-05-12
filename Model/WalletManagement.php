<?php

namespace Monext\Payline\Model;

use Magento\Sales\Api\Data\OrderInterface;
use Monext\Payline\Helper\Data as HelperData;
use Monext\Payline\Model\ResourceModel\Helper as PaylineResourceHelper;

class WalletManagement
{
    /**
     * @var HelperData 
     */
    protected $helperData;

    /**
     * @var PaylineResourceHelper
     */
    protected $paylineResourceHelper;

    public function __construct(HelperData $helperData, PaylineResourceHelper $paylineResourceHelper)
    {
        $this->helperData = $helperData;
        $this->paylineResourceHelper = $paylineResourceHelper;
    }

    public function handleSaveWallet(OrderInterface $order)
    {
        $payment = $order->getPayment();
        $customerId = $order->getCustomerId();
        $walletId = $payment->getAdditionalInformation('wallet_id');

        if(!empty($customerId) 
        && !empty($walletId) 
        && $this->helperData->isWalletEnabled($payment->getMethod()) 
        && !$this->paylineResourceHelper->hasCustomerWalletId($customerId)) {
            $this->paylineResourceHelper->saveCustomerWalletId($customerId, $walletId);
        }

        return $this;
    }
}
