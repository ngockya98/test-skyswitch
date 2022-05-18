define([
    'underscore',
    'Magento_Ui/js/form/element/abstract'
], function (_, Abstract) {
    'use strict';

    return Abstract.extend({
        defaults: {
            currencySymbols: {}
        },

        initObservable: function () {
            this._super();
            this.observe('addbefore');

            return this;
        },

        currencyCodeChanged: function (newCurrencyValue) {
            var currencySymbol = typeof this.currencySymbols[newCurrencyValue] !== 'undefined'
                ? this.currencySymbols[newCurrencyValue]
                : '';

            this.addbefore(currencySymbol);
        }
    });
});
