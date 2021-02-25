<?php

namespace Monext\Payline\PaylineApi\Request\DoWebPaymentType;

use Monext\Payline\Helper\Constants;
use Monext\Payline\PaylineApi\Constants as PaylineApiConstants;

class Cpt extends AbstractDoWebPaymentType
{
    const PAYMENT_METHOD = Constants::WEB_PAYMENT_CPT;

    /**
     * {@inheritDoc}
     */
    public function getData(&$data)
    {
        $integrationType = $this->scopeConfig->getValue('payment/' . static::PAYMENT_METHOD . '/integration_type');
        if ($integrationType == PaylineApiConstants::INTEGRATION_TYPE_REDIRECT) {
            $paymentAdditionalInformation = $this->getPayment()->getAdditionalInformation();
            $data['payment']['contractNumber'] = $paymentAdditionalInformation['contract_number'];
            $data['contracts'] = [$paymentAdditionalInformation['contract_number']];
            $this->prepareRedirectUrls($data);
        } elseif ($integrationType == PaylineApiConstants::INTEGRATION_TYPE_WIDGET) {
            $usedContracts = $this->contractManagement->getUsedContracts();
            $data['payment']['contractNumber'] = $usedContracts->getFirstItem()->getNumber();
            $data['contracts'] = $usedContracts->getColumnValues('number');
            $this->prepareWidgetUrls($data);
        }
    }

    /**
     * @param $data
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function prepareWidgetUrls(&$data)
    {
        if ($this->cart->getQuote()->getCustomerId()) {
            $data['returnURL'] = $this->urlBuilder->getUrl('payline/webpayment/returnfromwidget');
            $data['cancelURL'] = $this->urlBuilder->getUrl('payline/webpayment/returnfromwidget');
        } else {
            $data['returnURL'] = $this->urlBuilder->getUrl('payline/webpayment/guestreturnfromwidget');
            $data['cancelURL'] = $this->urlBuilder->getUrl('payline/webpayment/guestreturnfromwidget');
        }
        $data['notificationURL'] = $this->urlBuilder->getUrl('payline/webpayment/notifyfrompaymentgateway');
    }

    protected function prepareRedirectUrls(&$data)
    {
        $data['returnURL'] = $this->urlBuilder->getUrl('payline/webpayment/returnfrompaymentgateway');
        $data['cancelURL'] = $this->urlBuilder->getUrl('payline/webpayment/returnfrompaymentgateway');
        $data['notificationURL'] = $this->urlBuilder->getUrl('payline/webpayment/notifyfrompaymentgateway');
    }
}
