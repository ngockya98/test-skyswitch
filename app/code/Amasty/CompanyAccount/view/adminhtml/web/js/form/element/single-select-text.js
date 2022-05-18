/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'Magento_Ui/js/form/element/abstract',
    'uiRegistry',
    'uiLayout',
    'mageUtils',
    'underscore'
], function (Abstract, registry, layout, utils, _) {
    'use strict';

    return Abstract.extend({
        defaults: {
            additionalClasses: {},
            displayArea: 'outsideGroup',
            elementTmpl: 'Amasty_CompanyAccount/form/component/single-select-text',
            visible: true,
            title: '',
            valueText: '',
            dataScope: '',
            dataScopeText: '',
            error: '',
            required: true,
            labelVisible: true,
            uid: utils.uniqueid(),
            uidText: utils.uniqueid(),
            disabled: false,
            insertData: [],
            cacheInsertData: [],
            listens: {
                'insertData': 'processingSuperUserIds',
            },
            links: {
                insertData: '${ $.provider }:data.super_user_ids'
            }
        },

        /**
         * Initializes component.
         *
         * @returns {Object} Chainable.
         */
        initialize: function () {
            this._super()
                ._setClasses();
        },

        /** @inheritdoc */
        initObservable: function () {
            return this._super()
                .observe([
                    'visible',
                    'disabled',
                    'super_user_ids',
                    'value',
                    'valueText'
                ]);
        },

        hasAddons: function () {
            return false;
        },

        hasService: function () {
            return false;
        },

        initLinks: function () {
            return this._super();
        },

        /**
         * Performs configured actions
         */
        action: function () {
            this.actions.forEach(this.applyAction, this);
        },

        /**
         * Apply action on target component,
         * but previously create this component from template if it is not existed
         *
         * @param {Object} action - action configuration
         */
        applyAction: function (action) {
            var targetName = action.targetName,
                params = utils.copy(action.params) || [],
                actionName = action.actionName,
                target;

            if (!registry.has(targetName)) {
                this.getFromTemplate(targetName);
            }
            target = registry.async(targetName);

            if (target && typeof target === 'function' && actionName) {
                params.unshift(actionName);
                target.apply(target, params);
            }
        },

        /**
         * Create target component from template
         *
         * @param {Object} targetName - name of component,
         * that supposed to be a template and need to be initialized
         */
        getFromTemplate: function (targetName) {
            var parentName = targetName.split('.'),
                index = parentName.pop(),
                child;

            parentName = parentName.join('.');
            child = utils.template({
                parent: parentName,
                name: index,
                nodeTemplate: targetName
            });
            layout([child]);
        },

        /**
         * Extends 'additionalClasses' object.
         *
         * @returns {Object} Chainable.
         */
        _setClasses: function () {
            if (typeof this.additionalClasses === 'string') {
                if (this.additionalClasses === '') {
                    this.additionalClasses = {};

                    return this;
                }

                this.additionalClasses = this.additionalClasses
                    .trim()
                    .split(' ')
                    .reduce(function (classes, name) {
                            classes[name] = true;

                            return classes;
                        }, {}
                    );
            }

            _.extend(this.additionalClasses, {
                '_required':   this.required,
            });

            return this;
        },

        /**
         * Processing insert data
         *
         * @param {Object} data
         */
        processingSuperUserIds: function (data) {
            var changes,
                obj = {};

            changes = this._checkGridData(data);

            if (changes.length) {
                this.cacheInsertData = changes;

                this.set('value', changes[0]['entity_id']);
                this.set('valueText', changes[0]['name']);

                this.source.set(this.dataScope, changes[0]['entity_id']);
                this.source.set(this.dataScopeText, changes[0]['name']);
                this.source.set('data.super_user_ids', changes);
            }
        },

        /**
         * Check changed records
         *
         * @param {Array} data - array with records data
         * @returns {Array} Changed records
         */
        _checkGridData: function (data) {
            var cacheLength = this.cacheInsertData.length,
                curData = data.length,
                max = cacheLength > curData ? this.cacheInsertData : data,
                changes = [],
                obj = {};

            max.forEach(function (record, index) {
                obj['entity_id'] = record['entity_id'];
                obj['name'] = record['name'];

                if (!_.where(this.cacheInsertData, obj).length) {
                    changes.push(data[index]);
                }
            }, this);

            return changes;
        }
    });
});
