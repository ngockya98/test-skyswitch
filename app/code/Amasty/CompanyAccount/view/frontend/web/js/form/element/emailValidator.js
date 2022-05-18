define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'mage/validation'
], function ($) {
    'use strict';

    $.widget('mage.amcompanyEmailValidator', {
        options: {
            validateUrl: ''
        },

        _create: function () {
            this._bind();
            this.initialValue = $(this.element).val();
        },

        _bind: function () {
            this._on({
                focusout:  this._validateField.bind(this)
            });
        },

        _validateField: function () {
            var isValidEmail,
                currentValue = $(this.element).val();

            $(this.element).data('email_exist', true);
            $(this.element).data('email_in_company', true);
            isValidEmail = $.validator.validateSingleElement(this.element);

            if (!isValidEmail || this.initialValue === currentValue) {
                return false;
            }

            $.ajax({
                url: this.options.validateUrl,
                data: {'email': currentValue},
                type: 'post',
                dataType: 'json',
                showLoader: true
            })
                .done(function (data) {
                    if (data['email_in_company']) {
                        $(this.element).data('email_in_company', !data['email_in_company']);
                    } else if (data['email_exist']) {
                        $(this.element).data('email_exist', !data['email_exist']);
                    }

                    $.validator.validateSingleElement(this.element);
                }.bind(this))
                .fail(function () {
                    $(this.element).data('email_exist', true);
                    $.validator.validateSingleElement(this.element);
                }.bind(this));
        },
    });

    return $.mage.amcompanyEmailValidator;
});
