/**
 * Получает сведения о графических данных браузера и монитора клиента
 * @return obj
 */
function getClientDisplayMods(){
    var cwidth = 0, cheight = 0;
    var width = screen.width, height = screen.height;
    var color = screen.colorDepth;
    if ('CSS1Compat' && !window.opera){
        cwidth = document.documentElement.clientWidth;
        cheight = document.documentElement.clientHeight;
    }else{
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
/**
 * Получает перечень клиентских плагинов
 * @return str
 */
function getClientPlugins(){
    var str = '';
    for (var key in navigator.plugins) {
        str = str + navigator.plugins[key].name + ";\n";
    }
    return str;
}

 $(document).ready(function(){

    (function(){
        var noClick = false;

        $('#footer #popupreport').hide();

        $('#footer .addmessage').click(function(){
            $('#footer #popupreport').toggle();
        });

        $('#popupreport .close').click(function(){
            $('#popupreport').hide();
        });

        //скрытие лишнего текста на поле ввода
        $('#footer .f_field.ffmes').click(function (){
            $(this).find('label').hide();
        });
        //отправление сообщения
        $('#footer .but').click(function (){
            if (noClick){
                return false;
            }
            noClick = true;
            $.jGrowl('Подождите!<br/>Ваше сообщение отправляется!', {theme: 'green'});
            var feedback = getClientDisplayMods();
            feedback.plugins = getClientPlugins();
            feedback.msg = $('#footer #ffmes').val();
            if ($('#footer #fmail').length){
                feedback.email = $('#footer #fmail').val();
                //feedback.captcha = f_captcha.cph;
                //feedback.answer = $('#footer #fcaptha').val();
                if (!$('#footer #fmail').val()){
                    $.jGrowl('Введите адрес вашей почты', {theme: 'red'})
                    return;
                }
            }
            $.post(
                '/feedback/add_message/',
                feedback,
                function(data){
                    if (!data.error){
                        $('#footer input').val('');
                        $('#footer .f_field lable').show();
                        $('#footer .f_field textarea').val('');
                        $('#footer #popupreport').hide();
                        $.jGrowl('Спасибо!<br/>Ваше сообщение отправлено!', {theme: 'green'});
                    }else{
                        $.jGrowl(data.error.text, {theme: 'red'});
                    }
                    noClick = false;
                }
            );
        });
      })();
});