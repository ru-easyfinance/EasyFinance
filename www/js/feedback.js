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
    // #1324. грёбаный IE не поддерживает navigator.plugins
    
    var str = '';
    try {
        for (var key in navigator.plugins) {
            str = str + navigator.plugins[key].name + ";\n";
        }
    } catch (err) {
        str = 'Cannot detect plugins! (IE7/IE8)';
    }

    return str;
}

 $(document).ready(function(){

    (function(){
        var noClick = false;

        $('#footer #popupreport').hide();

        $('#btnFeedback, #footerAddMessage').click(function(){
            $('#popupreport').show();
        });

        $('#popupreport .close').click(function(){
            $('#popupreport').hide();
        });

        $('#popupreport button, #popupreport input#fmail').keypress(function(e){
            if (e.keyCode == 13){
                $('#footer .but').click();
            }
        });

        //скрытие лишнего текста на поле ввода
        $('#footer .f_field #ffmes').focus(function (){
            $(this).closest('div').find('label').hide();
        });
        //отправление сообщения
        $('#footer #sendFeedback,#footer #sendFeedback img').click(function (){            
            if (noClick){
                return false;
            }

            var feedback = getClientDisplayMods();
            feedback.plugins = getClientPlugins();
            
            // Проверяем данные, см. тикет #1127
            if (!$('#footer #ftheme').val() || $('#footer #ftheme').val() == ''){
                $.jGrowl('Введите тему отзыва!', {theme: 'red'})
                noClick = false;
                return;
            }

            if ($('#footer #fmail').length){
                feedback.email = $('#footer #fmail').val();
                if (!$('#footer #fmail').val()){
                    $.jGrowl('Введите адрес вашей почты!', {theme: 'red'})
                    noClick = false;
                    return;
                }
            }

            // см. тикет #1127
            // вместо блокировки отправки и сообщения
            // просто закрываем окно в случае успешной отправки
            //
            //noClick = true;
            $.jGrowl('Подождите!<br/>Ваше сообщение отправляется!', {theme: 'green'});
            $('#popupreport').hide();
            
            feedback.msg = $('#footer #ffmes').val();
            feedback.title = $('#footer #ftheme').val();
			
            $.post(
                '/feedback/add_message/?responseMode=json',
                feedback,
                function(data){
                    noClick = false;
					
                    if (data.error){
                        if (data.error.text) {
                            $.jGrowl(data.error.text, {theme: 'red'});
                        }
                    } else if (data.result){
						// #1201 очищаем поля темы и сообщения
						$('#footer #ffmes').val('');
						$('#footer #ftheme').val('');
                        $('#footer .f_field label').show();
                        $('#footer #popupreport').hide();

                        if (data.result.text) {
                            $.jGrowl(data.result.text, {theme: 'green'});
                        }
                    }
                }, "json"
			);
			
			return false;
        });
      })();
});