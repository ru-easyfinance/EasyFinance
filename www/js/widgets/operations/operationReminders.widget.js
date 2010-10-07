easyFinance.widgets.operationReminders = function(){
    var _model = null;
    var _$node = null;
    var _mode = "";

    // отображает настройки из профиля
    // возвращает true, если уведомления используются
    // и false, если они по умолчанию отключены
    function setDefaults() {
        var settings = _model.getRemindersSettings();
        var using = false;
        if (settings.mailEnabled == true || settings.mailEnabled == "1") {
            $("#checkReminderMail").attr('checked', true);
            using = true;
        }

        $("#mailDaysBefore").val(settings.mailDaysBefore),
        $("#mailHour").val(settings.mailHour);
        $("#mailMinutes").val(settings.mailMinutes);

        if (settings.smsEnabled == true || settings.smsEnabled == "1") {
            $("#checkReminderSms").attr('checked', true);
            using = true;
        }

        $("#smsPhone").val(settings.smsPhone),
        $("#smsDaysBefore").val(settings.smsDaysBefore),
        $("#smsHour").val(settings.smsHour);
        $("#smsMinutes").val(settings.smsMinutes);

        $("#op_checkReminders").attr("checked", using);

        return using;
    }

    function getSettings() {
        var params = {
            mailEnabled: $("#checkReminderMail").attr('checked') ? true : false,
            mailDaysBefore: $("#mailDaysBefore").val(),
            mailHour: $("#mailHour").val(),
            mailMinutes: $("#mailMinutes").val(),

            smsEnabled: $("#checkReminderSms").attr('checked') ? true : false,
            smsPhone: $("#smsPhone").val(),
            smsDaysBefore: $("#smsDaysBefore").val(),
            smsHour: $("#smsHour").val(),
            smsMinutes: $("#smsMinutes").val()
        }

        if (_mode == "operation" && ! $("#op_checkReminders").attr("checked")) {
            // в форме операций, если блок напоминаний скрыт
            params.mailEnabled = false;
            params.smsEnabled = false;
        }

        return params;
    }

    function setSettings(reminders) {
        if (_mode == "operation" && reminders.mailEnabled || reminders.smsEnabled) {
            // используем напоминалки в операциях
            $("#op_checkReminders").attr("checked", true);
            $("#tableReminders").show();

            $("#checkReminderMail").attr('checked', reminders.mailEnabled);
            $("#mailDaysBefore").val(reminders.mailDaysBefore)
            $("#mailHour").val(reminders.mailHour);
            $("#mailMinutes").val(reminders.mailMinutes);

            $("#checkReminderSms").attr('checked', reminders.smsEnabled);
            $("#smsPhone").val(reminders.smsPhone);
            $("#smsDaysBefore").val(reminders.smsDaysBefore);
            $("#smsHour").val(reminders.smsHour);
            $("#smsMinutes").val(reminders.smsMinutes);
        } else {
            $("#op_checkReminders").attr("checked", false);
            $("#tableReminders").hide();
        }

        return;
    }

    // параметр mode определяет, как используется виджет
    // по умолчанию mode = "operation"
    // если mode = "profile", то дополнительно
    // отображается настройка часового пояса

    function init(nodeSelector, model, mode) {
        if (!model) {
            return null;
        } else {
            _model = model;
        }

        _$node = $(nodeSelector);

        _mode = mode === undefined ? "operation" : mode;

        if (_mode == "profile") {
            // показываем подзаголовок и блок с телефоном
            $("#reminderOptions_profile").show();

            // отображаем часовой пояс
            $("#selTimeZoneOffset").val(_model.getTimeZone());

            // процедура сохранения новых настроек напоминаний
            $('#save_reminders').click( function() {
                var params = easyFinance.widgets.operationReminders.getSettings();
                params.timezone = $("#selTimeZoneOffset").val();

                easyFinance.models.user.saveRemindersDefaults(params, function(data) {
                    if (data.result) {
                        if (data.result.text) {
                            $.jGrowl(data.result.text, {theme: 'green'});
                        }
                    } else if (data.error) {
                        if (data.error.text) {
                            $.jGrowl(data.error.text, {theme: 'red'});
                        }
                    }
                });

                return false;
            });
        } else {
            // отображаем галочку использования напоминаний
            // Что делать если таких элементов на странице 2 как в профиле?
            $("#divUseReminders", _$node).show();
        }

        if (setDefaults() || _mode == "profile") {
            // показываем остальные опции
            $("#tableReminders", _$node).show();
        }

        // переключалка в форме операций
        $("#op_checkReminders", _$node).click(function() {
            $("#tableReminders", _$node)[( $( this ).attr('checked') ) ? "show" : "hide"]();
        });

        return this;
    }

    return {
        init: init,
        setDefaults: setDefaults,
        getSettings: getSettings,
        setSettings: setSettings
    }
}();
