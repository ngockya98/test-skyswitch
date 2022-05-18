define([
    'Magento_Ui/js/grid/provider',
], function (Provider) {
    'use strict';

    return Provider.extend({
        defaults: {
            superUserId: false,
            filterIdProvider: false,
            imports: {
                superUserId: '${ $.filterIdProvider }:data.super_user_id'
            }
        },

        superUserChanged: function (superUserId) {
            this.params['super_user_id'] = superUserId;
            this.reload();
        },

        reload: function (options) {
            if (this.superUserId && typeof this.params['super_user_id'] === 'undefined') {
                this.params['super_user_id'] = this.superUserId;
            }

            return this._super(options);
        }
    });
});
