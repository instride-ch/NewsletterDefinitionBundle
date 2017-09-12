/**
 * Newsletter Definition
 *
 * LICENSE
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) 2016-2017 W-Vision (http://www.w-vision.ch)
 * @license    https://github.com/w-vision/NewsletterDefinition/blob/master/gpl-3.0.txt GNU General Public License version 3 (GPLv3)
 */

pimcore.registerNS("pimcore.document.newsletters.addressSourceAdapters.newsletterDefinition");
pimcore.document.newsletters.addressSourceAdapters.newsletterDefinition = Class.create({

    initialize: function (document, data) {
        this.document = document;
    },

    /**
     * returns name of corresponding php implementation class
     *
     * @returns {string}
     */
    getName: function () {
        return "newsletterDefinition";
    },

    /**
     * returns layout for sending panel
     *
     * @returns {Ext.form.Panel|*}
     */
    getLayout: function () {

        if (!Ext.isDefined(this.layout)) {
            this.layout = Ext.create('Ext.form.Panel', {
                border: false,
                autoScroll: true,
                defaults: {labelWidth: 200},
                items: [
                    {
                        xtype: "combo",
                        name: "class",
                        fieldLabel: t("class"),
                        triggerAction: 'all',
                        editable: false,
                        store: new Ext.data.Store({
                            autoDestroy: true,
                            proxy: {
                                type: 'ajax',
                                url: "/admin/newsletter/get-available-classes",
                                reader: {
                                    type: 'json',
                                    rootProperty: 'data'
                                }
                            },
                            fields: ["name"]
                        }),
                        width: 600,
                        displayField: 'name',
                        valueField: 'name',
                        listeners: {
                            change: function (combo, newValue, oldValue) {
                                if (newValue) {
                                    combo.up("form").down("#addButton").enable();
                                    combo.up("form").down("#definition").enable();

                                    var values = this.up("form").getForm().getFieldValues();

                                    combo.up("form").down("#definition").getStore().reload({
                                        params: {
                                            className: values['class']
                                        }
                                    });
                                }
                                else {
                                    combo.up("form").down("#addButton").disable();
                                    combo.up("form").down("#definition").disable();
                                }
                            }
                        }
                    },
                    {
                        xtype: 'container',
                        layout: {
                            type: 'hbox',
                            align: 'stretch'
                        },
                        items: [
                            {
                                xtype: "combo",
                                flex: 6,
                                name: "definition",
                                disabled: true,
                                fieldLabel: t("newsletter_definition"),
                                triggerAction: 'all',
                                editable: false,
                                itemId: 'definition',
                                labelWidth: 200,
                                store: new Ext.data.Store({
                                    autoDestroy: true,
                                    proxy: {
                                        type: 'ajax',
                                        url: "/admin/newsletter-definition/list",
                                        reader: {
                                            type: 'json',
                                            rootProperty: 'data'
                                        }
                                    },
                                    fields: ["id", "name"]
                                }),
                                width: 600,
                                displayField: 'name',
                                valueField: 'id',
                                listeners: {
                                    change: function (combo, newValue, oldValue) {
                                        if (newValue) {
                                            combo.up("form").down("#editButton").enable();
                                            combo.up("form").down("#deleteButton").enable();
                                        }
                                        else {
                                            combo.up("form").down("#editButton").disable();
                                            combo.up("form").down("#deleteButton").disable();
                                        }
                                    }.bind(this)
                                }
                            },
                            {
                                xtype: 'container',
                                width: 10
                            },
                            {
                                xtype: 'button',
                                flex: 1,
                                iconCls: 'pimcore_icon_add',
                                disabled: true,
                                itemId: 'addButton',
                                bodyPadding: '0 0 0 10px',
                                handler: function (button) {
                                    Ext.MessageBox.prompt(
                                        t('add_newsletter_definition'),
                                        t('enter_the_name_of_the_new_newsletter_definition'),
                                        this.addDefinitionComplete.bind(this, button),
                                        null,
                                        null,
                                        ""
                                    );
                                }.bind(this)
                            },
                            {
                                xtype: 'container',
                                width: 10
                            },
                            {
                                xtype: 'button',
                                flex: 1,
                                iconCls: 'pimcore_icon_edit',
                                disabled: true,
                                itemId: 'editButton',
                                bodyPadding: '0 0 0 10px',
                                handler: function (btn) {
                                    var defCombo = btn.up("form").down("#definition");
                                    var id = defCombo.getValue();
                                    var record = defCombo.getStore().getById(id);

                                    if (id) {
                                        this.openDefinition(btn, record.data);
                                    }
                                }.bind(this)
                            },
                            {
                                xtype: 'container',
                                width: 10
                            },
                            {
                                xtype: 'button',
                                flex: 1,
                                iconCls: 'pimcore_icon_delete',
                                disabled: true,
                                itemId: 'deleteButton',
                                bodyPadding: '0 0 0 10px',
                                handler: function (btn) {
                                    var id = btn.up("form").down("#definition").getValue();

                                    if (id) {
                                        this.deleteDefinition(btn, id);
                                    }
                                }.bind(this)
                            }
                        ]
                    },
                    {
                        xtype: 'container',
                        height: 10
                    }
                ]
            });
        }

        return this.layout;
    },

    addDefinitionComplete: function (button, buttonResult, value, object) {
        var values = button.up("form").getForm().getFieldValues();
        var regresult = value.match(/[a-zA-Z0-9_\-]+/);

        if (buttonResult === "ok" && value.length > 2 && regresult == value) {
            Ext.Ajax.request({
                url: "/admin/newsletter-definition/create",
                params: {
                    name: value,
                    className: values['class']
                },
                success: function (response) {
                    var data = Ext.decode(response.responseText);

                    if (!data || !data.success) {
                        Ext.Msg.alert(t('add_newsletter_definition'), t('problem_creating_new_target'));
                    } else {
                        button.up("form").down("#definition").getStore().load({
                            callback: function() {
                                button.up("form").down("#definition").setValue(data.data.id);
                            }
                        });
                        //this.openTarget(intval(data.id));
                    }
                }.bind(this)
            });
        } else if (buttonResult === "cancel") {
            return;
        }
        else {
            Ext.Msg.alert(t('add_newsletter_definition'), t('naming_requirements_3chars'));
        }
    },

    deleteDefinition: function(button, definitionId) {
        Ext.Ajax.request({
            url: "/admin/newsletter-definition/delete",
            params: {
                id: definitionId
            },
            success: function (response) {
                button.up("form").down("#definition").getStore().load();
                button.up("form").down("#definition").setValue(null);
            }.bind(this)
        });
    },

    openDefinition: function(button, data) {
        new pimcore.plugin.newsletterDefinition.definitionDialog(data, function() {
            button.up("form").down("#definition").getStore().load();
        });
    },

    /**
     * returns values for sending process
     *
     * @returns {*|Object}
     */
    getValues: function () {

        if (!this.layout.rendered) {
            throw "settings not available";
        }

        return this.getLayout().getForm().getFieldValues();
    }
});