/**
 * Ядро виджетной системы, класс диспетчера
 */
function CDispatcher() {
}

CDispatcher.prototype = {
    widgetSelector: ".js-widget",

    models: {}, // для реализации регистра

    widgets: [],

    doc: null,

    __init__: function() {
        this.doc = $(document);

        $(this.widgetSelector).each(function(index, elem) {
            this.register($(node));
        });
    },

    register: function(node) {
        var type = utils.getType.widget(node);
        var params = utils.getParams(node);

        this.widgets.push( new wdgt[type](node, this.doc, this) );
    },

    getModel: function(modelName) {
        return this.models[modelName];
    }
}