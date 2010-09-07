$(function() {
    _InputPlaceholder.bind();
});

_InputPlaceholder = {
    bind: function() {
        this.setListeners();
    },

    setListeners: function() {
        var self = this;
        $('.b-placeholder').each(function() {
            var el = $(this),
                input = el.next();
            if(el.text() != '') {
                input.data('value', input.val());
                el.show().next().css('opacity', 0).unbind('focus.placeholder').unbind('blur.placeholder').bind('focus.placeholder', function() {
                    el.hide();
                    $(this).css('opacity', 1);
                }).bind('blur.placeholder', function() {
                    if(input.val() == input.data('value')) {
                        el.show();
                        $(this).css('opacity', 0);
                    }
                });
            }
        });
    }
};