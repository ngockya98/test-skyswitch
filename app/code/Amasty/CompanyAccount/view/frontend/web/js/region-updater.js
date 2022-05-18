/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'regionUpdater'
], function ($, regionUpdater) {
    'use strict';

    $.widget('mage.amcompanyRegionUpdater', regionUpdater, {

        _updateRegion: function (country) {
            // Clear validation error messages
            var regionInput = $(this.options.regionInputId),
                regionInputValue = regionInput.val();

            this._super(country);

            if (!regionInput.is(":visible")) {
                $(this.options.regionListId).val(this.options.regionListIdValue);
            }
            regionInput.val(regionInputValue);
        }
    });

    return $.mage.amcompanyRegionUpdater;
});
