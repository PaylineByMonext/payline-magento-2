<?php

namespace Monext\Payline\Model\Method\WebPayment;

use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Model\Order\Payment as OrderPayment;
use Monext\Payline\Model\Method\AbstractMethod;
use Monext\Payline\Helper\Constants as HelperConstants;
use Monext\Payline\PaylineApi\Constants as PaylineApiConstants;

class Cpt extends AbstractMethod
{
    protected $_code = HelperConstants::WEB_PAYMENT_CPT;

    protected $_isInitializeNeeded = true;

    protected $_isGateway = true;

    protected $_canCapture = true;

    protected $_canRefund = true;

    protected $_canVoid = true;

    public function initialize($paymentAction, $stateObject)
    {
        $payment = $this->getInfoInstance();
        $status = HelperConstants::ORDER_STATUS_PAYLINE_PENDING;
        if($payment instanceof OrderPayment) {
            $order = $payment->getOrder();
            $status = $this->helperData->getMatchingConfigurableStatus($order, $status);

            if( $this->getConfigData('integration_type') == PaylineApiConstants::INTEGRATION_TYPE_REDIRECT) {
                $quoteId = $order->getQuoteId();
                $result = $this->paylinePaymentManagement->wrapCallPaylineApiDoWebPaymentFacade($quoteId);

                $additionalInformation = $payment->getAdditionalInformation();
                $additionalInformation['do_web_payment_response_data'] = $result;
                $payment->setAdditionalInformation($additionalInformation);
            }
        }

        $stateObject->setData('status', $status);
        
        return $this;
    }
    
    public function capture(InfoInterface $payment, $amount)
    {
        if($this->canCapture() && !$this->getSkipCapture() && $payment instanceof OrderPayment) {
            $this->paylinePaymentManagement->callPaylineApiDoCaptureFacade($payment->getOrder(), $payment, $amount);
        }

        return $this;
    }
    
    public function void(InfoInterface $payment)
    {
        if($this->canVoid() && !$this->getSkipVoid() && $payment instanceof OrderPayment) {
            $this->paylinePaymentManagement->callPaylineApiDoVoidFacade($payment->getOrder(), $payment);
        }
    }
    
    public function refund(InfoInterface $payment, $amount)
    {
        if($this->canRefund() && !$this->getSkipRefund() && $payment instanceof OrderPayment) {
            $this->paylinePaymentManagement->callPaylineApiDoRefundFacade($payment->getOrder(), $payment, $amount);
        }
    }
}