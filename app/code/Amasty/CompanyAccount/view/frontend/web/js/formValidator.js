define([
    'jquery',
    'mage/validation'
], function ($) {
    'use strict';

    $.widget('mage.formValidator', {
        options: {},

        _create: function () {
            var self = this,
                form = this.element;

            form.mage('validation', {
                ignore: ':hidden'
            }).find('input:text').attr('autocomplete', 'off');
        }
    });

    return $.mage.formValidator;
});
