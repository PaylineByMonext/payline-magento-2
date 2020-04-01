<?php

namespace Monext\Payline\PaylineApi\Request;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\UrlInterface;
use Monext\Payline\Model\ContractManagement;
use Monext\Payline\PaylineApi\AbstractRequest;
use Monext\Payline\PaylineApi\Constants;

class ManageWebWallet extends AbstractRequest
{
    protected $includeCardTypeWallet = array(
        Constants::PAYMENT_CONTRACT_CARD_TYPE_CB,
        Constants::PAYMENT_CONTRACT_CARD_TYPE_CB_3DS
    );

    /**
     * @var ContractManagement
     */
    protected $contractManagement;

    /**
     * @var CustomerInterface
     */
    protected $customer;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    public function __construct(
        ContractManagement $contractManagement,
        UrlInterface $urlBuilder
    ) {
        $this->contractManagement = $contractManagement;
        $this->urlBuilder = $urlBuilder;
    }

    public function setCustomer(CustomerInterface $customer)
    {
        $this->customer = $customer;
        return $this;
    }

    public function getData()
    {
        $data = parent::getData();

        $usedContracts = $this->contractManagement->getUsedContracts()->addFieldToFilter('card_type', array('in' => $this->includeCardTypeWallet));
        $data['contractNumber'] = $usedContracts->getFirstItem()->getNumber();
        $data['contracts'] = $usedContracts->getColumnValues('number');

        $data['buyer']['walletId'] = $this->customer->getCustomAttribute('wallet_id')->getValue();
        $data['buyer']['lastName'] = $this->customer->getLastname();
        $data['buyer']['firstName'] = $this->customer->getFirstname();

        $data['updatePersonalDetails'] = 1;

        $data['returnURL'] = $this->urlBuilder->getUrl('payline/webpayment/returnfromwalletgateway');
        $data['cancelURL'] = $this->urlBuilder->getUrl('payline/webpayment/returnfromwalletgateway');
        $data['notificationURL'] = $this->urlBuilder->getUrl('payline/webpayment/returnfromwalletgateway');

        return $data;
    }
}
