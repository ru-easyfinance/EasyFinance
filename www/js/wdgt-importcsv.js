var importcsv = function (element, doc, dispatcher) {
    this.element = $(element);
    this.doc = doc;
    this.dispatcher = dispatcher;

    // mixin the widget here

    this.__init__();
}

importcsv.prototype = {
    __init__: function() {
        utils.initControls(this.element);
    }
}

$(function(){
    var uploadFrm = $('.js-widget-importcsv');
    if (uploadFrm.length) {
        new importcsv(uploadFrm, $(document), null)
    }
})