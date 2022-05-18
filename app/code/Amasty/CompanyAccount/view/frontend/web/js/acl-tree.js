define([
    'jquery',
    'mage/translate',
    'amaclTree',
], function ($, $t) {
    'use strict';

    $.widget('mage.aclTree', {
        options: {
            data: {},
            selectors: {
                treeWrapper: '[data-amcompany-js="tree"]',
                toolbar: '[data-amcompany-js="toolbar"]',
                form: '[data-amcompany-js="form"]',
                permissions: '[data-amcompany-js="permissions"]'
            },
        },
        nodes: {
            linkButton: $('<button>', {class: 'amcompany-button -clear -link', type: 'button'})
        },

        _create: function () {
            this.nodes.toolbar = this.element.find(this.options.selectors.toolbar);
            this.nodes.treeWrapper = this.element.find(this.options.selectors.treeWrapper);

            this.initTree();
            this.bind();
        },

        initTree: function () {
            var self = this;

            self.nodes.toolbar.append(
                self.nodes.linkButton
                    .clone()
                    .text($t('Expand All'))
                    .click(function () {
                        self.nodes.treeWrapper.jstree("open_all");
                    })
            );

            self.nodes.toolbar.append(
                self.nodes.linkButton
                    .clone()
                    .text($t('Collapse All'))
                    .click(function () {
                        self.nodes.treeWrapper.jstree("close_all");
                    })
            );

            self.nodes.treeWrapper.jstree({
                plugins: ['checkbox'],
                checkbox: {cascade: "", three_state: false},
                expand_selected_onload: true,
                core: {
                    data: this.options.data
                }
            });
        },

        bind: function () {
            var self = this;

            $(self.options.selectors.form).on('submit', function () {
                $(self.options.selectors.permissions).val(self.nodes.treeWrapper.jstree('get_selected'));
            });

            self.nodes.treeWrapper
                .on('select_node.jstree', function (e, data) {
                    if (data.event) {
                        data.instance.select_node(data.node.children_d);

                        $.each(data.node.parents, function (item, val) {
                            data.instance.select_node(val);
                        });
                    }
                })
                .on('deselect_node.jstree', function (e, data) {
                    if (data.event) {
                        data.instance.deselect_node(data.node.children_d);
                    }
                });
        }
    });

    return $.mage.aclTree;
});
