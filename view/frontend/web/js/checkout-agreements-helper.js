define([
        'jquery',
    ],
    function ($) {
        'use strict';

        return {
            getCheckoutAgreementsLoadedDeferredObject: function(checkoutAgreementsSelector) {
                var self = this;

                if(!self.areCheckoutAgreementsActive() || $(checkoutAgreementsSelector).find('input[type=checkbox], input[type=radio]').length > 0) {
                    return {
                        done: function(callback) {
                            callback();
                        }
                    };
                } else {
                    var result = $.Deferred();

                    var timeoutCallback = function() {
                        if( $(checkoutAgreementsSelector).find('input[type=checkbox], input[type=radio]').length > 0) {
                            result.resolve();
                        } else {
                            window.setTimeout(function(){timeoutCallback();}, 100);
                        }
                    };

                    window.setTimeout(function(){timeoutCallback();}, 100);
                    return result;
                }
            },

            getCheckoutAgreementsVisibleDeferredObject: function(checkoutAgreementsSelector) {
                var self = this;

                if(!self.areCheckoutAgreementsActive() || $(checkoutAgreementsSelector).find('input[type=checkbox], input[type=radio]').is(':visible')) {
                    return {
                        done: function(callback) {
                            callback();
                        }
                    };
                } else {
                    var result = $.Deferred();

                    var timeoutCallback = function() {
                        if($(checkoutAgreementsSelector).find('input[type=checkbox], input[type=radio]').is(':visible')) {
                            result.resolve();
                        } else {
                            window.setTimeout(function(){timeoutCallback();}, 100);
                        }
                    };

                    window.setTimeout(function(){timeoutCallback();}, 100);
                    return result;
                }
            },

            areCheckoutAgreementsActive: function() {
                var agreementsConfig = window.checkoutConfig.checkoutAgreements;
                return agreementsConfig && agreementsConfig.isEnabled;
            }
        };
    }
);
