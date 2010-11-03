var feedback = (function(selector) {
    var container,
        dialog,
        dlgButtons,
        that = this,
        frm,
        tabs,
        tabsSwitches;



    // Получает сведения о графических данных браузера и монитора клиента
    function getClientDisplayMods() {
        var cwidth = 0,
            cheight = 0;

        var width = screen.width, height = screen.height;
        var color = screen.colorDepth;
        if ('CSS1Compat' && !window.opera) {
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

        var displayMods = getClientDisplayMods();
        for (var key in displayMods) {
            frm[0][key].value = displayMods[key];
        }
        frm[0]["plugins"].value = getClientPlugins();

        utils.ajaxForm(frm, function() { dialog.dialog('close') }, false, false, 'json');

        return false;
    }

    function onTabs(e) {
       if (tabs.tabs('option', 'selected') == 0) {
           tabs.tabs('select', 1);
           dialog.dialog('option', 'buttons', dlgButtons);
       }
       else {
           tabs.tabs('select', 0)
           dialog.dialog('option', 'buttons', {});
       }
       dialog.dialog('option', 'title', $(e.target).attr('title'));
    }


    function init() {
        container = $(selector || '.js-widget-feedback');
        utils.initControls(container);

        dialog = container.find('.js-feedback-dialogue');

        if ('user' in res && 'name' in res.user) {
            container.find('input[name="email"]').closest('.b-row').remove();
        }

        frm = dialog.find('form');
        frm.bind('submit', onSubmit)

        $('#btnFeedback, #footerAddMessage, #linkMainMenuFeedback').bind('click', function() {
            document.write = function(){};
            dialog.prompt(utils.getParams(dialog));
            dialog.dialog('open');

            dlgButtons = dialog.dialog('option', 'buttons'); // сохраняем кнопки
            dialog.dialog('option', 'buttons', {});
        });

        container.bind('widget.ok', function() {
            frm.trigger('submit');
        });

        container.bind('widget.cancel', function() {
            dialog.dialog('close');
        });

        tabs = dialog.find('.js-control-tabs');
        tabsSwitches = dialog.find('.js-switchtabs');
        tabsSwitches.bind('click', onTabs);
    }

    $(init);
})();