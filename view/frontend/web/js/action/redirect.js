/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'mage/url'
    ],
    function (urlBuilder) {
        'use strict';

        return function (urlPath) {
            window.location.replace(urlBuilder.build(urlPath));
        };
    }
);
