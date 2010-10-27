(function($){
    $.fn.ajaxupload = function(options) {
        var that = this;

        function onSubmit(evt) {
            evt.preventDefault();

            utils.ajaxUpload(that, null, null, null, null, options);

            return false;
        }

        this.bind('submit', onSubmit);

        return this;
    }
})(jQuery);