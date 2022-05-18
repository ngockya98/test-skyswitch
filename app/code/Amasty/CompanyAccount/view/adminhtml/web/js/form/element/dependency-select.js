define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select'
], function (_, uiRegistry, select) {
    'use strict';
    return select.extend({

        initialize: function (){
            this._super();
            this.updateDependenciesBounced = _.debounce(this.updateDependencies, 300);
            this.updateDependenciesBounced(this.initialValue);
            if (this.initialValue != "0") {
                this.options().splice(0, 1);
            }
        },

        updateDependencies: function (statusValue) {
            var rejectedAt = uiRegistry.get('index = rejected_at'),
                rejectedReason = uiRegistry.get('index = reject_reason');

            if (statusValue == 3) {
                rejectedAt.show();
                rejectedReason.show();
                if (rejectedReason.value()) {
                    rejectedReason.disabled(true);
                }
            } else{
                rejectedAt.hide();
                rejectedReason.hide();
            }
        },

        onUpdate: function (value) {
            this.updateDependencies(value);
            return this._super();
        }
    });
});