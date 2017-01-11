define(
    [
        'jquery',
        'Monext_Payline/js/view/payment/method-renderer/payline-web-payment-abstract',
    ],
    function ($, Component) {
        'use strict';
        
        return Component.extend({
            /**
             * @returns {String}
             */
            getCode: function () {
                return 'payline_web_payment_cpt';
            },
            
            /**
             * @returns {String}
             */
            getConfigKey: function () {
                return 'paylineWebPaymentCpt';
            },
            
            /**
             * Get payment method data
             */
            getData: function () {
                var parent = this._super(),
                    additionalData = null;
                
                additionalData = {'payment_mode': 'CPT'};
                
                return $.extend(true, parent, {
                    'additional_data': additionalData
                });
            }
        });
    }
);
