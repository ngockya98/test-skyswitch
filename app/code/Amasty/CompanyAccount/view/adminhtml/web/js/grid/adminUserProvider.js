define([
    'Magento_Ui/js/grid/provider',
], function (Provider) {
    'use strict';

    return Provider.extend({
        defaults: {
            companyId: false,
            filterIdProvider: false,
            imports: {
                companyId: '${ $.filterIdProvider }:data.company_id'
            }
        },

        reload: function (options) {
            if (this.companyId) {
                this.params['company_id'] = this.companyId;
            }

            return this._super(options);
        }
    });
});
