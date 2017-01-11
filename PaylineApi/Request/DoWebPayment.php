<?php

namespace Monext\Payline\PaylineApi\Request;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\Data\TotalsInterface;
use Monext\Payline\Helper\Currency as HelperCurrency;
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
    
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        HelperCurrency $helperCurrency
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->helperCurrency = $helperCurrency;
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
        
        // PAYMENT
        $data['payment']['amount'] = round($this->totals->getGrandTotal() * 100, 0);
        $data['payment']['currency'] = $this->helperCurrency->getNumericCurrencyCode($this->totals->getBaseCurrencyCode());
        $data['payment']['action'] = $this->scopeConfig->getValue('payment/'.$paymentMethod.'/payment_action');
        $data['payment']['mode'] = $paymentAdditionalInformation['payment_mode'];
        // TODO
        $data['payment']['contractNumber'] = '1234567';
        
        // ORDER
        $data['order']['ref'] = $this->cart->getReservedOrderId();
        $data['order']['amount'] = round($this->totals->getGrandTotal() * 100, 0);
        $data['order']['currency'] = $this->helperCurrency->getNumericCurrencyCode($this->totals->getBaseCurrencyCode());
        $data['order']['date'] = $this->formatDateTime($this->cart->getCreatedAt());
        
        $data['returnURL'] = 'http://127.0.0.1/magento2_payline/test3.html';
        $data['cancelURL'] = 'http://127.0.0.1/magento2_payline/test3.html';
        $data['notificationURL'] = 'http://127.0.0.1/magento2_payline/test3.html';
        
        return $data;
    }
}