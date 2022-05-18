define([
    'jquery',
    'Magento_Ui/js/form/components/group'
], function ($, group) {
    'use strict';

    return group.extend({
        show: function () {
            this.visible(true);
            $.each(this.elems(), function (index, elem) {
                elem.visible(true);
            });
        },

        hide: function () {
            this.visible(false);
            $.each(this.elems(), function (index, elem) {
                elem.visible(false);
            });
        }
    });
});
