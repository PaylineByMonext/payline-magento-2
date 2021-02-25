<?php

namespace Monext\Payline\Model\Method\WebPayment;

use Magento\Payment\Model\InfoInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Model\Order\Payment as OrderPayment;
use Monext\Payline\Model\Method\AbstractMethod;
use Monext\Payline\Helper\Constants as HelperConstants;

class Nx extends AbstractMethod
{
    protected $_code = HelperConstants::WEB_PAYMENT_NX;

    protected $_isInitializeNeeded = true;

    protected $_isGateway = true;

    protected $_canCapture = true;

    protected $_canRefund = false;

    protected $_canVoid = true;

    protected $_canCapturePartial = true;

    protected $_canRefundInvoicePartial = true;

    public function isAvailable(CartInterface $quote = null)
    {
        $parentResult = parent::isAvailable($quote);
        $displayMinimumAmount = $this->helperData->getNxMinimumAmountCart();
        return $parentResult && ($quote->getGrandTotal() >= $displayMinimumAmount);
    }

    public function initialize($paymentAction, $stateObject)
    {
        $payment = $this->getInfoInstance();
        $status = HelperConstants::ORDER_STATUS_PAYLINE_PENDING;
        if ($payment instanceof OrderPayment) {
            $order = $payment->getOrder();
            $status = $this->helperData->getMatchingConfigurableStatus($order, $status);

            $quoteId = $order->getQuoteId();
            $result = $this->paylinePaymentManagement->wrapCallPaylineApiDoWebPaymentFacade($quoteId);

            $additionalInformation = $payment->getAdditionalInformation();
            $additionalInformation['do_web_payment_response_data'] = $result;
            $payment->setAdditionalInformation($additionalInformation);
        }

        $stateObject->setData('status', $status);

        return $this;
    }

    /**
     * Return Order place redirect url
     *
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
        return $this->urlBuilder->getUrl('payline/index/nx');
    }
}
