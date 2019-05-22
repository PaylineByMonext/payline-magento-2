<?php

namespace Monext\Payline\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Math\Random as MathRandom;
use Monext\Payline\Helper\Constants as HelperConstants;

class Data extends AbstractHelper
{
    /**
     * @var MathRandom
     */
    protected $mathRandom;

    /**
     * @param Context $context
     */
    public function __construct(
        Context $context,
        MathRandom $mathRandom
    )
    {
        parent::__construct($context);
        $this->mathRandom = $mathRandom;
    }

    public function encodeString($string)
    {
        return iconv('UTF-8', "ASCII//TRANSLIT", $string);
    }

    public function getNormalizedPhoneNumber($phoneNumberCandidate)
    {
        $forbidenPhoneCars = [' ', '.', '(', ')', '-', '/', '\\', '#'];
        $regexpPhone = '/^\+?[0-9]{1,14}$/';

        $phoneNumberCandidate = str_replace($forbidenPhoneCars, '', $phoneNumberCandidate);
        if (preg_match($regexpPhone, $phoneNumberCandidate)) {
            return $phoneNumberCandidate;
        } else {
            return false;
        }
    }

    public function isEmailValid($emailCandidate)
    {
        $pattern = '/\+/i';

        $charPlusExist = preg_match($pattern, $emailCandidate);
        if (strlen($emailCandidate) <= 50 && \Zend_Validate::is($emailCandidate, 'EmailAddress') && !$charPlusExist) {
            return true;
        } else {
            return false;
        }
    }

    public function buildPersonNameFromParts($firstName, $lastName, $prefix = null)
    {
        $name = '';

        if ($prefix) {
            $name .= $prefix . ' ';
        }
        $name .= $firstName;
        $name .= ' ' . $lastName;

        return $name;
    }

    public function generateRandomWalletId()
    {
        return $this->mathRandom->getRandomString(50);
    }

    public function isWalletEnabled($paymentMethod)
    {
        return $this->scopeConfig->getValue('payment/'.$paymentMethod.'/wallet_enabled');
    }

    public function mapMagentoAmountToPaylineAmount($magentoAmount)
    {
        return round($magentoAmount * 100, 0);
    }

    public function mapPaylineAmountToMagentoAmount($paylineAmount)
    {
        return $paylineAmount / 100;
    }

    public function getMatchingConfigurableStatus(\Magento\Sales\Model\Order $order, $status)
    {
        if(empty($status)) {
            return null;
        }

        $path = 'payment/' . $order->getPayment()->getMethod() . '/order_status_' . $status;
        if($configurableStatus = $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $status = $configurableStatus;
        }
        return $status;
    }

    public function isPaymentFromPayline(\Magento\Sales\Model\Order\Payment $payment)
    {
        return $payment->getMethod() == HelperConstants::WEB_PAYMENT_CPT;
    }
}