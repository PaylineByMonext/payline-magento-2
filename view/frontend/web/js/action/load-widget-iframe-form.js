define(
    [
        'jquery',
        'mage/url',
    ],
    function ($, urlBuilder) {
        'use strict';

        return function (urlPath) {
            $('<iframe>', {
                src: urlBuilder.build(urlPath),
                id:  'myFrame',
                frameborder: 0
            })
            .css({'width': '100%', 'height': 'auto'})
            .appendTo('#test-iframe');
        };
    }
);
