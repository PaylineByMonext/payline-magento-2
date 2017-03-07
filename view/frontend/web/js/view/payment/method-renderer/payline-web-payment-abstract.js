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
        'Monext_Payline/js/action/load-widget',
        'Monext_Payline/js/action/destroy-widget',
        'Monext_Payline/js/lib/Uri',
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
        loadWidgetAction,
        destroyWidgetAction,
        Uri
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
                    this.widgetJsApiId = 'payline-widget-js-api';
                    this.isPaymentWidgetMessageVisible = ko.observable(false);
                    this.isRetryCallPaymentWidgetButtonVisible = ko.observable(false);
                    this.isContractChecked = ko.observable(-1);
                    this.flagPreventSaveCheckoutPaymentInformationFacade = this.getPaylinetokenQueryParam() ? true : false;
                    this.areMagentoInputsVisible = ko.observable(this.getPaylinetokenQueryParam() ? false : true);

                    destroyWidgetAction(this.widgetContainerId, this.widgetJsApiId);

                    // PREVENT TO SUSBCRIBE MULTIPLE TIMES AS THIS PAYMENT JS COMPONENTS CAN BE INITIALIZED SEVERAL 
                    // TIMES IF CUSTOMER GOES TO REVIEW => SHIPPING => REVIEW ... AND SO ON
                    if(!window.hasQuoteBillingAddressSubscribedToPaylineWidget) {
                        quote.billingAddress.subscribe(function(address) {
                            if(address !== null && this.isCurrentMethodSelected()) {
                                this.saveCheckoutPaymentInformationFacade();
                            } else if(address === null) {
                                destroyWidgetAction(this.widgetContainerId, this.widgetJsApiId);
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
                    loadWidgetAction(
                        self.widgetContainerId,
                        self.getMethodConfigData('widgetDisplay'),
                        this.getPaylinetokenQueryParam(),
                        function() {
                            if(!window.isPaylineWidgetCssApiLoaded) {
                                self.initWidgetCssApi();
                                window.isPaylineWidgetCssApiLoaded = true;
                            }

                            self.initWidgetJsApi();
                        }
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
                    destroyWidgetAction(self.widgetContainerId, self.widgetJsApiId);
                    self.flagPreventSaveCheckoutPaymentInformationFacade = true;
                    self.isRetryCallPaymentWidgetButtonVisible(false);

                    $.when(
                        saveCheckoutPaymentInformationFacadeAction(self.getData(), self.messageContainer)
                    ).done(function(response) {
                        loadWidgetAction(
                            self.widgetContainerId,
                            self.getMethodConfigData('widgetDisplay'),
                            response[0],
                            function() {
                                if(!window.isPaylineWidgetCssApiLoaded) {
                                    self.initWidgetCssApi();
                                    window.isPaylineWidgetCssApiLoaded = true;
                                }

                                self.initWidgetJsApi();
                            }
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

            initWidgetJsApi: function() {
                if(this.getEnvironment() === 'PROD') {
                    $('head').append('<script id="payline-widget-api-js" type="text/javascript" src="https://payment.payline.com/scripts/widget-min.js"></script>');
                } else {
                    $('head').append('<script id="payline-widget-api-js" type="text/javascript" src="https://homologation-payment.payline.com/scripts/widget-min.js"></script>');
                }
            },

            initWidgetCssApi: function() {
                if(this.getEnvironment() === 'PROD') {
                    $('head').append('<link rel="stylesheet" type="text/css" href="https://payment.payline.com/styles/widget-min.css">');
                } else {
                    $('head').append('<link rel="stylesheet" type="text/css" href="https://homologation-payment.payline.com/styles/widget-min.css">');
                }
            },

            getPaylinetokenQueryParam: function() {
                var uri = new Uri(window.location.href);
                return uri.getQueryParamValue('paylinetoken');
            }
        });
    }
);

