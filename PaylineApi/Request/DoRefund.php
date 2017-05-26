<?php

namespace Monext\Payline\PaylineApi\Request;

use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Monext\Payline\Helper\Currency as HelperCurrency;
use Monext\Payline\PaylineApi\AbstractRequest;
use Monext\Payline\PaylineApi\Constants as PaylineApiConstants;

class DoRefund extends AbstractRequest
{
    /**
     * @var HelperCurrency 
     */
    protected $helperCurrency;
    
    /**
     * @var PaymentInterface
     */
    protected $payment;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var array 
     */
    protected $paymentData;
   
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        HelperCurrency $helperCurrency
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->helperCurrency = $helperCurrency;
    }

    public function setPaymentData(array $paymentData)
    {
        $this->paymentData = $paymentData;
        return $this;
    }
    
    public function setOrder(OrderInterface $order)
    {
        $this->order = $order;
        return $this;
    }
    
    public function setPayment($payment)
    {
        $this->payment = $payment;
        return $this;
    }
          
    public function getData() 
    {
        $data = array();
        
        // PAYMENT
        $data['payment'] = $this->paymentData;
        $data['payment']['action'] = PaylineApiConstants::PAYMENT_ACTION_REFUND;
        
        $paymentMethod = $this->payment->getMethod();
        $paymentAdditionalInformation = $this->payment->getAdditionalInformation();
        $integrationType = $this->scopeConfig->getValue('payment/'.$paymentMethod.'/integration_type');
        if($integrationType == PaylineApiConstants::INTEGRATION_TYPE_REDIRECT) {
            $data['payment']['contractNumber'] = $paymentAdditionalInformation['contract_number'];
        } elseif($integrationType == PaylineApiConstants::INTEGRATION_TYPE_WIDGET) {
            $usedContracts = $this->contractManagement->getUsedContracts();
            $data['payment']['contractNumber'] = $usedContracts->getFirstItem()->getNumber();
        }
        $data['payment']['mode'] = $paymentAdditionalInformation['payment_mode'];
        // currency
        $data['payment']['currency'] = $this->helperCurrency->getNumericCurrencyCode($this->order->getOrderCurrencyCode());
        
        // Transaction ID
        $data['transactionID'] = $data['payment']['transactionID'];
        unset($data['payment']['transactionID']);
        // Same for comment
        $data['comment'] = $data['payment']['comment'];
        unset($data['payment']['comment']);
        
        // PRIVATE DATA LIST
        $data['privateDataList'] = array();
        
        // SEQUENCE NUMBER
        $data['sequenceNumber'] = '';
        
        // MEDIA
        $data['media'] = '';
        
        return $data;
    }
}