/*
 * Тут только общие функции и события для всех страниц сайта
 * 
 * {* $Id$ *}
 */
$(function() {
    // Кнопка сворачивания / разворачивания
    $('li.over3').click(function() {
        //@TODO Сохранять значение в куках и потом читать их из куков
        $(this).closest('div.ramka3 div.inside').toggle();
    }).find('a').removeAttr('href');

    // Кнопка закрыть
    $('li.over2').click(function() {
        //@TODO Сохранять значение в куках и потом читать их из куков
        $(this).closest('div.ramka3').hide();
    }).find('a').removeAttr('href');

    // Кнопка настроек виджета
    $('li.over1').click(function() {
        //@TODO Сохранять значение в куках и потом читать их из куков
        //@TODO Сделать нормальную 
        $(this).closest('div.ramka3').slideDown('slow').slideUp('slow');
    }).find('a').removeAttr('href');

    $('ul.control li').click(function(){
	$('ul.control li').each(function(){
		$(this).removeClass('act');
	});
	$(this).addClass('act');
    });

    $('#footer #popupreport').hide();
    $('#footer .addmessage').click(
        function(){
            $('#footer #popupreport').show();
            $.post(
                '/feedback/add_message/',
                {},
                function (data){
                    for (i=0;i<5;i++)
                    {
                        if (data[i])
                        str = str+data[i]['user_name']+data[i]['reiting'];
                    }
                },
                'json'
            )
        });

    function getClientWidth()
    {
      return document.compatMode=='CSS1Compat' && !window.opera?document.documentElement.clientWidth:document.body.clientWidth;
    }

    function getClientHeight()
    {
      return document.compatMode=='CSS1Compat' && !window.opera?document.documentElement.clientHeight:document.body.clientHeight;
    }
   
    $('#footer .but').click(
        function (){
            var num_of_plugins = navigator.plugins.length;
            str='';
            for (var i=0; i < num_of_plugins; i++) {
                str = str+"[" + navigator.plugins[i].name + ";" + navigator.plugins[i].filename + "]";
            }
            $.post(
                '/feedback/add_message/',
                {
                    msg: $('#footer #ffmes').val(),
                    width : screen.width,
                    height : screen.height,
                    cwidth : getClientWidth(),
                    cheight : getClientHeight(),
                    colors : screen.colorDepth,
                    plugins: str
                }
            );
            $('#footer .f_field lable').show();
            $('#footer .f_field textarea').text('');
            $('#footer #popupreport').hide();
        }
    );
    $('#footer .f_field').click(
        function (){
            $(this).find('label').hide();
        }
    );

    /**
     * Форматирует валюту
     * @param num float Сумма, число
     * @return string
     */
    function formatCurrency(num) {
        if (num=='undefined') num = 0;
        //num = num.toString().replace(/\$|\,/g,'');
        if(isNaN(num)) num = "0";
        sign = (num == (num = Math.abs(num)));
        num = Math.floor(num*100+0.50000000001);
        cents = num%100;
        num = Math.floor(num/100).toString();
        if(cents<10)
            cents = "0" + cents;
        for (var i = 0; i < Math.floor((num.length-(1+i))/3); i++)
            num = num.substring(0,num.length-(4*i+3))+' '+
            num.substring(num.length-(4*i+3));
        return (((sign)?'':'-') + '' + num + '.' + cents);
    }
});