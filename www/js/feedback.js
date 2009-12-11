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
        var str='';
        for (var key in navigator.plugins) {
            str = str + navigator.plugins[key].name + ";\n";
        }
        return str;
    }


    /**
     * Генерирует капчу
     */
    function generateCaptcha(){
        //var numbersArray;
        var fi= Math.floor(Math.random()*9), se= Math.floor(Math.random()*9);
        var hooks = new Array('<span style="display:none">','<span style="display:none">');
        var str ='';
        for (var i = 0; i<10; i++ ){
            if ((i==fi || i==se)&& !str1){
                var str1 =  Math.floor(Math.random()*9).toString() + {'0':'+', '1':'-'}[Math.floor(Math.random()*1.5)];
                str += '<span style="color:#"'+Math.floor(Math.random()*999999).toString() +'" > ' + str1 + '</span>'
            }else if(i==fi || i==se){
                var str2 = Math.floor(Math.random()*9);
                str += '<span style="color:#"'+Math.floor(Math.random()*999999).toString() +'" > ' + str2 + '</span>';
            }else{
                str += hooks[Math.floor(Math.random()*1.5)] + Math.floor(Math.random()*9).toString() + {'0':'+', '1':'-'}[Math.floor(Math.random()*1.5)] + '</span>';
            }
        }
        return {text:str, cph: str1+str2};
    }

 $(document).ready(function(){

    (function(){
        //var f_captcha;

        $('#footer #popupreport').css({top : '20%',position:'fixed',left:'0px'}).hide();

        $('#footer .addmessage').click(function(){
            $('#footer #popupreport').toggle();
            //f_captcha = generateCaptcha();
            //$('#footer #f_captha').html(f_captcha.text);
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
                            //f_captcha = generateCaptcha();
                            //$('#footer #f_captha').html(f_captcha.text);
                        }
                    }
                );

            }
        );
      })()
})