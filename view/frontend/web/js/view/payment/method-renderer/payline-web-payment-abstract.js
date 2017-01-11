define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Monext_Payline/js/action/redirect',
    ],
    function ($, Component, redirect) {
        'use strict';
        
        return Component.extend({
            redirectAfterPlaceOrder: false,
            
            initialize: function () {
                this._super();

                if(this.getPaymentWorkflow() === 'widget') {
                    this.template = 'Monext_Payline/payment/payline-web-payment-widget';
                } else {
                    this.template = 'Monext_Payline/payment/payline-web-payment-redirect';
                }
                
                return this;
            },
            
            afterPlaceOrder: function () {
                if(this.getPaymentWorkflow() === 'redirect') {
                    redirect('payline/webpayment/redirecttopaymentgateway');
                }
            },
            
            /**
             * @return {Boolean}
             */
            selectPaymentMethod: function () {
                this._super();
                
                //Payline.Api.init();
                //Payline.Api.show();
                
                return true;
            },
            
            /**
             * @returns {String}
             */
            getConfigKey: function () {
                return '';
            },
            
            getPaymentWorkflow: function() {
                return window.checkoutConfig['payment'][this.getConfigKey()]['paymentWorkflow'];
            }
        });
    }
);

