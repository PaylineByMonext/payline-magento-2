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
    
    //
    const PAYMENT_WIDGET_DISPLAY_COLUMN = 'column';
    const PAYMENT_WIDGET_DISPLAY_TAB = 'tab';
    const PAYMENT_WIDGET_DISPLAY_LIGHTBOX = 'lightbox';
    
    // @see https://payline.atlassian.net/wiki/pages/viewpage.action?pageId=31588361
    const PAYMENT_BACK_CODE_RETURN_OK = '00000';
    const PAYMENT_BACK_CODES_RETURN_GET_WEB_PAYMENT_DETAILS_TRANSACTION_APPROVED = ['00000', '02400', '02500', '02501', '02517', '02520', '02616', '03000', '04000'];
    const PAYMENT_BACK_CODES_RETURN_GET_WEB_PAYMENT_DETAILS_TRANSACTION_WAITING_ACCEPTANCE = ['02005', '02306', '02015', '02000'];
    const PAYMENT_BACK_CODES_RETURN_GET_WEB_PAYMENT_DETAILS_TRANSACTION_ABANDONED = ['02304', '02324', '02534'];
    const PAYMENT_BACK_CODES_RETURN_GET_WEB_PAYMENT_DETAILS_TRANSACTION_CANCELED = ['02319'];
    const PAYMENT_BACK_CODES_RETURN_GET_WEB_PAYMENT_DETAILS_TRANSACTION_FRAUD = ['04003'];
    
    //
    const PAYMENT_CONTRACT_CARD_TYPE_CB = 'CB';
    const PAYMENT_CONTRACT_CARD_TYPE_CB_3DS = 'CB_3DS';
    const PAYMENT_CONTRACT_CARD_TYPE_PAYPAL = 'PAYPAL';
}