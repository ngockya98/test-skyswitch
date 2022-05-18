/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */

 define([
    'jquery',
    'Magento_Checkout/js/view/summary/shipping',
    'Magento_Checkout/js/model/quote'
], function ($, Component, quote) {
    'use strict';

    var displayMode = window.checkoutConfig.reviewShippingDisplayMode;

    return Component.extend({
        defaults: {
            displayMode: displayMode,
            template: 'SkySwitch_Distributors/checkout/summary/shipping'
        },

        /**
         * @return {Boolean}
         */
        isBothPricesDisplayed: function () {
            return this.displayMode == 'both'; //eslint-disable-line eqeqeq
        },

        /**
         * @return {Boolean}
         */
        isIncludingDisplayed: function () {
            return this.displayMode == 'including'; //eslint-disable-line eqeqeq
        },

        /**
         * @return {Boolean}
         */
        isExcludingDisplayed: function () {
            return this.displayMode == 'excluding'; //eslint-disable-line eqeqeq
        },

        /**
         * @return {*|Boolean}
         */
        isCalculated: function () {
            return this.totals() && this.isFullMode() && quote.shippingMethod() != null;
        },

        /**
         * @return {*}
         */
        getIncludingValue: function () {
            var price;

            if (!this.isCalculated()) {
                return this.notCalculatedMessage;
            }
            price = this.totals()['shipping_incl_tax'];

            return this.getFormattedPrice(price);
        },

        /**
         * @return {*}
         */
        getExcludingValue: function () {
            var price;

            if (!this.isCalculated()) {
                return this.notCalculatedMessage;
            }
            price = this.totals()['shipping_amount'];

            return this.getFormattedPrice(price);
        },

        getShippingMethodData: function () {
            var shippingMethod = quote.shippingMethod(),
                data = [];
            if (!shippingMethod) {
                return [];
            }
            var carrier_pieces = shippingMethod['carrier_title'].split(', ');
            var method_pieces = shippingMethod['method_title'].split(', ');

            let i = 0;
            for (const element of carrier_pieces) {
                data.push(element + ': ' + method_pieces[i]);
                i++;
            }
            
            return data;
        },

        getShippingMethodPrices: function () {
            var shippingMethod = quote.shippingMethod(),
                data = [];
            if (!shippingMethod) {
                return [];
            }
            var price_pieces = shippingMethod['amount'].split(', ');

            let i = 0;
            for (const element of price_pieces) {
                data.push('$' + element);
                i++;
            }
            
            return data;
        }
    });
});
