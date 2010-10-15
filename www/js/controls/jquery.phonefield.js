(function($){
    $.fn.phonefield = function() {
        var that = this;

        this.bind('change blur', function(evt){
            that.val( utils.toPhone(that.val()) );
        })

        return this;
    }
})(jQuery);