define(
    [
        'jquery',
        'ko',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/quote',
        'Monext_Payline/js/action/redirect',
        'Monext_Payline/js/action/save-payment-information-facade',
        'Monext_Payline/js/action/load-widget-iframe-form',
    ],
    function ($, ko, Component, quote, redirect, savePaymentInformationFacadeAction, loadWidgetIframeFormAction) {
        'use strict';
        
        return Component.extend({
            redirectAfterPlaceOrder: false,
            isSavePaymentInformationFacadeActionAllowed: ko.observable(quote.billingAddress() != null),
            
            initialize: function () {
                this._super();
                
                if(this.getPaymentWorkflow() === 'widget') {
                    this.template = 'Monext_Payline/payment/payline-web-payment-widget';
                    
                    quote.billingAddress.subscribe(function (address) {
                        this.isSavePaymentInformationFacadeActionAllowed(address !== null);
                    }, this);
                    
                    this.isSavePaymentInformationFacadeActionAllowed.subscribe(function (flag) {
                        if(flag) {
                            this.savePaymentInformationFacade();
                        }
                    }, this);
                } else {
                    this.template = 'Monext_Payline/payment/payline-web-payment-redirect';
                }
            },
            
            afterPlaceOrder: function () {
                if(this.getPaymentWorkflow() === 'redirect') {
                    redirect('payline/webpayment/redirecttopaymentgateway');
                }
            },
            
            savePaymentInformationFacade: function() {
                if(this.getPaymentWorkflow() === 'widget') {
                    $.when(
                        savePaymentInformationFacadeAction(this.getData(), this.messageContainer)
                    ).done(function(response) {
                        loadWidgetIframeFormAction('payline/webpayment/loadwidgetiframeform/token/'+response[0]);
                    });
                }
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

