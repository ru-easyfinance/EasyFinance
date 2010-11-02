var importcsv = function (element, doc, dispatcher) {
    this.element = $(element);
    this.doc = doc;
    this.dispatcher = dispatcher;

    // mixin the widget here

    this.__init__();
};

importcsv.prototype = {
    frm: null,
    submit: null,

    __init__: function() {
        var that = this;

        this.frm = this.element.find('form');
        this.submit = this.frm.find('input[type="submit"]');

        this.frm.bind('submit', function(evt) { that.onSubmit.apply(that, [evt]) });
    },
    onSubmit: function(evt) {
        evt.preventDefault();
        if (this.frm.find('input[type="file"]').val() == '') {
            utils.notifyUser('Не выбран файл');
            return false;
        }
        this.submit.attr('disabled', 'disabled');

        function onResponse(data) {
            utils.defaultOnSuccess(data);
            this.submit.removeAttr('disabled');
        }

        utils.ajaxUpload(this.frm, onResponse, onResponse, this);

        return false;
    }
};

$(function(){
    var uploadFrm = $('.js-widget-importcsv');
    if (uploadFrm.length) {
        new importcsv(uploadFrm, $(document), null)
    }
});