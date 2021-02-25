define(
    [
        'jquery',
        'mage/utils/wrapper',
        'Magento_CheckoutAgreements/js/model/agreements-assigner',
    ],
    function ($, wrapper, agreementsAssigner) {
        'use strict';

        var checkoutAgreementsUtils = {
            checkoutAgreementsSelector: '.payline-payment-block .checkout-agreements-block div[data-role="checkout-agreements"]',

            waitForCheckoutAgreementsLoaded: function () {
                var self = this;

                if (!self.areCheckoutAgreementsActive() || $(self.checkoutAgreementsSelector).length > 0) {
                    return {
                        done: function (callback) {
                            callback();
                        }
                    };
                } else {
                    var result = $.Deferred();

                    var timeoutCallback = function () {
                        if ($(self.checkoutAgreementsSelector).length > 0) {
                            result.resolve();
                        } else {
                            window.setTimeout(function () {
                                timeoutCallback();}, 100);
                        }
                    };

                    window.setTimeout(function () {
                        timeoutCallback();}, 100);
                    return result;
                }
            },

            waitForCheckoutAgreementsVisible: function () {
                var self = this;

                if (!self.areCheckoutAgreementsActive() || $(self.checkoutAgreementsSelector).is(':visible')) {
                    return {
                        done: function (callback) {
                            callback();
                        }
                    };
                } else {
                    var result = $.Deferred();

                    var timeoutCallback = function () {
                        if ($(self.checkoutAgreementsSelector).is(':visible')) {
                            result.resolve();
                        } else {
                            window.setTimeout(function () {
                                timeoutCallback();}, 100);
                        }
                    };

                    window.setTimeout(function () {
                        timeoutCallback();}, 100);
                    return result;
                }
            },

            areCheckoutAgreementsActive: function () {
                var agreementsConfig = window.checkoutConfig.checkoutAgreements;
                return agreementsConfig && agreementsConfig.isEnabled;
            }
        };

        return function (config, element) {
            if (!checkoutAgreementsUtils.areCheckoutAgreementsActive()) {
                return;
            }

            $(document).on('payline.web.payment.beforeInitialize', function (event1, component) {
                component.saveCheckoutPaymentInformationFacade = wrapper.wrap(component.saveCheckoutPaymentInformationFacade, function (originalAction) {
                    if (!component.flagPreventSaveCheckoutPaymentInformationFacade) {
                        component.flagPreventSaveCheckoutPaymentInformationFacade = true;
                        checkoutAgreementsUtils.waitForCheckoutAgreementsLoaded().done(function () {
                            checkoutAgreementsUtils.waitForCheckoutAgreementsVisible().done(function () {
                                component.flagPreventSaveCheckoutPaymentInformationFacade = false;
                                originalAction();

                                var handler = function (event2) {
                                    if ($(checkoutAgreementsUtils.checkoutAgreementsSelector).find('input[type=checkbox], input[type=radio]').is(':checked')) {
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

            $(document).on('payline.web.payment.beforeSaveCheckoutPaymentInformationFacade', function (event1, payload) {
                agreementsAssigner(payload.paymentMethod);
            });
        };
    }
);
