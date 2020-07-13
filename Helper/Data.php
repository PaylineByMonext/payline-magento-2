<?php

namespace Monext\Payline\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Math\Random as MathRandom;
use Magento\Framework\Serialize\Serializer\Json as Serialize;
use Monext\Payline\Helper\Constants as HelperConstants;

class Data extends AbstractHelper
{
    private $delivery = null;

    private $prefix = null;
    /**
     * @var MathRandom
     */
    protected $mathRandom;

    /**
     * @var Serialize
     */
    protected $serialize;

    /**
     * @param Context $context
     * @param MathRandom $mathRandom
     * @param Serialize $serialize
     */
    public function __construct(
        Context $context,
        MathRandom $mathRandom,
        Serialize $serialize
    ) {
        parent::__construct($context);
        $this->mathRandom = $mathRandom;
        $this->serialize = $serialize;
    }

    public function encodeString($string)
    {
        return iconv('UTF-8', "ASCII//TRANSLIT", $string);
    }

    public function getNormalizedPhoneNumber($phoneNumberCandidate)
    {
        // "field": "purchase.delivery.recipient.phone_number"
        // format attendu: (+33|508|590|594|596|262|681|687|689)|0033|+33|33|+33(0)|0XXXXXXXXX
        $forbidenPhoneCars = [' ', '.', '(', ')', '-', '/', '\\', '#'];
        //$regexpPhone = '/^\+?[0-9]{1,14}$/';
        $regexpPhone = '/^\+?[0-9]{1,14}$/';

        $normalizedPhone = str_replace($forbidenPhoneCars, '', $phoneNumberCandidate);
        if (!preg_match($regexpPhone, $phoneNumberCandidate)) {
            $normalizedPhone = false;
        }

        return $normalizedPhone;
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
        if (empty($status)) {
            return null;
        }

        $path = 'payment/' . $order->getPayment()->getMethod() . '/order_status_' . $status;
        if ($configurableStatus = $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $status = $configurableStatus;
        }
        return $status;
    }

    public function isPaymentFromPayline(\Magento\Sales\Model\Order\Payment $payment)
    {
        return in_array($payment->getMethod(),HelperConstants::AVAILABLE_WEB_PAYMENT_PAYLINE);
    }

    public function getDeliverySetting() {
        if(is_null($this->delivery)) {
            $this->delivery = [];
            $addressConfigSerialized = $this->scopeConfig->getValue(HelperConstants::CONFIG_PATH_PAYLINE_DELIVERY);
            if ($addressConfigSerialized) {
                try {
                    $this->delivery = $this->serialize->unserialize($addressConfigSerialized);
                } catch (\Exception $e) {
                    $this->_logger->error($e->getMessage());
                }
            }
        }
        return $this->delivery;
    }

    public function getPrefixSetting() {
        if(is_null($this->prefix)) {
            $this->prefix = [];
            $prefixConfigSerialized = $this->scopeConfig->getValue(HelperConstants::CONFIG_PATH_PAYLINE_PREFIX);
            if ($prefixConfigSerialized) {
                try {
                    $this->prefix = $this->serialize->unserialize($prefixConfigSerialized);
                } catch (\Exception $e) {
                    $this->_logger->error($e->getMessage());
                }
            }
        }
        return $this->prefix;
    }

    public function getDefaultDeliveryTime() {
        return $this->scopeConfig->getValue(HelperConstants::CONFIG_PATH_PAYLINE_DEFAULT_DELIVERYTIME);
    }

    public function getDefaultDeliveryMode() {
        return $this->scopeConfig->getValue(HelperConstants::CONFIG_PATH_PAYLINE_DEFAULT_DELIVERYMODE);
    }

    public function getDefaultDeliveryExpectedDelay() {
        return $this->scopeConfig->getValue(HelperConstants::CONFIG_PATH_PAYLINE_DEFAULT_DELIVERY_EXPECTED_DELAY);
    }

    public function getDefaultPrefix() {
        return $this->scopeConfig->getValue(HelperConstants::CONFIG_PATH_PAYLINE_DEFAULT_PREFIX);
    }
}
