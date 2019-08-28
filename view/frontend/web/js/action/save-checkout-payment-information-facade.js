define(
    [
        'jquery',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/url-builder',
        'Magento_Customer/js/model/customer',
        'Monext_Payline/js/model/save-checkout-payment-information-facade',
    ],
    function ($, quote, urlBuilder, customer, saveCheckoutPaymentInformationFacadeService) {
        'use strict';

        return function (paymentData, messageContainer) {
            var serviceUrl, payload;
                
            payload = {
                cartId: quote.getQuoteId(),
                billingAddress: quote.billingAddress(),
                paymentMethod: paymentData
            };

            $(document).trigger('payline.web.payment.beforeSaveCheckoutPaymentInformationFacade', [payload]);

            if (customer.isLoggedIn()) {
                serviceUrl = urlBuilder.createUrl('/payline-checkout/mine/payment-information-facade', {});
            } else {
                serviceUrl = urlBuilder.createUrl('/payline-guest-checkout/:cartId/payment-information-facade', {
                    cartId: quote.getQuoteId()
                });
                payload.email = quote.guestEmail;
            }

            return saveCheckoutPaymentInformationFacadeService(serviceUrl, payload, messageContainer);
        };
    }
);
