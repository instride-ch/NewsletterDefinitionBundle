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

pimcore.registerNS("pimcore.plugin.newsletterDefinition.definitionDialog");
pimcore.plugin.newsletterDefinition.definitionDialog = Class.create({

    initialize: function (data, callback) {
        this.data = data;
        this.callback = Ext.isFunction(callback) ? callback : Ext.emptyFn;

        this.window = new Ext.window.Window({
            activeTab: 0,
            title: this.data.name,
            modal: true,
            width: 800,
            height: 600,
            layout: 'border',
            buttons: [{
                text: t("save"),
                iconCls: "pimcore_icon_apply",
                handler: this.save.bind(this)
            }],
            items: [this.getFilters()]
        });

        this.fieldsStore = new Ext.data.JsonStore({
            autoDestroy: true,
            proxy: {
                type: 'ajax',
                url: "/admin/newsletter-definition/fields",
                reader: {
                    type: 'json',
                    rootProperty: 'data'
                },
                extraParams: {
                    id: this.data.id
                }
            },
            fields: ["code", "name"]
        });
        this.fieldsStore.load();

        // fill data into filters
        if (this.data.filters && this.data.filters.length > 0) {
            for (var i = 0; i < this.data.filters.length; i++) {
                this.addFilter(this.data.filters[i]);
            }
        }

        this.window.show();
    },

    getFilters: function () {
        this.filtersContainer = new Ext.Panel({
            autoScroll: true,
            forceLayout: true,
            region: 'center',
            tbar: [{
                iconCls: "pimcore_icon_add",
                text: t('add'),
                handler: this.addFilter.bind(this, {})
            }],
            border: false
        });

        return this.filtersContainer;
    },

    addFilter: function (data) {

        var item = this.itemFilter(data);

        // add logic for brackets
        var tab = this;
        item.on("afterrender", function (el) {
            el.getEl().applyStyles({position: "relative", "min-height": "40px", "border-bottom": "1px solid #d0d0d0"});
            var leftBracket = el.getEl().insertHtml("beforeEnd",
                '<div class="pimcore_targeting_bracket pimcore_targeting_bracket_left">(</div>', true);
            var rightBracket = el.getEl().insertHtml("beforeEnd",
                '<div class="pimcore_targeting_bracket pimcore_targeting_bracket_right">)</div>', true);

            if (data["bracketLeft"]) {
                leftBracket.addCls("pimcore_targeting_bracket_active");
            }
            if (data["bracketRight"]) {
                rightBracket.addCls("pimcore_targeting_bracket_active");
            }

            // open
            leftBracket.on("click", function (ev, el) {
                var bracket = Ext.get(el);
                bracket.toggleCls("pimcore_targeting_bracket_active");

                tab.recalculateBracketIdent(tab.filtersContainer.items);
            });

            // close
            rightBracket.on("click", function (ev, el) {
                var bracket = Ext.get(el);
                bracket.toggleCls("pimcore_targeting_bracket_active");

                tab.recalculateBracketIdent(tab.filtersContainer.items);
            });

            // make ident
            tab.recalculateBracketIdent(tab.filtersContainer.items);
        });

        this.filtersContainer.add(item);
        item.updateLayout();
        this.filtersContainer.updateLayout();

        this.currentIndex++;

        this.recalculateButtonStatus();
    },

    itemFilter: function (data) {
        if (!Ext.isDefined(data)) {
            data = {};
        }
        var myId = Ext.id();

        return new Ext.form.FormPanel({
            id: myId,
            forceLayout: true,
            style: "margin: 10px 0 0 0",
            bodyStyle: "padding: 10px 30px 10px 30px; min-height:40px;",
            tbar: this.getTopBar('filter', myId, this, data),
            items: [{
                xtype: 'combo',
                fieldLabel: t('field'),
                displayField: 'name',
                valueField: 'identifier',
                name: "field",
                store: this.fieldsStore,
                triggerAction: "all",
                mode: "local",
                width: 350,
                value: data.hasOwnProperty('field') ? data.field : null
            }, {
                xtype: 'combo',
                fieldLabel: t('field'),
                typeAhead: false,
                editable: false,
                forceSelection: true,
                name: "filterOperator",
                store: [
                    ['equal', '='],
                    ['not_equal', '!='],
                    ['greater', '>'],
                    ['greaterEqual', '>='],
                    ['lower', '<'],
                    ['lowerEqual', '<='],
                    ['like', 'like'],
                    ['startsWith', 'startsWith'],
                    ['endsWith', 'endsWith']
                ],
                triggerAction: "all",
                mode: "local",
                width: 350,
                value: data.hasOwnProperty('filterOperator') ? data.filterOperator : null
            }, {
                xtype: 'textfield',
                fieldLabel: t('value'),
                name: "value",
                width: 350,
                value: data.hasOwnProperty('value') ? data.value : null
            }]
        });
    },

    save: function () {

        var saveData = {};

        var filtersData = [];
        var filter, tb, operator;
        var filters = this.filtersContainer.items.getRange();
        for (var i = 0; i < filters.length; i++) {
            filter = filters[i].getForm().getFieldValues();

            // get the operator (AND, OR, AND_NOT)
            tb = filters[i].getDockedItems()[0];

            if (tb.getComponent("toggle_or").pressed) {
                operator = "or";
            } else if (tb.getComponent("toggle_and_not").pressed) {
                operator = "and_not";
            } else {
                operator = "and";
            }
            filter["operator"] = operator;

            // get the brackets
            filter["bracketLeft"] = Ext.get(filters[i].getEl().query(".pimcore_targeting_bracket_left")[0]).hasCls("pimcore_targeting_bracket_active");
            filter["bracketRight"] = Ext.get(filters[i].getEl().query(".pimcore_targeting_bracket_right")[0]).hasCls("pimcore_targeting_bracket_active");

            filtersData.push(filter);
        }
        saveData["filters"] = filtersData;

        Ext.Ajax.request({
            url: "/admin/newsletter-definition/save",
            params: {
                id: this.data.id,
                data: Ext.encode(saveData)
            },
            method: "post",
            success: function () {
                pimcore.helpers.showNotification(t("success"), t("item_saved_successfully"), "success");

                this.callback.call(this);
                this.window.close();
            }.bind(this)
        });
    },

    recalculateButtonStatus: function () {
        var filters = this.filtersContainer.items.getRange();
        var tb;
        for (var i = 0; i < filters.length; i++) {
            tb = filters[i].getDockedItems()[0];

            if (i === 0) {
                tb.getComponent("toggle_and").hide();
                tb.getComponent("toggle_or").hide();
                tb.getComponent("toggle_and_not").hide();
            } else {
                tb.getComponent("toggle_and").show();
                tb.getComponent("toggle_or").show();
                tb.getComponent("toggle_and_not").show();
            }
        }
    },


    /**
     * make ident for bracket
     * @param list
     */
    recalculateBracketIdent: function (list) {
        var ident = 0, lastIdent = 0, margin = 20;
        var colors = ["transparent", "#007bff", "#00ff99", "#e1a6ff", "#ff3c00", "#000000"];

        list.each(function (filter) {

            // only rendered filters
            if (filter.rendered === false) {
                return;
            }

            // html from this filter
            var item = filter.getEl();


            // apply ident margin
            item.applyStyles({
                "margin-left": margin * ident + "px",
                "margin-right": margin * ident + "px"
            });


            // apply colors
            if (ident > 0) {
                item.applyStyles({
                    "border-left": "1px solid " + colors[ident],
                    "border-right": "1px solid " + colors[ident]
                });
            } else {
                item.applyStyles({
                    "border-left": "0px",
                    "border-right": "0px"
                });
            }


            // apply specials :-)
            if (ident === 0) {
                item.applyStyles({
                    "margin-top": "10px"
                });
            } else if (ident === lastIdent) {
                item.applyStyles({
                    "margin-top": "0px",
                    "margin-bottom": "0px"
                });
            } else {
                item.applyStyles({
                    "margin-top": "5px"
                });
            }


            // remember current ident
            lastIdent = ident;

            // check if a bracket is open
            if (item.select('.pimcore_targeting_bracket_left.pimcore_targeting_bracket_active').getCount() === 1) {
                ident++;
            }
            // check if a bracket is close
            else if (item.select('.pimcore_targeting_bracket_right.pimcore_targeting_bracket_active').getCount() === 1) {
                if (ident > 0) {
                    ident--;
                }
            }
        });

        this.filtersContainer.updateLayout();
    },

    getTopBar: function (name, index, parent, data) {

        var toggleGroup = "g_" + index + parent.data.id;
        if(!data["operator"]) {
            data.operator = "and";
        }

        return [{
            xtype: "tbtext",
            text: "<b>" + name + "</b>"
        },"-",{
            iconCls: "pimcore_icon_up",
            handler: function (blockId, parent) {

                var container = parent.filtersContainer;
                var blockElement = Ext.getCmp(blockId);
                var index = pimcore.settings.targeting.conditions.detectBlockIndex(blockElement, container);

                var newIndex = index-1;
                if(newIndex < 0) {
                    newIndex = 0;
                }

                container.remove(blockElement, false);
                container.insert(newIndex, blockElement);

                parent.recalculateButtonStatus();
                parent.recalculateBracketIdent(parent.filtersContainer.items);

                pimcore.layout.refresh();
            }.bind(window, index, parent)
        },{
            iconCls: "pimcore_icon_down",
            handler: function (blockId, parent) {
                var container = parent.filtersContainer;
                var blockElement = Ext.getCmp(blockId);
                var index = pimcore.settings.targeting.conditions.detectBlockIndex(blockElement, container);

                container.remove(blockElement, false);
                container.insert(index+1, blockElement);

                parent.recalculateButtonStatus();
                parent.recalculateBracketIdent(parent.filtersContainer.items);

                pimcore.layout.refresh();
            }.bind(window, index, parent)
        },"-", {
            text: t("AND"),
            toggleGroup: toggleGroup,
            enableToggle: true,
            itemId: "toggle_and",
            pressed: (data.operator == "and") ? true : false
        },{
            text: t("OR"),
            toggleGroup: toggleGroup,
            enableToggle: true,
            itemId: "toggle_or",
            pressed: (data.operator == "or") ? true : false
        },{
            text: t("AND_NOT"),
            toggleGroup: toggleGroup,
            enableToggle: true,
            itemId: "toggle_and_not",
            pressed: (data.operator == "and_not") ? true : false
        },"->",{
            iconCls: "pimcore_icon_delete",
            handler: function (index, parent) {
                parent.filtersContainer.remove(Ext.getCmp(index));
                parent.recalculateButtonStatus();
                parent.recalculateBracketIdent(parent.filtersContainer.items);
            }.bind(window, index, parent)
        }];
    }
});

