<?php

namespace Monext\Payline\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
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
}