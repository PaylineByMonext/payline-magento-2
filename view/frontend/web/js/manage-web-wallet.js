/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
        'jquery',
        'Monext_Payline/js/widget-api',
    ],
    function ($, WidgetApi) {
        'use strict';

        return function (config) {
            WidgetApi.showWidget(
                config['environment'],
                config['token'],
                config['widgetDisplay'],
                config['containerId']
            );
        };
    }
);

