<?php

namespace Monext\Payline\PaylineApi;

class Constants
{
    // @see : https://payline.atlassian.net/wiki/display/DT/Codes+Mode#suk=
    const PAYMENT_MODE_CPT = 'CPT';
    const PAYMENT_MODE_DIF = 'DIF';
    const PAYMENT_MODE_NX = 'NX';
    const PAYMENT_MODE_REC = 'REC';

    // @see : https://payline.atlassian.net/wiki/display/DT/Codes+Action#suk=
    const PAYMENT_ACTION_AUTHORIZATION = 100;
    const PAYMENT_ACTION_AUTHORIZATION_CAPTURE = 101;
    const PAYMENT_ACTION_CAPTURE = 201;
    const PAYMENT_ACTION_REFUND = 421;

    // 
    const PAYMENT_WORKFLOW_REDIRECT = 'redirect';
    const PAYMENT_WORKFLOW_WIDGET = 'widget';
    
    // @see https://payline.atlassian.net/wiki/pages/viewpage.action?pageId=31588361
    const PAYMENT_BACK_CODE_RETURN_OK = '00000';
}