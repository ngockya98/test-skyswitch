define([
    'Magento_Ui/js/grid/columns/multiselect'
], function (Multiselect) {
    'use strict';

    return Multiselect.extend({
        defaults: {
            headerTmpl: 'ui/grid/columns/text',
            fieldClass: {
                'data-grid-onoff-cell': true,
                'data-grid-checkbox-cell': false
            },
            excludeMode: true,
            selectedValue: false,
            listens: {
                selectedValue: 'onSelectedValueChange',
            },
        },

        initObservable: function () {
            return this._super()
                .observe([
                    'selectedValue'
                ]);
        },

        onSelectedValueChange: function (selected) {
            if (selected.length) {
                if (typeof selected != 'object') {
                    selected = [selected];
                }
                this.selected(selected);
            }

            return this;
        },

        onSelectedChange: function (selected) {
            var selectedValue = selected;

            if (selectedValue.length) {
                if (typeof selectedValue == 'object') {
                    selectedValue = selectedValue[0];
                }
                this.selectedValue(selectedValue);
            }

            return this._super(selected);
        },
    });
});
