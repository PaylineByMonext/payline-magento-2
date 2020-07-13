/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';

        rendererList.push(
            {
                type: 'payline_web_payment_nx',
                component: 'Monext_Payline/js/view/payment/method-renderer/payline-web-payment-nx'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
