<?php

namespace Monext\Payline\Model;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Payment as OrderPayment;
use Monext\Payline\Helper\Data as HelperData;
use Monext\Payline\Model\ResourceModel\Helper as PaylineResourceHelper;
use Monext\Payline\PaylineApi\Client as PaylineApiClient;
use Monext\Payline\PaylineApi\Request\ManageWebWalletFactory as RequestManageWebWalletFactory;
use Monext\Payline\PaylineApi\Response\GetWebPaymentDetails as ResponseGetWebPaymentDetails;

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

    /**
     * @var RequestManageWebWalletFactory
     */
    protected $requestManageWebWalletFactory;

    /**
     * @var PaylineApiClient
     */
    protected $paylineApiClient;

    public function __construct(
        HelperData $helperData,
        PaylineResourceHelper $paylineResourceHelper,
        RequestManageWebWalletFactory $requestManageWebWalletFactory,
        PaylineApiClient $paylineApiClient
    ) {
        $this->helperData = $helperData;
        $this->paylineResourceHelper = $paylineResourceHelper;
        $this->requestManageWebWalletFactory = $requestManageWebWalletFactory;
        $this->paylineApiClient = $paylineApiClient;
    }

    public function handleSaveWallet(OrderInterface $order)
    {
        $payment = $order->getPayment();
        $customerId = $order->getCustomerId();
        $walletId = $payment->getAdditionalInformation('wallet_id');

        if (!empty($customerId)
        && !empty($walletId)
        && $this->helperData->isWalletEnabled($payment->getMethod())
        && !$this->paylineResourceHelper->hasCustomerWalletId($customerId)) {
            $this->paylineResourceHelper->saveCustomerWalletId($customerId, $walletId);
        }

        return $this;
    }

    public function hasCustomerWallet(CustomerInterface $customer)
    {
        return $this->paylineResourceHelper->hasCustomerWalletId($customer->getId());
    }

    public function handleWalletReturnFromPaymentGateway(
        ResponseGetWebPaymentDetails $response,
        OrderPayment $payment
    ) {
        $paymentMethod = $payment->getMethod();
        $walletData = $response->getWalletData();

        if ($this->helperData->isWalletEnabled($paymentMethod) && $walletData && isset($walletData['walletId'])) {
            $payment->setAdditionalInformation('wallet_id', $walletData['walletId']);
        }

        return $this;
    }

    public function wrapCallPaylineManageWebWallet(CustomerInterface $customer)
    {
        $response = $this->callPaylineManageWebWallet($customer);

        if (!$response->isSuccess()) {
            throw new \Exception($response->getLongErrorMessage());
        }

        return [
            'token' => $response->getToken(),
            'redirect_url' => $response->getRedirectUrl(),
        ];
    }

    protected function callPaylineManageWebWallet(CustomerInterface $customer)
    {
        $request = $this->requestManageWebWalletFactory->create();
        $request
            ->setCustomer($customer);

        return $this->paylineApiClient->callManageWebWallet($request);
    }
}
