define(
    [
        'jquery',
    ],
    function ($, urlBuilder) {
        'use strict';

        return function (iframeFormId) {
            $('#'+iframeFormId).remove();
        };
    }
);
