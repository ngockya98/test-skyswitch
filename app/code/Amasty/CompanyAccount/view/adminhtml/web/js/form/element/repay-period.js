define([
    'Magento_Ui/js/form/element/select'
], function (select) {
    return select.extend({
        show: function () {
            if (this.value() === '1') {
                // trigger value reload for switcher work. Penalty field must show.
                this.value('0');
                this.value('1');
            }

            this._super();
        }
    });
});
