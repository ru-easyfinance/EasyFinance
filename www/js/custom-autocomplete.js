$(function() {
    _Suggest.bind();
});

_Suggest = {
    ufdReady: false,
    listHeight: 200,

    bind: function() {
        this.setListeners();
    },

    checkStatus: function() {
        var timer = setInterval(function() {
            if($('.b-custom-input.suggest .ufd button').length) {
                $(document).trigger('ufd-ready');
                clearInterval(timer);
            }
        }, 100);
    },

    setListeners: function() {
        var self = this;

        $('#op_addoperation_but, #op_addtocalendar_but').add(DataTables.table).click(function() {
            self.checkStatus();
        });

        $(document).bind('ufd-ready', function() {
            $('.b-custom-input.suggest input').bind('focus.ones', function() {
                $(this).unbind('focus.ones').closest('.b-custom-input.suggest').find('.b-custom-select-trigger').trigger('click');
            });
            self.ufdReady = true;
        });

        $('.b-custom-input.suggest .b-custom-select-trigger').each(function(i) {
            $(this).click(function() {
                var el = $(this),
                    list = el.parent().find('.ufd').data('dropdown'),
                    listWrapper = list.find('.list-wrapper'),
                    input = el.closest('.b-custom-input');

                function keyup() {
                    listWrapper.width('auto').find('.list-scroll').width('auto');
                    if(listWrapper.width() < input.width()) listWrapper.width(input.width()).find('.list-scroll').width(input.width() - 2);
                }

                function show() {
                    hide();
                    el.closest('.suggest').addClass('open');
                    listWrapper.show().height(self.listHeight + 1).find('.list-scroll').height(self.listHeight);
                    keyup();
                    setTimeout(function() {
                        el.parent().find('input')[0].select();
                    }, 200);
                }

                function hide() {
                    $('.suggest').removeClass('open');
                    $('.list-wrapper').hide();
                }

                if(!el.data('open') && el.data('open') != false) {
                    el.parent().find('input').keyup(function() {
                        keyup();
                    }).focus(function() {
                        el.data('open', true);
                        show();
                    });
                    keyup();
                }

                if(!el.data('open')) {
                    el.data('open', true).parent().find('input').trigger('focus');
                    if(!self.ufdReady) {
                        $(document).bind('ufd-ready', function() {
                            show();
                        });
                    } else {
                        show();
                    }
                } else {
                    el.data('open', false);
                    hide();
                }

                $(document).bind('keydown.esc', function(e) {
                    if(e.keyCode == 27) {
                        hide();
                        el.removeData('open');
                    } else if(e.keyCode == 9) {
                        hide();
                    }
                }).click(function(e) {
                    var target = $(e.target);
                    if(!target.closest('.b-custom-input.suggest.open').length) {
                        el.data('open', false);
                        hide();
                    }
                });
            });
        });
    }
};
