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
                if($.browser.webkit || $.browser.safari) {
                    input.attr('placeholder', el.text());
                } else {
                    input.data('value', input.val());
                    el.next().unbind('focus.placeholder').unbind('blur.placeholder').bind('focus.placeholder', function() {
                        self.show(el, $(this));
                    }).bind('blur.placeholder', function() {
                        if(el.hasClass('error')) {
                            if(input.val() == input.data('value')) self.hide(el, $(this));
                        } else {
                            if(input.val() == '') self.hide(el, $(this));
                        }
                    });
                    if(el.hasClass('error') || el.next().val() == '') self.hide(el, el.next());
                }
            }
        });
    },

    show: function(placeholder, input) {
        placeholder.hide();
        input.css('opacity', 1);
    },

    hide: function(placeholder, input) {
        placeholder.show();
        input.css('opacity', 0);
    }
};
