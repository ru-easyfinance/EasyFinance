(function($){
    $.fn.ajaxupload = function(params) {
        var that = this;

        var defaults = {
        }
        var options = $.extend({}, defaults, params);

        function onSubmit(evt) {
            evt.preventDefault();

            utils.ajaxUpload(that, null, null, null, null, options);

            return false;
        }

        this.bind('submit', onSubmit);

        return this;
    }
})(jQuery);