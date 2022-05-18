/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

 define([
    'jquery',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/step-navigator',
    'Magento_Checkout/js/model/sidebar'
], function ($, Component, quote, stepNavigator, sidebarModel) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'SkySwitch_Distributors/shipping-information'
        },

        /**
         * @return {Boolean}
         */
        isVisible: function () {
            return !quote.isVirtual() && stepNavigator.isProcessed('shipping');
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

        /**
         * Back step.
         */
        back: function () {
            sidebarModel.hide();
            stepNavigator.navigateTo('shipping');
        },

        /**
         * Back to shipping method.
         */
        backToShippingMethod: function () {
            sidebarModel.hide();
            stepNavigator.navigateTo('shipping', 'opc-shipping_method');
        }
    });
});
