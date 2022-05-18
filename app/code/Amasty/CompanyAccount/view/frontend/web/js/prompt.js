define([
    'jquery',
    'Magento_Ui/js/modal/confirm',
    'mage/translate'
], function ($, confirm, $t) {
    'use strict';

    $.widget('mage.amcompanyPrompt', {
        options: {
            modalClass: 'amcompany-popup-block',
            responsive: true,
            title: '',
            cancellationLink: '',
            action: {},
            buttons: [],
            showButtons: true
        },

        _create: function () {
            var self = this,
                options = this.options;

            if (options.showButtons) {
                options.buttons = [{
                    text: $t('No'),
                    class: 'amcompany-button -primary -empty',
                    click: function () {
                        this.closeModal();
                    }
                }, {
                    text: $t('Yes'),
                    class: 'amcompany-button -fill -primary',
                    click: function () {
                        window.location.href = self.element.attr('href');
                        this.closeModal();
                    }
                }];
            }

            this.element.click(function (e) {
                confirm(options);
                e.preventDefault();
            });
        }
    });

    return $.mage.amcformPrompt
});
