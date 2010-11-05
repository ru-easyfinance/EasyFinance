(function($){
    $.fn.phonefield = function(params) {
        var that = this;

        var defaults = {
            forceRussia: false
        }

        var options = $.extend({}, defaults, params);

        this.bind('change blur', function(evt){
            that.val( utils.toPhone(that.val(), options.forceRussia) );
        })

        return this;
    }
})(jQuery);