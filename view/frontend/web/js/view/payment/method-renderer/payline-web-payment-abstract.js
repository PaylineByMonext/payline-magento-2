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
        'Monext_Payline/js/lib/Uri',
        'Monext_Payline/js/widget-api',
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
        Uri,
        WidgetApi
    ) {
        'use strict';

        return Component.extend({
            redirectAfterPlaceOrder: false,
            flagPreventSaveCheckoutPaymentInformationFacade: false,

            initialize: function () {
                this._super().initChildren();

                if(this.getMethodConfigData('integrationType') === 'widget') {
                    this.template = 'Monext_Payline/payment/payline-web-payment-widget';
                    this.widgetContainerId = 'payline-widget-container';
                    this.isPaymentWidgetMessageVisible = ko.observable(false);
                    this.isRetryCallPaymentWidgetButtonVisible = ko.observable(false);
                    this.isContractChecked = ko.observable(-1);
                    this.flagPreventSaveCheckoutPaymentInformationFacade = this.getPaylinetokenQueryParam() ? true : false;
                    this.areMagentoInputsVisible = ko.observable(this.getPaylinetokenQueryParam() ? false : true);

                    WidgetApi.destroyWidget(this.widgetContainerId);

                    // PREVENT TO SUSBCRIBE MULTIPLE TIMES AS THIS PAYMENT JS COMPONENTS CAN BE INITIALIZED SEVERAL 
                    // TIMES IF CUSTOMER GOES TO REVIEW => SHIPPING => REVIEW ... AND SO ON
                    if(!window.hasQuoteBillingAddressSubscribedToPaylineWidget) {
                        quote.billingAddress.subscribe(function(address) {
                            if(address !== null && this.isCurrentMethodSelected()) {
                                this.saveCheckoutPaymentInformationFacade();
                            } else if(address === null) {
                                WidgetApi.destroyWidget(this.widgetContainerId);
                                this.isPaymentWidgetMessageVisible(true);
                            }
                        }, this);

                        window.hasQuoteBillingAddressSubscribedToPaylineWidget = true;
                    }

                    if(this.isCurrentMethodSelected()) {
                        this.selectPaymentMethod();
                    }
                } else {
                    this.template = 'Monext_Payline/payment/payline-web-payment-redirect';
                    this.isContractChecked = ko.observable(null);
                }
            },

            tryReloadWidget: function() {
                var self = this;

                if(this.getPaylinetokenQueryParam()) {
                    WidgetApi.showWidget(
                        self.getEnvironment(),
                        self.getPaylinetokenQueryParam(),
                        self.getMethodConfigData('widgetDisplay'),
                        self.widgetContainerId
                    );
                }
            },

            afterPlaceOrder: function () {
                if(this.getMethodConfigData('integrationType') === 'redirect') {
                    redirect('payline/webpayment/redirecttopaymentgateway');
                }
            },

            saveCheckoutPaymentInformationFacade: function() {
                var self = this;

                if(self.getMethodConfigData('integrationType') === 'widget' 
                && !self.flagPreventSaveCheckoutPaymentInformationFacade
                && self.validate() && additionalValidators.validate()) {
                    WidgetApi.destroyWidget(self.widgetContainerId);
                    self.flagPreventSaveCheckoutPaymentInformationFacade = true;
                    self.isRetryCallPaymentWidgetButtonVisible(false);

                    $.when(
                        saveCheckoutPaymentInformationFacadeAction(self.getData(), self.messageContainer)
                    ).done(function(response) {
                        WidgetApi.showWidget(
                            self.getEnvironment(),
                            response[0],
                            self.getMethodConfigData('widgetDisplay'),
                            self.widgetContainerId
                        );
                        self.isPaymentWidgetMessageVisible(false);
                        self.flagPreventSaveCheckoutPaymentInformationFacade = false;
                    }).fail(function(response) {
                        self.isRetryCallPaymentWidgetButtonVisible(true);
                        self.flagPreventSaveCheckoutPaymentInformationFacade = false;
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

            getEnvironment: function() {
                return window.checkoutConfig['payline']['general']['environment'];
            },

            validate: function() {
                var parentValidate = this._super();
                var currentValidate = true;

                if(!this.isContractChecked()) {
                    this.messageContainer.addErrorMessage({'message' : $t('You must choose a card type.')});
                    currentValidate = false;
                }

                return parentValidate && currentValidate;
            },

            getPaylinetokenQueryParam: function() {
                var uri = new Uri(window.location.href);
                return uri.getQueryParamValue('paylinetoken');
            }
        });
    }
);

