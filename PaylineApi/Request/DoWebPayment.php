<?php

namespace Monext\Payline\PaylineApi\Request;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\Data\TotalsInterface;
use Monext\Payline\Helper\Currency as HelperCurrency;
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
    
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        HelperCurrency $helperCurrency,
        UrlInterface $urlBuilder,
        ContractManagement $contractManagement
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->helperCurrency = $helperCurrency;
        $this->urlBuilder = $urlBuilder;
        $this->contractManagement = $contractManagement;
    }
    
    public function setCart(CartInterface $cart)
    {
        $this->cart = $cart;
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
        
        $paymentMethod = $this->payment->getMethod();
        $paymentAdditionalInformation = $this->payment->getAdditionalInformation();
        $paymentWorkflow = $this->scopeConfig->getValue('payment/'.$paymentMethod.'/payment_workflow');
        
        // PAYMENT
        $data['payment']['amount'] = round($this->totals->getGrandTotal() * 100, 0);
        $data['payment']['currency'] = $this->helperCurrency->getNumericCurrencyCode($this->totals->getBaseCurrencyCode());
        $data['payment']['action'] = $this->scopeConfig->getValue('payment/'.$paymentMethod.'/payment_action');
        $data['payment']['mode'] = $paymentAdditionalInformation['payment_mode'];
        
        // ORDER
        $data['order']['ref'] = $this->cart->getReservedOrderId();
        $data['order']['amount'] = round($this->totals->getGrandTotal() * 100, 0);
        $data['order']['currency'] = $this->helperCurrency->getNumericCurrencyCode($this->totals->getBaseCurrencyCode());
        $data['order']['date'] = $this->formatDateTime($this->cart->getCreatedAt());
        
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
    
    protected function prepareUrlsForPaymentWorkflowRedirect(&$data)
    {
        $data['returnURL'] = $this->urlBuilder->getUrl('payline/webpayment/returnfrompaymentgateway');
        $data['cancelURL'] = $this->urlBuilder->getUrl('payline/webpayment/cancelfrompaymentgateway');
        $data['notificationURL'] = $this->urlBuilder->getUrl('payline/webpayment/notifyfrompaymentgateway');
    }
    
    protected function prepareUrlsForPaymentWorkflowWidget(&$data)
    {
        if($this->cart->getCustomer()->getId()) {
            $data['returnURL'] = $this->urlBuilder->getUrl('payline/webpayment/returnfromwidget');
            $data['cancelURL'] = $this->urlBuilder->getUrl('payline/webpayment/cancelfromwidget');
        } else {
            $data['returnURL'] = $this->urlBuilder->getUrl('payline/webpayment/guestreturnfromwidget');
            $data['cancelURL'] = $this->urlBuilder->getUrl('payline/webpayment/guestcancelfromwidget');
        }
        
        $data['notificationURL'] = $this->urlBuilder->getUrl('payline/webpayment/notifyfrompaymentgateway');
    }
}