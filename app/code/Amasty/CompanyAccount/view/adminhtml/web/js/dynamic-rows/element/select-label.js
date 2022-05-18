/**
 * Select Element for dynamic rows
 */
define([
    'underscore',
    'Magento_Ui/js/form/element/select'
], function (_, select) {
    'use strict';

    return select.extend({
        /**
         * Replace value id with label text
         */
        normalizeData: function (normalizedValue) {
            var value = this._super(),
                option;

            if (value !== '') {
                option = this.getOption(value);

                if (typeof option == 'undefined') {
                    return normalizedValue;
                }

                return option.label;
            }

            if (!this.caption()) {
                return findFirst(this.options);
            }
        }
    });
});
