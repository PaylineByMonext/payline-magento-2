<?php

namespace Monext\Payline\PaylineApi\Request;

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
    )
    {
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
    
    public function setBillingAddress(AddressInterface $billingAddress)
    {
        $this->billingAddress = $billingAddress;
        return $this;
    }
    
    public function setShippingAddress(AddressInterface $shippingAddress)
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
        $data = array();
        
        $this->preparePaymentData($data);
        $this->prepareOrderData($data);
        $this->prepareBuyerData($data);
        $this->prepareBillingAddressData($data);
        $this->prepareShippingAddressData($data);
        
        $paymentMethod = $this->payment->getMethod();
        $paymentAdditionalInformation = $this->payment->getAdditionalInformation();
        $paymentWorkflow = $this->scopeConfig->getValue('payment/'.$paymentMethod.'/payment_workflow');
        
        if($paymentWorkflow == PaylineApiConstants::PAYMENT_WORKFLOW_REDIRECT) {
            $data['payment']['contractNumber'] = $paymentAdditionalInformation['contract_number'];
            $data['contracts'] = [$paymentAdditionalInformation['contract_number']];
            $this->prepareUrlsForPaymentWorkflowRedirect($data);
        } elseif($paymentWorkflow == PaylineApiConstants::PAYMENT_WORKFLOW_WIDGET) {
            $usedContracts = $this->contractManagement->getUsedContracts();
            $data['payment']['contractNumber'] = $usedContracts->getFirstItem()->getNumber();
            $data['contracts'] = $usedContracts->getColumnValues('number');
            $this->prepareUrlsForPaymentWorkflowWidget($data);
        }
        
        return $data;
    }
    
    protected function preparePaymentData(&$data)
    {
        $paymentMethod = $this->payment->getMethod();
        $paymentAdditionalInformation = $this->payment->getAdditionalInformation();
        
        // PAYMENT
        $data['payment']['amount'] = round($this->totals->getGrandTotal() * 100, 0);
        $data['payment']['currency'] = $this->helperCurrency->getNumericCurrencyCode($this->totals->getBaseCurrencyCode());
        $data['payment']['action'] = $this->scopeConfig->getValue('payment/'.$paymentMethod.'/payment_action');
        $data['payment']['mode'] = $paymentAdditionalInformation['payment_mode'];
    }
    
    protected function prepareOrderData(&$data)
    {
        $data['order']['ref'] = $this->cart->getReservedOrderId();
        $data['order']['amount'] = round($this->totals->getGrandTotal() * 100, 0);
        $data['order']['currency'] = $this->helperCurrency->getNumericCurrencyCode($this->totals->getBaseCurrencyCode());
        $data['order']['date'] = $this->formatDateTime($this->cart->getCreatedAt());
    }
    
    protected function prepareUrlsForPaymentWorkflowRedirect(&$data)
    {
        $data['returnURL'] = $this->urlBuilder->getUrl('payline/webpayment/returnfrompaymentgateway');
        $data['cancelURL'] = $this->urlBuilder->getUrl('payline/webpayment/cancelfrompaymentgateway');
        $data['notificationURL'] = $this->urlBuilder->getUrl('payline/webpayment/notifyfrompaymentgateway');
    }
    
    protected function prepareUrlsForPaymentWorkflowWidget(&$data)
    {
        if($this->cart->getCustomer() && $this->cart->getCustomer()->getId()) {
            $data['returnURL'] = $this->urlBuilder->getUrl('payline/webpayment/returnfromwidget');
            $data['cancelURL'] = $this->urlBuilder->getUrl('payline/webpayment/cancelfromwidget');
        } else {
            $data['returnURL'] = $this->urlBuilder->getUrl('payline/webpayment/guestreturnfromwidget');
            $data['cancelURL'] = $this->urlBuilder->getUrl('payline/webpayment/guestcancelfromwidget');
        }
        
        $data['notificationURL'] = $this->urlBuilder->getUrl('payline/webpayment/notifyfrompaymentgateway');
    }
    
    protected function prepareBuyerData(&$data)
    {
        foreach(['lastName' => 'getLastname', 'firstName' => 'getFirstname', 'email' => 'getEmail'] as $dataIdx => $getter) {
            $tmpData = null;
            
            if($this->cart->getCustomer()) {
                $tmpData = $this->cart->getCustomer()->$getter();
            }
            
            if(empty($tmpData)) {
                $tmpData = $this->billingAddress->$getter();
            }
            
            $data['buyer'][$dataIdx] = $this->helperData->encodeString($tmpData);
            
            if($dataIdx == 'email') {
                if(!$this->helperData->isEmailValid($tmpData)) {
                    unset($data['buyer']['email']);
                }
                
                $data['buyer']['customerId'] = $this->helperData->encodeString($tmpData);
            }
        }
        
        if($this->cart->getCustomer() && $this->cart->getCustomer()->getId()) {
            $data['buyer']['accountCreateDate'] = $this->formatDateTime($this->cart->getCustomer()->getCreatedAt(), 'd/m/y');
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
        if($billingPhone) {
            $data['billingAddress']['phone'] = $billingPhone;
        }
        
        $streetData = $this->billingAddress->getStreet();
        for($i = 0; $i <= 1; $i++) {
            if(isset($streetData[$i])) {
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
        if(!$this->cart->getIsVirtual() && isset($this->shippingAddress)) {
            $data['shippingAddress']['title'] = $this->helperData->encodeString($this->shippingAddress->getPrefix());
            $data['shippingAddress']['firstName'] = $this->helperData->encodeString(substr($this->shippingAddress->getFirstname(), 0, 100));
            $data['shippingAddress']['lastName'] = $this->helperData->encodeString(substr($this->shippingAddress->getLastname(), 0, 100));
            $data['shippingAddress']['cityName'] = $this->helperData->encodeString(substr($this->shippingAddress->getCity(), 0, 40));
            $data['shippingAddress']['zipCode'] = substr($this->shippingAddress->getPostcode(), 0, 12);
            $data['shippingAddress']['country'] = $this->shippingAddress->getCountry();
            $data['shippingAddress']['state'] = $this->helperData->encodeString($this->shippingAddress->getRegion());
            
            $shippingPhone = $this->helperData->getNormalizedPhoneNumber($this->shippingAddress->getTelephone());
            if($shippingPhone) {
                $data['shippingAddress']['phone'] = $shippingPhone;
            }
            
            $streetData = $this->shippingAddress->getStreet();
            for($i = 0; $i <= 1; $i++) {
                if(isset($streetData[$i])) {
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