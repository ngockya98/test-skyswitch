define([
    'underscore',
    'jquery',
    'uiElement',
    'uiRegistry'
], function (_, $, Element, uiRegistry) {
    return Element.extend({
        defaults: {
            bePaid: '',
            overdraftSum: '',
            notificationUrl: ''
        },

        initialize: function (){
            this._super();
            this.bePaid(this.source.get('data.store_credit.be_paid'));
            this.overdraftSum(this.source.get('data.store_credit.balance_for_card').substring(1));
        },

        initObservable: function () {
            this._super();
            this.observe('bePaid overdraftSum');

            return this;
        },

        isOverdraftExist: function () {
            return typeof this.source.get('data.store_credit.overdraft') !== 'undefined';
        },

        getOverdraftData: function (key) {
            return this.source.get('data.store_credit.overdraft.' + key);
        },

        getPenalty: function () {
            return this.source.get('data.store_credit.overdraft_penalty');
        },

        sendNotification: function () {
            location.href = this.notificationUrl + '?' + $.param({
                'company_id': this.source.get('data.company_id'),
                'exceed': +this.getOverdraftData('exceed')
            });
        }
    });
});
