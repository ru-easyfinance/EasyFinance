var feedback = (function(selector) {
    var container,
        dialog,
        that = this,
        frm,
        tabs,
        tabsSwitches;

    // строковые ресурсы
    var messages = {
        onSending: 'Заявка отправляется&hellip;',
        onSendOk: 'Спасибо, ваша заявка отправлена. На ваш e-mail адрес придет уведомление.',
        onSendError: 'Не удалось отправить заявку из-за технического сбоя. Попробуйте еще раз через несколько минут.',
        onInvalidate: 'Все поля обязательны для заполнения.'
    }

    // Получает сведения о графических данных браузера и монитора клиента
    function getClientDisplayMods() {
        var cwidth = 0,
            cheight = 0;

        var width = screen.width, height = screen.height;
        var color = screen.colorDepth;
        if ((document.compatMode == 'CSS1Compat') && !window.opera) {
            cwidth = document.documentElement.clientWidth;
            cheight = document.documentElement.clientHeight;
        }
        else {
            cwidth = document.body.clientWidth;
            cheight = document.body.clientHeight;
        }

        return {
            width : width,
            height : height,
            cwidth : cwidth,
            cheight : cheight,
            colors : color
        }
    }

     // Получает перечень клиентских плагинов
    function getClientPlugins() { // NB: IE не поддерживает navigator.plugins
        var str = '';
        try {
            for (var key in navigator.plugins) {
                str = str + navigator.plugins[key].name + ";\n";
            }
        }
        catch (err) {
            str = "Can't detect plugins! (IE7/IE8)";
        }

        return str;
    }

    function onSubmit(e) {
        e.preventDefault();

        // валидация
        var emptyFields = frm
            .find('input[type="text"], textarea')
            .filter(function(index) {
                return $(this).val() == '';
            });
        if (emptyFields.length) {
            utils.notifyUser(messages.onInvalidate)
            return false;
        }

        // дополнительные сведения о браузере
        var displayMods = getClientDisplayMods();
        for (var key in displayMods) {
            frm[0][key].value = displayMods[key];
        }
        frm[0]["plugins"].value = getClientPlugins();

        // отправка
        utils.notifyUser(messages.onSending, 'process')
        utils.ajaxForm(
            frm,
            function() {dialog.dialog('close');utils.notifyUser(messages.onSendOk, 'ok');},
            function() {utils.notifyUser(messages.onSendError)},
            false,
            'json'
        );

        return false;
    }

    function init() {
        container = $(selector || '.js-widget-feedback');

        dialog = container.find('.js-feedback-dialogue');

        if ('user' in res && 'name' in res.user) {
            container.find('input[name="email"]').closest('.b-row').remove();
        }

        frm = dialog.find('form');
        frm.bind('submit', onSubmit)

        $('#btnFeedback, #footerAddMessage, #linkMainMenuFeedback').bind('click', function() {
            dialog.prompt(utils.getParams(dialog));
            dialog.dialog('open');
            if ($.browser.opera) {
                dialog.find('textarea').css('display', 'none');
                window.setTimeout(function(){
                    dialog.find('textarea').css('display', 'block');
                }, 10)
            }
        });

        container.bind('widget.ok', function() {
            frm.trigger('submit');
        });

        container.bind('widget.cancel', function() {
            dialog.dialog('close');
        });
    }

    $(init);
})();