define(
    [
        'jquery',
        'ko',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Monext_Payline/js/action/redirect',
        'Monext_Payline/js/action/save-checkout-payment-information-facade',
        'Monext_Payline/js/action/load-widget-iframe-form',
        'Monext_Payline/js/action/destroy-widget-iframe-form',
    ],
    function (
        $, 
        ko,
        Component, 
        quote, 
        additionalValidators, 
        redirect, 
        saveCheckoutPaymentInformationFacadeAction, 
        loadWidgetIframeFormAction, 
        destroyWidgetIframeFormAction
    ) {
        'use strict';
        
        return Component.extend({
            redirectAfterPlaceOrder: false,
            flagSaveCheckoutPaymentInformationFacade: false,
            widgetIframeFormId: 'widget-iframe-form',
            widgetIframeFormContainerId: 'widget-iframe-form-container',
            isPaymentWidgetMessageVisible: ko.observable(false),
            
            initialize: function () {
                this._super();
                
                if(this.getPaymentWorkflow() === 'widget') {
                    this.template = 'Monext_Payline/payment/payline-web-payment-widget';
                    
                    quote.billingAddress.subscribe(function (address) {
                        if(address !== null && this.isCurrentMethodSelected()) {
                            this.saveCheckoutPaymentInformationFacade();
                        } else if(address === null) {
                            destroyWidgetIframeFormAction(this.widgetIframeFormId);
                            this.isPaymentWidgetMessageVisible(true);
                        }
                    }, this);
                    
                    if(this.isCurrentMethodSelected()) {
                        this.selectPaymentMethod();
                    }
                } else {
                    this.template = 'Monext_Payline/payment/payline-web-payment-redirect';
                }
            },
            
            afterPlaceOrder: function () {
                if(this.getPaymentWorkflow() === 'redirect') {
                    redirect('payline/webpayment/redirecttopaymentgateway');
                }
            },
            
            saveCheckoutPaymentInformationFacade: function() {
                var self = this;
                if(this.getPaymentWorkflow() === 'widget' 
                && !self.flagSaveCheckoutPaymentInformationFacade
                && self.validate() && additionalValidators.validate()) {
                    self.flagSaveCheckoutPaymentInformationFacade = true;
                    $.when(
                        saveCheckoutPaymentInformationFacadeAction(self.getData(), self.messageContainer)
                    ).done(function(response) {
                        loadWidgetIframeFormAction(
                            'payline/webpayment/loadwidgetiframeform/token/'+response[0], 
                            self.widgetIframeFormId, 
                            self.widgetIframeFormContainerId
                        );
                        self.isPaymentWidgetMessageVisible(false);
                        self.flagSaveCheckoutPaymentInformationFacade = false;
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
            },
            
            isCurrentMethodSelected: function() {
                return quote.paymentMethod() && quote.paymentMethod().method === this.getCode();
            },
            
            getCcLogoSrc: function() {
                return window.checkoutConfig['payment']['payline']['ccLogoSrc'];
            }
        });
    }
);

