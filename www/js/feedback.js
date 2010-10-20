var feedback = (function(selector) {
    var container;



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


    function init() {
        container = $(selector || '.js-widget-feedback');
        utils.initControls(container);
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
