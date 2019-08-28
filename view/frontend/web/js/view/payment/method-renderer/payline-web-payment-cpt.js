define(
    [
        'jquery',
        'Monext_Payline/js/view/payment/method-renderer/payline-web-payment-abstract',
    ],
    function ($, Component) {
        'use strict';
        
        return Component.extend({
            getData: function () {
                var parent = this._super(),
                    additionalData = null;

                additionalData = {
                    'payment_mode': 'CPT',
                    'contract_id': this.isContractChecked()
                };
                
                return $.extend(true, parent, {
                    'additional_data': additionalData
                });
            },
            
            getMethodConfigData: function (field) {
                return window.checkoutConfig['payment']['paylineWebPaymentCpt'][field];
            }
        });
    }
);
