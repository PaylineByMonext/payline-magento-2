define([
        'jquery',
        'mage/utils/wrapper',
    ],
    function ($, wrapper) {
        'use strict';

        var checkoutAgreementsUtils = {
            checkoutAgreementsSelector: '.payline-payment-block .checkout-agreements-block div[data-role="checkout-agreements"]',

            getCheckoutAgreementsLoadedDeferredObject: function() {
                var self = this;

                if(!self.areCheckoutAgreementsActive() || $(self.checkoutAgreementsSelector).length > 0) {
                    return {
                        done: function(callback) {
                            callback();
                        }
                    };
                } else {
                    var result = $.Deferred();

                    var timeoutCallback = function() {
                        if($(self.checkoutAgreementsSelector).length > 0) {
                            result.resolve();
                        } else {
                            window.setTimeout(function(){timeoutCallback();}, 100);
                        }
                    };

                    window.setTimeout(function(){timeoutCallback();}, 100);
                    return result;
                }
            },

            getCheckoutAgreementsVisibleDeferredObject: function() {
                var self = this;

                if(!self.areCheckoutAgreementsActive() || $(self.checkoutAgreementsSelector).is(':visible')) {
                    return {
                        done: function(callback) {
                            callback();
                        }
                    };
                } else {
                    var result = $.Deferred();

                    var timeoutCallback = function() {
                        if($(self.checkoutAgreementsSelector).is(':visible')) {
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

        return function (config, element) {
            if(!checkoutAgreementsUtils.areCheckoutAgreementsActive()) {
                return;
            }

            $(document).on('payline.web.payment.beforeInitialize', function(event1, component) {
                component.saveCheckoutPaymentInformationFacade = wrapper.wrap(component.saveCheckoutPaymentInformationFacade, function(originalAction){
                    if(!component.flagPreventSaveCheckoutPaymentInformationFacade) {
                        component.flagPreventSaveCheckoutPaymentInformationFacade = true;
                        checkoutAgreementsUtils.getCheckoutAgreementsLoadedDeferredObject().done(function() {
                            checkoutAgreementsUtils.getCheckoutAgreementsVisibleDeferredObject().done(function() {
                                component.flagPreventSaveCheckoutPaymentInformationFacade = false;

                                if($(checkoutAgreementsUtils.checkoutAgreementsSelector).find('input[type=checkbox], input[type=radio]').length == 0
                                || $(checkoutAgreementsUtils.checkoutAgreementsSelector).find('input[type=checkbox], input[type=radio]').is(':checked')) {
                                    originalAction();
                                }

                                var handler = function(event2) {
                                    if($(checkoutAgreementsUtils.checkoutAgreementsSelector).find('input[type=checkbox], input[type=radio]').is(':checked')) {
                                        originalAction();
                                    } else {
                                        component.destroyWidget();
                                    }
                                };

                                $(checkoutAgreementsUtils.checkoutAgreementsSelector).find('input[type=checkbox], input[type=radio]')
                                    .unbind('click', handler)
                                    .bind('click', handler);
                            })
                        });
                    }
                });
            });
        };
    }
);
