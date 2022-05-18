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
        },

        updateDependencies: function (value) {
            var groupField = uiRegistry.get('index = customer_group_id');

            if (value == 1) {
                groupField.show();
            } else{
                groupField.hide();
            }
        },

        onUpdate: function (value) {
            this.updateDependencies(value);
            return this._super();
        }
    });
});