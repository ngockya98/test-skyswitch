define([
    'jquery',
    'Magento_Ui/js/form/components/fieldset'
], function ($, fieldset) {
    'use strict';

    return fieldset.extend({
        scrollToTab: function () {
            if (window.location.hash === '#' + this.index) {
                this.open();
                $('html, body').animate({
                    scrollTop: ($('[data-index="' + this.index + '"]').offset().top)
                }, 500);
            }
        }
    });
});
