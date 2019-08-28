<?php

namespace Monext\Payline\PaylineApi\Request;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\Data\TotalsInterface;
use Monext\Payline\Helper\Currency as HelperCurrency;
use Monext\Payline\Helper\Data as HelperData;
use Monext\Payline\Model\ContractManagement;
use Monext\Payline\PaylineApi\AbstractRequest;
use Monext\Payline\PaylineApi\Constants as PaylineApiConstants;

class DoWebPayment extends AbstractRequest
{
    /**
     * @var CartInterface
     */
    protected $cart;

    /**
     * @var ProductCollection
     */
    protected $productCollection;

    /**
     * @var TotalsInterface
     */
    protected $totals;

    /**
     * @var PaymentInterface
     */
    protected $payment;

    /**
     * @var AddressInterface
     */
    protected $billingAddress;

    /**
     * @var AddressInterface
     */
    protected $shippingAddress;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var HelperCurrency
     */
    protected $helperCurrency;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var ContractManagement
     */
    protected $contractManagement;

    /**
     * @var HelperData
     */
    protected $helperData;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        HelperCurrency $helperCurrency,
        HelperData $helperData,
        UrlInterface $urlBuilder,
        ContractManagement $contractManagement
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->helperCurrency = $helperCurrency;
        $this->helperData = $helperData;
        $this->urlBuilder = $urlBuilder;
        $this->contractManagement = $contractManagement;
    }

    public function setCart(CartInterface $cart)
    {
        $this->cart = $cart;
        return $this;
    }

    public function setProductCollection(ProductCollection $productCollection)
    {
        $this->productCollection = $productCollection;
        return $this;
    }

    public function setBillingAddress(AddressInterface $billingAddress)
    {
        $this->billingAddress = $billingAddress;
        return $this;
    }

    public function setShippingAddress(AddressInterface $shippingAddress = null)
    {
        $this->shippingAddress = $shippingAddress;
        return $this;
    }

    public function setTotals(TotalsInterface $totals)
    {
        $this->totals = $totals;
        return $this;
    }

    public function setPayment(PaymentInterface $payment)
    {
        $this->payment = $payment;
        return $this;
    }

    public function getData()
    {
        if (!isset($this->data)) {
            $data = parent::getData();

            $this->preparePaymentData($data);
            $this->prepareOrderData($data);
            $this->prepareBuyerData($data);
            $this->prepareBillingAddressData($data);
            $this->prepareShippingAddressData($data);

            $paymentMethod = $this->payment->getMethod();
            $paymentAdditionalInformation = $this->payment->getAdditionalInformation();
            $integrationType = $this->scopeConfig->getValue('payment/'.$paymentMethod.'/integration_type');

            if ($integrationType == PaylineApiConstants::INTEGRATION_TYPE_REDIRECT) {
                $data['payment']['contractNumber'] = $paymentAdditionalInformation['contract_number'];
                $data['contracts'] = [$paymentAdditionalInformation['contract_number']];
                $this->prepareUrlsForIntegrationTypeRedirect($data);
            } elseif ($integrationType == PaylineApiConstants::INTEGRATION_TYPE_WIDGET) {
                $usedContracts = $this->contractManagement->getUsedContracts();
                $data['payment']['contractNumber'] = $usedContracts->getFirstItem()->getNumber();
                $data['contracts'] = $usedContracts->getColumnValues('number');
                $this->prepareUrlsForIntegrationTypeWidget($data);
            }

            $this->data = $data;
        }

        return $this->data;
    }

    protected function preparePaymentData(&$data)
    {
        $paymentMethod = $this->payment->getMethod();
        $paymentAdditionalInformation = $this->payment->getAdditionalInformation();

        $data['payment']['amount'] = $this->helperData->mapMagentoAmountToPaylineAmount($this->totals->getGrandTotal() + $this->totals->getTaxAmount());
        $data['payment']['currency'] = $this->helperCurrency->getNumericCurrencyCode($this->totals->getBaseCurrencyCode());
        $data['payment']['action'] = $this->scopeConfig->getValue('payment/'.$paymentMethod.'/payment_action');
        $data['payment']['mode'] = $paymentAdditionalInformation['payment_mode'];
    }

    protected function prepareOrderData(&$data)
    {
        $data['order']['ref'] = $this->cart->getReservedOrderId();
        $data['order']['amount'] = $this->helperData->mapMagentoAmountToPaylineAmount($this->totals->getGrandTotal() + $this->totals->getTaxAmount());
        $data['order']['currency'] = $this->helperCurrency->getNumericCurrencyCode($this->totals->getBaseCurrencyCode());
        $data['order']['date'] = $this->formatDateTime($this->cart->getCreatedAt());
        $this->prepareOrderDetailsData($data);
    }

    protected function prepareOrderDetailsData(&$data)
    {
        $data['order']['details'] = [];

        foreach ($this->cart->getItems() as $item) {
            $tmpProduct = $this->productCollection->getItemById($item->getProductId());
            $orderDetail = [
                'ref' => $item->getSku(),
                'price' => $this->helperData->mapMagentoAmountToPaylineAmount($item->getPrice()),
                'quantity' => $item->getQty(),
                'brand' => $tmpProduct->getManufacturer(),
                'category' => $tmpProduct->getPaylineCategoryMapping(),
                'taxRate' => $this->helperData->mapMagentoAmountToPaylineAmount($item->getTaxPercent()),
            ];

            $data['order']['details'][] = $orderDetail;
        }
    }

    protected function prepareUrlsForIntegrationTypeRedirect(&$data)
    {
        $data['returnURL'] = $this->urlBuilder->getUrl('payline/webpayment/returnfrompaymentgateway');
        $data['cancelURL'] = $this->urlBuilder->getUrl('payline/webpayment/returnfrompaymentgateway');
        $data['notificationURL'] = $this->urlBuilder->getUrl('payline/webpayment/notifyfrompaymentgateway');
    }

    protected function prepareUrlsForIntegrationTypeWidget(&$data)
    {
        $customer = $this->cart->getCustomer();

        if ($customer->getId()) {
            $data['returnURL'] = $this->urlBuilder->getUrl('payline/webpayment/returnfromwidget');
            $data['cancelURL'] = $this->urlBuilder->getUrl('payline/webpayment/returnfromwidget');
        } else {
            $data['returnURL'] = $this->urlBuilder->getUrl('payline/webpayment/guestreturnfromwidget');
            $data['cancelURL'] = $this->urlBuilder->getUrl('payline/webpayment/guestreturnfromwidget');
        }

        $data['notificationURL'] = $this->urlBuilder->getUrl('payline/webpayment/notifyfrompaymentgateway');
    }

    protected function prepareBuyerData(&$data)
    {
        $customer = $this->cart->getCustomer();
        $paymentMethod = $this->payment->getMethod();

        foreach (['lastName' => 'getLastname', 'firstName' => 'getFirstname', 'email' => 'getEmail'] as $dataIdx => $getter) {
            $tmpData = $customer->$getter();

            if (empty($tmpData)) {
                $tmpData = $this->billingAddress->$getter();
            }

            $data['buyer'][$dataIdx] = $this->helperData->encodeString($tmpData);

            if ($dataIdx == 'email') {
                if (!$this->helperData->isEmailValid($tmpData)) {
                    unset($data['buyer']['email']);
                }

                $data['buyer']['customerId'] = $this->helperData->encodeString($tmpData);
            }
        }

        if ($customer->getId()) {
            $data['buyer']['accountCreateDate'] = $this->formatDateTime($customer->getCreatedAt(), 'd/m/y');
        }

        if ($this->helperData->isWalletEnabled($paymentMethod)) {
            if ($customer->getId() && $customer->getCustomAttribute('wallet_id')->getValue()) {
                $data['buyer']['walletId'] = $customer->getCustomAttribute('wallet_id')->getValue();
            } else {
                $data['buyer']['walletId'] = $this->helperData->generateRandomWalletId();
            }
        }
    }

    protected function prepareBillingAddressData(&$data)
    {
        $data['billingAddress']['title'] = $this->helperData->encodeString($this->billingAddress->getPrefix());
        $data['billingAddress']['firstName'] = $this->helperData->encodeString(substr($this->billingAddress->getFirstname(), 0, 100));
        $data['billingAddress']['lastName'] = $this->helperData->encodeString(substr($this->billingAddress->getLastname(), 0, 100));
        $data['billingAddress']['cityName'] = $this->helperData->encodeString(substr($this->billingAddress->getCity(), 0, 40));
        $data['billingAddress']['zipCode'] = substr($this->billingAddress->getPostcode(), 0, 12);
        $data['billingAddress']['country'] = $this->billingAddress->getCountry();
        $data['billingAddress']['state'] = $this->helperData->encodeString($this->billingAddress->getRegion());

        $billingPhone = $this->helperData->getNormalizedPhoneNumber($this->billingAddress->getTelephone());
        if ($billingPhone) {
            $data['billingAddress']['phone'] = $billingPhone;
        }

        $streetData = $this->billingAddress->getStreet();
        for ($i = 0; $i <= 1; $i++) {
            if (isset($streetData[$i])) {
                $data['billingAddress']['street'.($i+1)] = $this->helperData->encodeString(substr($streetData[$i], 0, 100));
            }
        }

        $name = $this->helperData->buildPersonNameFromParts(
            $this->billingAddress->getFirstname(),
            $this->billingAddress->getLastname(),
            $this->billingAddress->getPrefix()
        );
        $data['billingAddress']['name'] = $this->helperData->encodeString(substr($name, 0, 100));
    }

    protected function prepareShippingAddressData(&$data)
    {
        if (!$this->cart->getIsVirtual() && isset($this->shippingAddress)) {
            $data['shippingAddress']['title'] = $this->helperData->encodeString($this->shippingAddress->getPrefix());
            $data['shippingAddress']['firstName'] = $this->helperData->encodeString(substr($this->shippingAddress->getFirstname(), 0, 100));
            $data['shippingAddress']['lastName'] = $this->helperData->encodeString(substr($this->shippingAddress->getLastname(), 0, 100));
            $data['shippingAddress']['cityName'] = $this->helperData->encodeString(substr($this->shippingAddress->getCity(), 0, 40));
            $data['shippingAddress']['zipCode'] = substr($this->shippingAddress->getPostcode(), 0, 12);
            $data['shippingAddress']['country'] = $this->shippingAddress->getCountry();
            $data['shippingAddress']['state'] = $this->helperData->encodeString($this->shippingAddress->getRegion());

            $shippingPhone = $this->helperData->getNormalizedPhoneNumber($this->shippingAddress->getTelephone());
            if ($shippingPhone) {
                $data['shippingAddress']['phone'] = $shippingPhone;
            }

            $streetData = $this->shippingAddress->getStreet();
            for ($i = 0; $i <= 1; $i++) {
                if (isset($streetData[$i])) {
                    $data['shippingAddress']['street'.($i+1)] = $this->helperData->encodeString(substr($streetData[$i], 0, 100));
                }
            }

            $name = $this->helperData->buildPersonNameFromParts(
                $this->shippingAddress->getFirstname(),
                $this->shippingAddress->getLastname(),
                $this->shippingAddress->getPrefix()
            );
            $data['shippingAddress']['name'] = $this->helperData->encodeString(substr($name, 0, 100));
        }
    }
}
