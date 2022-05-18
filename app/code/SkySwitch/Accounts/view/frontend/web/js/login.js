define([
    'jquery',
    'Magento_Customer/js/customer-data'
], function ($, customerData) {

    'use strict';

    return function () {
        customerData.reload(['customer', 'cart']);
    };
});
