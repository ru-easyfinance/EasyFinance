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
            self.checkUfd();
        });
    },

    checkUfd: function() {
        var self = this,
            checker = setInterval(function() {
            if ($('.b-custom-input.suggest input[name="_ufd"]')) {
                clearInterval(checker);
                self.appendEvents();
            }
        }, 100);
    },

    appendEvents: function() {
        var self = this;

        // Навешиваем кастомные события на ufd инпуты, для полного контроля действий
        $('.b-custom-input.suggest input[name="_ufd"]').each(function(i) {
            var el = $(this),
                list = el.parent().data('dropdown'),
                input = el.closest('.b-custom-input'),
                focus = function() {
                    $('.b-custom-select-trigger', input).trigger('click');
                };

            el.bind('keyup', function() {
                self.onKeyUp(list.listWrapper, input);
            });

            el.unbind('focus blur click');

            el.bind('focus', focus);
            el.bind('blur', function() {
                el.unbind('focus', focus).bind('focus', focus); // freakin' magic для оперы
            })

        });

        // Проверка ушел ли фокус с ufd инпутов, если да то убираем дропдауны
        $('.b-custom-input.suggest:first').closest('form').find('input').focus(function() {
             $('.b-custom-input.open.suggest input[name="_ufd"]').each(function() {
                $(this).closest('.b-custom-input').find('.b-custom-select-trigger').trigger('click');
             });
        });

        // Навешиваем события на кнопочки активации дропдауна
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
