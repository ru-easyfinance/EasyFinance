var feedback = (function(selector) {
    var container,
        dialog, // i hate jquery.ui and strive for destroy it
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
            frm[0][key] = displayMods[key]
        }
        frm[0]["plugins"] = getClientPlugins();

        utils.ajaxForm(frm, false, false, false, 'json');

        return false;
    }

    function onTabs(e) {
       if (tabs.tabs('option', 'selected') == 0) {
           tabs.tabs('select', 1);
       }
       else {
           tabs.tabs('select', 0)
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



//
// $(document).ready(function(){
//    var dialogWrapper = $('#sendFeedBack');
//
//    function onSend() {
//        var feedback = getClientDisplayMods();
//        feedback.plugins = getClientPlugins();
//
//        var title = dialogWrapper.find('.js-feedback-title');
//        feedback.title = $.trim(title.val())
//
//        if (!feedback.title) {
//            $.jGrowl('Введите тему отзыва!', {theme: 'red'});
//            return false;
//        }
//
//        var msg = dialogWrapper.find('textarea');
//        feedback.msg = $.trim(msg.val());
//
//        if (!feedback.msg) {
//            $.jGrowl('Введите отзыв!', {theme: 'red'});
//            return false;
//        }
//
//        var email = dialogWrapper.find('.js-feedback-mail');
//
//        if (email.length) {
//            feedback.email = $.trim(email.val())
//
//            if (!feedback.email) {
//                $.jGrowl('Введите e-mail!', {theme: 'red'});
//                return false;
//            }
//        }
//
//        // все проверили, можно отправлять
//
//        $.jGrowl('Подождите, Ваше сообщение отправляется&hellip;', {theme: 'green'});
//
//        $.post(
//            '/feedback/add_message/?responseMode=json',
//            feedback,
//            function(data){
//                if (data.error){
//                    if (data.error.text) {
//                        $.jGrowl(data.error.text, {theme: 'red'});
//                    }
//                }
//                else if (data.result) {
//                    // #1201 очищаем поля темы и сообщения
//                    dialogWrapper.dialog('close');
//
//                    if (data.result.text) {
//                        $.jGrowl(data.result.text, {theme: 'green'});
//                    }
//                    dialogWrapper.find('input[type="text"], textarea').val('')
//                }
//            }, "json"
//        );
//        return true;
//    }
//
//    dialogWrapper.dialog({
//        autoOpen: false,
//        title: "Оставить отзыв",
//        modal: true,
//        width: 'auto',
//        buttons: {
//            "Отмена": function() {
//                dialogWrapper.dialog('close');
//            },
//            "Отправить": onSend
//        }
//    });
//
//    $('#btnFeedback, #footerAddMessage, #linkMainMenuFeedback').click(function(){
//        dialogWrapper.dialog('open');
//        dialogWrapper.find('input')[0].focus();
//        if ($.browser.opera) {
//            dialogWrapper.find('textarea').css({display: 'block'});
//        }
//    });
//});
