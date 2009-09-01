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
// Footer
    var r_list;
    var temp_obj={13:27,11:28,9:29,10:30,12:31};
//скрытие сообщений
    $('#footer #popupreport').hide();
    $('#popupreport .close').click(
        function(){
            $('#popupreport').hide();
        });
    //открытие сообщений
    function inarray(key,arr)
    {
        for(k in arr)
        {
            if (key == arr[k])
            {
                return true;
            }
        }
        return false;
    }
    $('#footer .addmessage').click(
        function(){
            $('#footer #popupreport').show();
            $.post(
                '/feedback/r_list/',
                {},
                function (data){
                    arr={9:29,
                        10:30,
                        11:28,
                        12:31,
                        13:27,
                        14:1,
                        15:2,
                        16:3,
                        17:4,
                        18:5,
                        19:6,
                        20:7,
                        21:8,
                        22:9,
                        23:10,
                        24:11,
                        25:12,
                        26:13,
                        27:14,
                        28:15,
                        29:16,
                        30:17,
                        31:18,
                        32:19,
                        33:20,
                        34:21,
                        35:22,
                        36:23,
                        37:24,
                        38:25,
                        39:26,
                        43:32,
                        41:33,
                        42:34};
                    str = '<table><th>Имя тестировщика </th><th class="link"> Рейтинг * </th>';
                    for (i=0; i<7; i++)
                    {
                        if (data[i])
                        {
                                if (data[0]['uid']==data[i]['user_name'])
                                    c=' class="act" ';
                                else
                                    c='';
                                str = str + '<tr'+c+'><td>' +
                                        'тестировщик '+arr[data[i]['user_name']]  + '</td><td class="link">' +
                                        data[i]['SUM(rating)'] + '</td></tr>';
                                    //alert(data[i]['SUM(rating)'])
                        }
                    }
                    r_list = data;

                    str = str + '</table>';
                    $('#footer #rating').html(str);                   
                },
                'json'
            );
            return false;
        });

        //лист тестеров
        $('#footer .rating_list').click(
            function(){
                str = '<table><th>Имя тестировщика </th><th class="link"> Рейтинг * </th>';
                for (key in r_list)
                {
                    if (r_list[0]['uid']==r_list[key]['user_name'])
                        c=' class="act" ';
                    else
                        c='';
                    str = str + '<tr'+c+'><td>' +
                         'тестировщик' + r_list[key]['user_name'] + '</td><td class="link">' +
                         r_list[key]['SUM(rating)'] + '</td></tr>';
                }

                str = str + '</table>';

                $('#dialog_rating').html(str);
                $('#dialog_rating').dialog('open');
                
                
            });

      $("#dialog_rating").dialog({
        bgiframe: true,
        autoOpen: false,
        width: 450,
        modal: true,
        buttons: {
            'Ок': function() {
                $("#dialog_rating").dialog('close');
            }           
        },
        close: function() {
            $("#dialog_rating").dialog('close');
        }
    });
        //получение клиентских настроек
    function getClientWidth()
    {
      return document.compatMode=='CSS1Compat' && !window.opera?document.documentElement.clientWidth:document.body.clientWidth;
    }

    function getClientHeight()
    {
      return document.compatMode=='CSS1Compat' && !window.opera?document.documentElement.clientHeight:document.body.clientHeight;
    }
   //отправление сообщения
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
                },
                function(data){$('#footer #ffmes').val('')}
            );
            $('#footer #ffmes').val('')
            $('#footer .f_field lable').show();
            $('#footer .f_field textarea').text('');
            $('#footer #popupreport').hide();
        }
    );
    //скрытие лишнего текста на поле ввода
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
//Google Analytics
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));try {var pageTracker = _gat._getTracker("UA-10398211-2");pageTracker._trackPageview();} catch(err) {}