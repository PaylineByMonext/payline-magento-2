define(
    [
        'jquery',
        'ko',
        'Magento_Checkout/js/view/payment/default',
        'mage/translate',
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
        $t,
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

            initialize: function () {
                this._super();
                
                if(this.getMethodConfigData('paymentWorkflow') === 'widget') {
                    this.template = 'Monext_Payline/payment/payline-web-payment-widget';
                    this.widgetIframeFormId = 'widget-iframe-form';
                    this.widgetIframeFormContainerId = 'widget-iframe-form-container';
                    this.isPaymentWidgetMessageVisible = ko.observable(false);
                    this.isRetryCallPaymentWidgetButtonVisible = ko.observable(false);
                    this.isContractChecked = ko.observable(-1);
                    
                    destroyWidgetIframeFormAction(this.widgetIframeFormId);
                    
                    quote.billingAddress.subscribe(function(address) {
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
                    this.isContractChecked = ko.observable(null);
                }
            },
            
            afterPlaceOrder: function () {
                if(this.getMethodConfigData('paymentWorkflow') === 'redirect') {
                    redirect('payline/webpayment/redirecttopaymentgateway');
                }
            },
            
            saveCheckoutPaymentInformationFacade: function() {
                var self = this;

                if(this.getMethodConfigData('paymentWorkflow') === 'widget' 
                && !self.flagSaveCheckoutPaymentInformationFacade
                && self.validate() && additionalValidators.validate()) {
                    self.flagSaveCheckoutPaymentInformationFacade = true;
                    self.isRetryCallPaymentWidgetButtonVisible(false);
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
                    }).fail(function(response) {
                        self.isRetryCallPaymentWidgetButtonVisible(true);
                        self.flagSaveCheckoutPaymentInformationFacade = false;
                    });
                }
            },
            
            getMethodConfigData: function(field) {
                throw new Error();
            },
            
            isCurrentMethodSelected: function() {
                return quote.paymentMethod() && quote.paymentMethod().method === this.getCode();
            },
            
            getContracts: function() {
                return window.checkoutConfig['payline']['general']['contracts'];
            },
            
            validate: function() {
                var parentValidate = this._super();
                var currentValidate = true;
                
                if(!this.isContractChecked()) {
                    this.messageContainer.addErrorMessage({'message' : $t('You must choose a card type.')});
                    currentValidate = false;
                }

                return parentValidate && currentValidate;
            }
        });
    }
);

