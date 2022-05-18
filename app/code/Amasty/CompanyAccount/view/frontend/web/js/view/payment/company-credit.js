define([
    'uiComponent',
    'Magento_Checkout/js/model/payment/renderer-list'
], function (Component, rendererList) {
    'use strict';

    rendererList.push({
        type: 'amasty_company_credit',
        component: 'Amasty_CompanyAccount/js/view/payment/method-renderer/company-credit'
    });

    /**
     * Add view logic here if needed
     */
    return Component.extend({});
});
