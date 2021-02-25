<?php

namespace Monext\Payline\Model\Method\WebPayment;

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

    protected $_canCapturePartial = true;

    protected $_canRefundInvoicePartial = true;

    public function initialize($paymentAction, $stateObject)
    {
        $payment = $this->getInfoInstance();
        $status = HelperConstants::ORDER_STATUS_PAYLINE_PENDING;
        if ($payment instanceof OrderPayment) {
            $order = $payment->getOrder();
            $status = $this->helperData->getMatchingConfigurableStatus($order, $status);

            if ($this->getConfigData('integration_type') == PaylineApiConstants::INTEGRATION_TYPE_REDIRECT) {
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
}
