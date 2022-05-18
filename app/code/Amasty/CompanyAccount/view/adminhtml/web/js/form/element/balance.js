define([
    'underscore',
    'uiElement',
    'uiRegistry'
], function (_, Element, uiRegistry) {
    return Element.extend({
        defaults: {
            balance: '',
            credit: '',
            overdraft_limit: '',
            allow_overdraft: ''
        },

        initialize: function (){
            this._super();
            this.balance(this.source.get('data.store_credit.balance_for_card'));
            this.credit(this.source.get('data.store_credit.credit_for_card'));
            this.allow_overdraft(+this.source.get('data.store_credit.allow_overdraft'));
            this.overdraft_limit(this.source.get('data.store_credit.overdraft_limit_for_card'));
        },

        initObservable: function () {
            this._super();
            this.observe('balance credit overdraft_limit allow_overdraft');

            return this;
        },

        openModal: function () {
            uiRegistry.get('index = change_balance_modal').openModal();
        },

        isOperationsAvailable: function () {
            return !!this.source.get('data.company_id');
        }
    });
});
