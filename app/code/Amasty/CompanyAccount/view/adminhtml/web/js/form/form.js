define([
    'Magento_Ui/js/form/form',
    'uiRegistry',
    'mage/translate',
    'Magento_Ui/js/modal/confirm'
], function (uiForm, uiRegistry, __, uiConfirm) {
    'use strict';

    return uiForm.extend({

        save: function (redirect, data) {
            var statusField = uiRegistry.get('index = status');
            this.validate();

            if (!this.additionalInvalid && !this.source.get('params.invalid')) {
                this.setAdditionalData(data);
                if (statusField.value() != statusField.initialValue) {
                    if (statusField.value() == 2) {
                        this.uiConfirmAction(
                            __(
                                'Are you sure you want to inactivate this company? Company users will be able to login, '
                                + 'but order placement will be restricted for them.'
                            ),
                            redirect
                        );
                    } else if (statusField.value() == 3) {
                        this.uiConfirmAction(
                            __(
                                'Are you sure you want to reject this company? Confirmation will block login for all '
                                + 'company users. Please specify a reason for rejection to explain your choice '
                                + 'to Company Administrator'
                            ),
                            redirect
                        );
                    } else {
                        this.submit(redirect);
                    }
                } else {
                    this.submit(redirect);
                }
            } else {
                this.focusInvalid();
            }
        },

        uiConfirmAction: function (content, redirect) {
            var self = this;
            uiConfirm({
                content:  content,
                actions: {
                    confirm: function () {
                        self.submit(redirect);
                    }
                }
            });
        }

    });
});
