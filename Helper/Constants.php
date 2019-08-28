<?php

namespace Monext\Payline\Helper;

class Constants
{
    const CACHE_KEY_MERCHANT_CONTRACT_IMPORT_FLAG = 'payline_merchant_contract_import_flag';

    const WEB_PAYMENT_CPT = 'payline_web_payment_cpt';

    const CONFIG_PATH_PAYLINE_GENERAL_ENVIRONMENT = 'payline/general/environment';
    const CONFIG_PATH_PAYLINE_GENERAL_MERCHANT_ID = 'payline/general/merchant_id';
    const CONFIG_PATH_PAYLINE_GENERAL_ACCESS_KEY = 'payline/general/access_key';
    const CONFIG_PATH_PAYLINE_GENERAL_DEBUG = 'payline/general/debug';
    const CONFIG_PATH_PAYLINE_GENERAL_CONTRACTS = 'payline/general/contracts';

    const ORDER_STATUS_PAYLINE_PENDING = 'payline_pending';
    const ORDER_STATUS_PAYLINE_WAITING_CAPTURE = 'payline_waiting_capture';
    const ORDER_STATUS_PAYLINE_CAPTURED = 'payline_captured';
    const ORDER_STATUS_PAYLINE_CANCELED = 'payline_canceled';
    const ORDER_STATUS_PAYLINE_ABANDONED = 'payline_abandoned';
    const ORDER_STATUS_PAYLINE_REFUSED = 'payline_refused';
    const ORDER_STATUS_PAYLINE_FRAUD = 'payline_fraud';
    const ORDER_STATUS_PAYLINE_WAITING_ACCEPTANCE = 'payline_waiting_acceptance';

    const PAYLINE_API_USED_BY_PREFIX = 'MGT2';

    const MODULE_NAME = 'Monext_Payline';
}
