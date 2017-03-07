define(
    [
        'jquery',
    ],
    function ($) {
        'use strict';

        return function (widgetContainerId, dataColumn, dataToken, widgetInitCallback) {
            var paylineWidgetHtml = '';
            
            if(dataColumn === 'lightbox') {
                paylineWidgetHtml = '<div id="PaylineWidget" data-token="'+dataToken+'" />';
            } else {
                paylineWidgetHtml = '<div id="PaylineWidget" data-template="'+dataColumn+'" data-token="'+dataToken+'" />';
            }
            
            $('#'+widgetContainerId).append(paylineWidgetHtml);

            widgetInitCallback();
        };
    }
);

