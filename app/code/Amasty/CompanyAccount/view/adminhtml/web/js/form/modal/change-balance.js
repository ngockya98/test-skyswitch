/**
 *  Amasty Change Balance modal logic
 */

define([
    'jquery',
    'Magento_Ui/js/modal/modal-component',
    'mageUtils',
    'uiRegistry'
], function ($, Modal, utils, uiRegistry) {
    'use strict';

    return Modal.extend({
        defaults: {
            valid: false,
            modules: {
                formFieldset: '${ $.store_credit }'
            },
            formData: {
                company_id: '',
                credit_event: {}
             },
            changeBalanceUrl: '${ $.change_balance_url }',
            ignoreTmpls: {
                data: true
            }
        },
        classes: {
            messageBlock: 'amcompany-message-element'
        },

        /**
         * @public
         * @returns {void}
         */
        openModal: function () {
            this._resetFields();
            this._super();
        },

        /**
         * @public
         * @returns {void}
         */
        closeModal: function () {
            this.formFieldset().elems().forEach(function (el) {
                el.disable();
            }, this);

            this.clearMessages(this.modal);

            this._super();
        },

        /**
         * @private
         * @returns {Object}
         */
        _resetFields: function () {
            this.clear();
            this.formFieldset().elems().forEach(function (el) {
                el.enable()
                    .reset();
            }, this);

            return this;
        },

        /**
         * @param {Object} target - DOM jQuery element
         * @param {Array} messages - array of messages
         * @param {String} type - message type
         * @public
         * @returns {void}
         */
        renderMessages: function (target, messages, type) {
            var element = $('<div>', {
                class: this.classes.messageBlock + ' -' + type
            });

            this.clearMessages(target);

            messages.forEach(function (value) {
                target.prepend(element.clone().html('<span>' + value + '</span>'));
            });
        },

        /**
         * @param {Object} target - DOM jQuery element
         * @private
         * @returns {void}
         */
        clearMessages: function (target) {
            $('.' + this.classes.messageBlock, target).remove();
        },

        /**
         * @public
         * @returns {void}
         */
        changeBalance: function () {
            this.valid = true;
            this.formFieldset().elems().forEach(this.validate, this);

            if (this.valid) {
                this.formData.credit_event.currency_event = this.formData.currency_code;
                utils.ajaxSubmit({
                    url: this.changeBalanceUrl,
                    data: this.formData
                }, {ajaxSaveType: 'simple'}).done(function (data) {
                    if (typeof data.errors === 'undefined') {
                        var balanceCard = uiRegistry.get('index = balance'),
                            paidCard = uiRegistry.get('index = be_paid'),
                            eventListing = uiRegistry.get('index = credit_event_grid');

                        balanceCard.balance(data.balance);
                        balanceCard.credit(data.credit);
                        paidCard.bePaid(data.be_paid);
                        paidCard.overdraftSum(data.balance.substring(1));
                        eventListing.reload();
                        this.closeModal();
                    } else {
                        this.renderMessages(this.modal, data.errors, 'error');
                    }
                }.bind(this));
            }
        }
    });
});
