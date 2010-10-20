(function($){
    $.fn.prompt = function(params) {
        var that = this;

        var parentWidget = this.closest('.js-widget');

        var defaults = {
            autoOpen: false,
            modal: true,
            closeOnEscape: true,
            width: 'auto',
            buttons: {
                'Отправить': function() {
                    parentWidget.trigger('widget.ok')
                },
                'Отмена': function() {
                    parentWidget.trigger('widget.cancel')
                }
            }
        };

        this.dialog( $.extend({}, defaults, params) );

        return this;
    }
})(jQuery);