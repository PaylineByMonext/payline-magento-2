<?php

namespace Monext\Payline\Helper;

class Constants
{
    const CACHE_KEY_MERCHANT_CONTRACT_IMPORT_FLAG = 'payline_merchant_contract_import_flag';

    const WEB_PAYMENT_CPT = 'payline_web_payment_cpt';
    const WEB_PAYMENT_NX = 'payline_web_payment_nx';
    const WEB_PAYMENT_REC = 'payline_web_payment_rec';

    const AVAILABLE_WEB_PAYMENT_PAYLINE = [self::WEB_PAYMENT_CPT, self::WEB_PAYMENT_NX];

    const CONFIG_PATH_PAYLINE_GENERAL_ENVIRONMENT = 'payline/general/environment';
    const CONFIG_PATH_PAYLINE_GENERAL_MERCHANT_ID = 'payline/general/merchant_id';
    const CONFIG_PATH_PAYLINE_GENERAL_ACCESS_KEY = 'payline/general/access_key';
    const CONFIG_PATH_PAYLINE_GENERAL_LANGUAGE = 'payline/general/language';
    const CONFIG_PATH_PAYLINE_GENERAL_DEBUG = 'payline/general/debug';
    const CONFIG_PATH_PAYLINE_GENERAL_CONTRACTS = 'payline/general/contracts';

    const CONFIG_PATH_PAYLINE_DELIVERY = 'payline/payline_common/address';
    const CONFIG_PATH_PAYLINE_PREFIX = 'payline/payline_common/prefix';
    const CONFIG_PATH_PAYLINE_DEFAULT_DELIVERYTIME = 'payline/common_default/deliverytime';
    const CONFIG_PATH_PAYLINE_DEFAULT_DELIVERYMODE = 'payline/common_default/deliverymode';
    const CONFIG_PATH_PAYLINE_DEFAULT_DELIVERY_EXPECTED_DELAY = 'payline/common_default/delivery_expected_delay';
    const CONFIG_PATH_PAYLINE_DEFAULT_PREFIX = 'payline/common_default/prefix';

    const ORDER_STATUS_PAYLINE_PENDING = 'payline_pending';
    const ORDER_STATUS_PAYLINE_WAITING_CAPTURE = 'payline_waiting_capture';
    const ORDER_STATUS_PAYLINE_CAPTURED = 'payline_captured';
    const ORDER_STATUS_PAYLINE_CYCLE_PAYMENT_CAPTURE = 'payline_cycle_payment_in_capture';
    const ORDER_STATUS_PAYLINE_CANCELED = 'payline_canceled';
    const ORDER_STATUS_PAYLINE_ABANDONED = 'payline_abandoned';
    const ORDER_STATUS_PAYLINE_REFUSED = 'payline_refused';
    const ORDER_STATUS_PAYLINE_FRAUD = 'payline_fraud';
    const ORDER_STATUS_PAYLINE_WAITING_ACCEPTANCE = 'payline_waiting_acceptance';

    //Config NX / REC
    const COST_TYPE_NO_CHARGES = 0;
    const COST_TYPE_FIXE = 1;
    const COST_TYPE_PERCENT = 2;

    const ORDER_STATUS_PAYLINE_PENDING_ONEY  = 'pending_oney';

    const PAYLINE_API_USED_BY_PREFIX = 'Magento';

    const MODULE_NAME = 'Monext_Payline';
}
