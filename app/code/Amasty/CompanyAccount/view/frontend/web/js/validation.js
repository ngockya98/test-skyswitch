define(['jquery'], function ($) {
    'use strict';

    return function (target) {
        var emailExist = function (value, element) {
            return $(element).data('email_exist');
        };

        var emailInCompany = function (value, element) {
            return $(element).data('email_in_company');
        };

        $.validator.addMethod(
            'validate-email-exist',
            emailExist,
            $.mage.__('A user with this email already has a Customer Account. You can add only new users. '
                + 'Please contact admin if you need to add this user to your company account.')
        );

        $.validator.addMethod(
            'validate-customer-in-group',
            emailInCompany,
            $.mage.__('There is already an account with this email address.')
        );

        return target;
    };
});
