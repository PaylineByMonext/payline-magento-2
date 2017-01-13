<?php

namespace Monext\Payline\PaylineApi\Request;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\Data\TotalsInterface;
use Monext\Payline\Helper\Currency as HelperCurrency;
use Monext\Payline\PaylineApi\Constants as PaylineApiConstants;
use Monext\Payline\PaylineApi\Request;

class DoWebPayment extends Request
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
    
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        HelperCurrency $helperCurrency,
        UrlInterface $urlBuilder
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->helperCurrency = $helperCurrency;
        $this->urlBuilder = $urlBuilder;
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
        $data['payment']['contractNumber'] = $this->scopeConfig->getValue('payment/'.$paymentMethod.'/contract');
        
        // ORDER
        $data['order']['ref'] = $this->cart->getReservedOrderId();
        $data['order']['amount'] = round($this->totals->getGrandTotal() * 100, 0);
        $data['order']['currency'] = $this->helperCurrency->getNumericCurrencyCode($this->totals->getBaseCurrencyCode());
        $data['order']['date'] = $this->formatDateTime($this->cart->getCreatedAt());
        
        if($paymentWorkflow == PaylineApiConstants::PAYMENT_WORKFLOW_REDIRECT) {
            $this->_prepareUrlsForPaymentWorkflowRedirect($data);
        } elseif($paymentWorkflow == PaylineApiConstants::PAYMENT_WORKFLOW_WIDGET) {
            $this->_prepareUrlsForPaymentWorkflowWidget($data);
        }
        
        return $data;
    }
    
    protected function _prepareUrlsForPaymentWorkflowRedirect(&$data)
    {
        $data['returnURL'] = $this->urlBuilder->getUrl('payline/webpayment/returnfrompaymentgateway');
        $data['cancelURL'] = $this->urlBuilder->getUrl('payline/webpayment/cancelfrompaymentgateway');
        $data['notificationURL'] = $this->urlBuilder->getUrl('payline/webpayment/notify');
    }
    
    protected function _prepareUrlsForPaymentWorkflowWidget(&$data)
    {
        if($this->cart->getCustomer()->getId()) {
            $data['returnURL'] = $this->urlBuilder->getUrl('payline/webpayment/returnfromwidget');
            $data['cancelURL'] = $this->urlBuilder->getUrl('payline/webpayment/cancelfromwidget');
        } else {
            $data['returnURL'] = $this->urlBuilder->getUrl('payline/webpayment/guestreturnfromwidget');
            $data['cancelURL'] = $this->urlBuilder->getUrl('payline/webpayment/guestcancelfromwidget');
        }
        
        $data['notificationURL'] = $this->urlBuilder->getUrl('payline/webpayment/notify');
    }
}