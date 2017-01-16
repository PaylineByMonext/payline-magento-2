define(
    [
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/url-builder',
        'Magento_Customer/js/model/customer',
        'Monext_Payline/js/model/save-payment-information-facade',
    ],
    function (quote, urlBuilder, customer, savePaymentInformationFacadeService) {
        'use strict';

        return function (paymentData, messageContainer) {
            var serviceUrl, payload;
                
            payload = {
                cartId: quote.getQuoteId(),
                billingAddress: quote.billingAddress(),
                paymentMethod: paymentData
            };

            if (customer.isLoggedIn()) {
                serviceUrl = urlBuilder.createUrl('/payline-checkout/mine/payment-information-facade', {});
            } else {
                serviceUrl = urlBuilder.createUrl('/payline-guest-checkout/:cartId/payment-information-facade', {
                    cartId: quote.getQuoteId()
                });
                payload.email = quote.guestEmail;
            }

            return savePaymentInformationFacadeService(serviceUrl, payload, messageContainer);
        };
    }
);
