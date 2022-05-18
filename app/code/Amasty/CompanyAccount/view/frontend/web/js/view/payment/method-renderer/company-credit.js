define([
    'Magento_Checkout/js/view/payment/default',
    'jquery',
    'Magento_Checkout/js/model/quote'
], function (Component, $, quote) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Amasty_CompanyAccount/payment/company-credit-form'
        },

        /** @inheritdoc */
        setConfig: function () {
            this.config = window.checkoutConfig;
        },

        getCredit: function () {
            return this.config.payment.amasty_company_credit;
        },

        /** @inheritdoc */
        isCanPlace: function () {
            return !this.isBalanceExceed()
                || (this.areCurrenciesDifferent() && !this.isBaseCreditCurrencyRateEnabled());
        },

        areCurrenciesDifferent: function () {
            return this.config.quoteData['quote_currency_code'] !== this.getCredit().currency;
        },

        isBaseCreditCurrencyRateEnabled: function () {
            return this.getCredit().isBaseCreditCurrencyRateEnabled;
        },

        isBalanceExceed: function () {
            return this.getCredit().balance_quote_currency < quote.getTotals()()['grand_total'];
        },

        getBalance: function () {
            return this.getCredit().balance;
        },

        getPaid: function () {
            return this.getCredit().be_paid;
        },

        getOverdraftSum: function () {
            return this.getCredit().balance.substring(1);
        },

        isOverdraftAllowed: function () {
            return typeof this.getCredit().overdraft_limit !== 'undefined';
        },

        getOverdraftLimit: function () {
            return this.getCredit().overdraft_limit;
        },

        isOverdraftExist: function () {
            return typeof this.getCredit().overdraft !== 'undefined';
        },

        getOverdraftData: function (key) {
            return this.getCredit()['overdraft'][key];
        }
    });
});
