<?php

namespace Monext\Payline\PaylineApi\Request\DoWebPaymentType;

use Monext\Payline\Helper\Constants;
use Monext\Payline\PaylineApi\Constants as PaylineApiConstants;

class Cpt extends AbstractDoWebPaymentType
{
    const PAYMENT_METHOD = Constants::WEB_PAYMENT_CPT;

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

    protected function prepareWidgetUrls(&$data)
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

    protected function prepareRedirectUrls(&$data)
    {
        $data['returnURL'] = $this->urlBuilder->getUrl('payline/webpayment/returnfrompaymentgateway');
        $data['cancelURL'] = $this->urlBuilder->getUrl('payline/webpayment/returnfrompaymentgateway');
        $data['notificationURL'] = $this->urlBuilder->getUrl('payline/webpayment/notifyfrompaymentgateway');
    }
}
