<?php

namespace Monext\Payline\Helper;

class Constants
{
    const CACHE_KEY_MERCHANT_CONTRACT_IMPORT_FLAG = 'payline_merchant_contract_import_flag';
    
    const REGISTRY_KEY_LAST_RESPONSE_DO_WEB_PAYMENT_DATA = 'payline_last_response_do_web_payment_data';
    
    const WEB_PAYMENT_CPT = 'payline_web_payment_cpt';
    
    const CONFIG_PATH_PAYMENT_PAYLINE_ENVIRONMENT = 'payment/payline/environment';
    const CONFIG_PATH_PAYMENT_PAYLINE_MERCHANT_ID = 'payment/payline/merchant_id';
    const CONFIG_PATH_PAYMENT_PAYLINE_ACCESS_KEY = 'payment/payline/access_key';
    const CONFIG_PATH_PAYMENT_PAYLINE_DEBUG = 'payment/payline/debug';
    
    const ORDER_STATUS_PAYLINE_PENDING = 'payline_pending';
    const ORDER_STATUS_PAYLINE_WAITING_CAPTURE = 'payline_waiting_capture';
    const ORDER_STATUS_PAYLINE_CAPTURED = 'payline_captured';
    const ORDER_STATUS_PAYLINE_CANCELED = 'payline_canceled';
}