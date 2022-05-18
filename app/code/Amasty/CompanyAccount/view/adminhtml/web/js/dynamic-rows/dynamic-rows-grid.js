/**
 * Rewrite for add prototype dependency , because prototype needed for each function used on magento < 230
 * https://github.com/magento/magento2/blob/2.2.9/app/code/Magento/Ui/view/base/web/js/dynamic-rows/dynamic-rows-grid.js#L268
 */
define([
    'prototype',
    'Magento_Ui/js/dynamic-rows/dynamic-rows-grid'
], function (_, dynamicRows) {
    'use strict';

    return dynamicRows.extend({
        reload: function () {
            this._super();
            this.parsePagesData(this.recordData());
            this.changePage();
        }
    });
});
