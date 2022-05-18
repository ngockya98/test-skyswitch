
define([
    'underscore',
    'Magento_Ui/js/grid/massactions',
    'uiRegistry',
    'mageUtils',
    'Magento_Ui/js/lib/collapsible',
    'Magento_Ui/js/modal/confirm',
    'Magento_Ui/js/modal/alert',
    'mage/translate'
], function (_, Massactions, registry, utils, Collapsible, confirm, alert, $t) {
    'use strict';

    return Massactions.extend({
         /**
          * Default action callback. Sends selections data
          * via POST request.
          *
          * @param {Object} action - Action data.
          * @param {Object} data - Selections data.
          */
        defaultCallback: function (action, data) {
            var itemsType = data.excludeMode ? 'excluded' : 'selected',
                selections = {};

            selections[itemsType] = data[itemsType];

            if (!selections[itemsType].length) {
                selections[itemsType] = false;
            }

            _.extend(selections, data.params || {});

            if (action.type && action.type.indexOf('amasty') === 0) {
                selections['action'] = action.type;
            }

            utils.submit({
                url: action.url,
                data: selections
            });
        },

        applyMassaction: function (parent, action) {
            var data = this.getSelections(),
                callback;

            action = this.getAction(action.type);

            try {
                var actionElement = jQuery(event.target).parents('.amcompany-customer-action-form').find('select');
            } catch (ex) {
                actionElement = null;
            }

            if (!actionElement || !actionElement.length) {
                actionElement = jQuery('.amcompany-customer-action-form:visible').find('select');
            }

            var value = actionElement.length ? actionElement[0].value : null;

            if (!value && actionElement.length) {
                alert({
                    content: 'Required field is empty.'
                });

                return this;
            }

            if (!data.total) {
                alert({
                    content: this.noItemsMsg
                });

                return this;
            }

            var me = this;
            callback = function () {
                me.massactionCallback(action, data, value)
            };

            action.confirm
                ? this._confirm(action, callback)
                : callback();
        },

        massactionCallback: function (action, data, value) {
            var itemsType = data.excludeMode ? 'excluded' : 'selected',
                selections = {};

            selections[itemsType] = data[itemsType];
            selections['company_id'] = value;
            selections['action'] = action.type;

            if (!selections[itemsType].length) {
                selections[itemsType] = false;
            }

            _.extend(selections, data.params || {});

            utils.submit({
                url: action.url,
                data: selections
            });
        }
    });
});
