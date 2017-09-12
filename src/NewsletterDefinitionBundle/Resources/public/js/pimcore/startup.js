pimcore.registerNS("pimcore.plugin.newsletterDefinition");

pimcore.plugin.newsletterDefinition = Class.create(pimcore.plugin.admin, {
    getClassName: function () {
        return "pimcore.plugin.newsletterDefinition";
    },

    initialize: function () {
        pimcore.plugin.broker.registerPlugin(this);
    },

    pimcoreReady: function (params, broker) {

    }
});

new pimcore.plugin.newsletterDefinition();
