$(function() {
    _Suggest.bind();
});

_Suggest = {
    ufdReady: false,
    listHeight: 200,

    bind: function() {
        this.setListeners();
    },

    setListeners: function() {
        var self = this;
        $('#op_addoperation_but, #op_addtocalendar_but').add(DataTables.table).click(function() {
            self.appendEvents();
        });
    },

    appendEvents: function() {
        var self = this;

        $('.b-custom-input.suggest input[name="_ufd"]').each(function(i) {
            var el = $(this),
                list = el.parent().data('dropdown'),
                input = el.closest('.b-custom-input');

            el.keyup(function() {
                self.onKeyUp(list.listWrapper, input);
            });
        });

        $('.b-custom-input.suggest .b-custom-select-trigger').each(function(i) {
            $(this).unbind('click').click(function() {
                var el = $(this),
                    list = el.parent().find('.ufd').data('dropdown'),
                    input = el.closest('.b-custom-input');

                $('button, input', input).unbind('click, focus');
                function show() {
                    $(document).trigger('autocomplete.hide');
                    list.filter(true);
                    list.inputFocus();
                    list.showList();
                    self.onKeyUp(list.listWrapper, input);
                    input.addClass('open');
                }
                function hide() {
                    list.hideList();
                    //list.inputFocus();
                    input.removeClass('open');
                }

                if (!input.hasClass('open')) {
                    show();
                } else {
                    hide();
                }

                if (!el.hasClass('processed')) {
                    $(document).bind('event.esc', function() {
                        hide();
                        self.hide();
                    }).bind('autocomplete.hide', function() {
                        hide();
                    }).click(function(e) {
                        if (!$(e.target).closest('.b-custom-input.suggest').length) hide();
                    });
                    el.addClass('processed');
                }
            });
        });

        $('#op_checkReminders').click(function() {
            $('#operationEdit_reminders select').trigger('change.customselect');
        });

        $('.financobject_block .add span').click(function() {
            $('#targets_category').trigger('change.customselect');
        });
    },

    onKeyUp: function(list, input) {
        list.width('auto').find('.list-scroll').width('auto');
        if (list.width() < input.width()) list.width(input.width()).find('.list-scroll').width(input.width() - 2);
    },

    hide: function() {
        $('.ui-dialog:visible .ui-icon-closethick').trigger('click');
    }
};
