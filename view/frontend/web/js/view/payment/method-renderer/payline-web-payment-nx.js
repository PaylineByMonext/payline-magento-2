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
            logo: require.toUrl('Monext_Payline/images/monext/payline-logo.png'),

            initialize: function () {
                this._super().initChildren();
                $(document).trigger('payline.web.payment.beforeInitialize', [this]);
                this.template = 'Monext_Payline/payment/payline-web-payment-nx';
            },

            afterPlaceOrder: function () {
                redirect('payline/webpayment/redirecttopaymentgateway');
            },

            getRedirectMessage: function () {
                return this.getMethodConfigData('redirect_message')
            },

            getMethodConfigData: function (field) {
                return window.checkoutConfig['payment']['paylineWebPaymentNx'][field];
            },

            getData: function () {
                let parent = this._super(),
                additionalData = {
                    'payment_mode': 'NX',
                };

                return $.extend(true, parent, {
                    'additional_data': additionalData
                });
            },
        });
    }
);
