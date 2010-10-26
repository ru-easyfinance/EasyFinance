if (!wdgt) {
    var wdgt = {}
}

wdgt.budgetwizard = function(element, doc, dispatcher) {
    this.element = element;
    this.doc = doc;
    this.__init__()
}

wdgt.budgetwizard.prototype = {
    tpl: {

    },

    __init__: function() {
        var that = this;
        this.doc.bind('show.budgetwizard', utils.proxy(this.show, this));

        this.dialogue = this.element.find('.js-control-dialogue');
        this.dialogue.dialog({
            autoOpen: false,
            width: 650,
            bgiframe: true,
            resizable: false
        });

        this.element = this.dialogue.dialog('widget');
        this.steps = this.element.find('.b-budgetwizard-step');

        utils.initControls(this.element);
    },

    show: function() {
        this.dialogue.dialog('open');
        this.switchStep(1);
    },

    switchStep: function(index) {
        this.steps.addClass('hidden');
        this.steps.eq(index - 1).removeClass('hidden');
    }
}