define(
    [
        'jquery',
    ],
    function ($) {
        'use strict';

        return function (widgetContainerId, widgetJsApiId) {
            $('#'+widgetJsApiId).remove();
            $('#'+widgetContainerId).html('');
        }
    }
);
