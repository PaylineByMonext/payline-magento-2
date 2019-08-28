define(
    [
        'jquery',
        'underscore',
    ],
    function ($, _) {
        'use strict';

        var WidgetApi = {};

        _.extend(WidgetApi, {
            initJs: function (environment) {
                if (environment === 'PROD') {
                    $('head').append('<script id="payline-widget-api-js" type="text/javascript" src="https://payment.payline.com/scripts/widget-min.js"></script>');
                } else {
                    $('head').append('<script id="payline-widget-api-js" type="text/javascript" src="https://homologation-payment.payline.com/scripts/widget-min.js"></script>');
                }
            },

            initCss: function (environment) {
                if (environment === 'PROD') {
                    $('head').append('<link rel="stylesheet" type="text/css" href="https://payment.payline.com/styles/widget-min.css">');
                } else {
                    $('head').append('<link rel="stylesheet" type="text/css" href="https://homologation-payment.payline.com/styles/widget-min.css">');
                }
            },

            destroyJs: function () {
                $('#payline-widget-api-js').remove();
            },

            showWidget: function (environment, dataToken, dataColumn, widgetContainerId) {
                var paylineWidgetHtml = '';

                if (dataColumn === 'lightbox') {
                    paylineWidgetHtml = '<div id="PaylineWidget" data-token="'+dataToken+'" />';
                } else {
                    paylineWidgetHtml = '<div id="PaylineWidget" data-template="'+dataColumn+'" data-token="'+dataToken+'" />';
                }

                $('#'+widgetContainerId).append(paylineWidgetHtml);

                if (!window.isPaylineWidgetCssApiLoaded) {
                    this.initCss(environment);
                    window.isPaylineWidgetCssApiLoaded = true;
                }

                this.initJs(environment);
            },

            destroyWidget: function (widgetContainerId) {
                this.destroyJs();
                $('#'+widgetContainerId).html('');
            }
        });

        return WidgetApi;
    }
);
