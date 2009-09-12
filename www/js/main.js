/*
 * Тут только общие функции и события для всех страниц сайта
 * 
 * {* $Id$ *}
 */
//conf href to modul
function get_array_key($arr, $val)
{
    $ret = -1;
    for(key in $arr)
    {
        if ($val == $arr[key])
        {
            $ret = key;
            break;
        }
    }
    return $ret;
}

aPath=['//',
    '/about/',
    '/accounts/',
    '/admin/',
    '/blog/',
    '/budget/',
    '/calendar/',
    '/category/',
    '/experts/',
    '/feedback/',
    '/forum/',
    '/info/',
    '/login/',
    '/logout/',
    '/mail/',
    '/operation/',
    '/periodic/',
    '/profile/',
    '/registration/',
    '/report/',
    '/review/',
    '/rules/',
    '/security/',
    '/start/',
    '/tags/',
    '/targets/',
    '/welcome/',
    '/template/']//данный контроллер можно использовать как системный))
href = location.pathname;
href = href.toLowerCase() + '/';
b=0;
nhref=new String;
for(i=0;i<href.length;i++)
{
    if (href[i] == '/')
            b++;
    nhref = nhref + href[i];
    if(b == 2)
        break;
}
var Current_module = get_array_key(aPath, nhref);
var Connected_functional = {operation:[2,5,6,7,8,11,15,16,19,25],
                            menu:[2,5,6,7,8,11,15,16,17,19,25]};


$(function(document) {
    
    // *** Функции ***
    
    //открытие сообщений
    function inarray(key,arr) {
        for(k in arr) {
            if (key == arr[k]) {
                return true;
            }
        }
        return false;
    }

    //получение клиентских настроек
    function getClientWidth() {
      return document.compatMode=='CSS1Compat' && !window.opera?document.documentElement.clientWidth:document.body.clientWidth;
    }

    function getClientHeight() {
      return document.compatMode=='CSS1Compat' && !window.opera?document.documentElement.clientHeight:document.body.clientHeight;
    }

    /**
     * Получает список тегов
     */
    function getTags() {
        $('a#tags').click(function(){
            $('.tags_could').dialog({
                close: function(event, ui){$(this).dialog( "destroy" )}
            }).dialog("open");
            $('.tags_could li').show();
        });
		$('.tags input').keyup(function(){
                    $('.tags_could li').show();

		})
                $('.tags_could li').live('click',function(){
                    txt=$('.tags input').val()+$(this).text()+', ';
                    $('.tags input').val(txt);
                    $('.tags_could').dialog("close");
                });
        // Загружаем теги
        $.get('/tags/getCloudTags/', '', function(data) {
            str = '<ul>';
            for (key in data) {
                k = data[key]['COUNT(name)']/data[0]['COUNT(name)'];
                n = Math.floor(k*5);
                str = str + '<li class="tag'+n+'"><a>'+data[key]['name']+'</a></li>';
            }
            $('.tags_could').html(str+'</ul>');
            
            $('.tags_could li').hide();
        }, 'json');
    }

    /**
     * Добавляет новую операцию
     * @return void
     */
    function saveOperation() {
        if (!validateForm()){
            return false;
        }
        $.post(($('form').attr('action')), {
            id        : $('#id').val(),
            type      : $('#type').val(),
            account   : $('#account').val(),
            category  : $('#category').val(),
            date      : $('#date').val(),
            comment   : $('#comment').val(),
            amount    : $('#amount').val(),
            toAccount : $('#AccountForTransfer').val(),
            currency  : $('#currency').val(),
            target    : $('#target').val(),
            close     : $('#close:checked').length,
            tags      : $('#tags').val()
        }, function(data, textStatus){
            for (var v in data) {
                //@FIXME Дописать обработку ошибок и подсветку полей с ошибками
                alert('Ошибка в ' + v);
            }
            // В случае успешного добавления, закрываем диалог и обновляем календарь
            if (data.length == 0) {
                clearForm();
            }
           // data could be xmlDoc, jsonObj, html, text, etc...
           //this; // the options for this ajax request
           // textStatus can be one of:
           //   "timeout"
           //   "error"
           //   "notmodified"
           //   "success"
           //   "parsererror"
        }, 'json');
        return true;
    }

    /**
     * Проверяет валидность введённых данных
     */
    function validateForm() {
        $error = '';
        if (isNaN(parseFloat($('#amount').val()))){
            alert('Вы ввели неверное значение в поле "сумма"!');
            return false;
        }

        if ($('#type') == 4) {
            //@FIXME Написать обновление финцелей
            amount = parseFloat($("#target_sel option:selected").attr("amount")); $("#amount").text(amount);
            amount_done = parseFloat($("#target_sel option:selected").attr("amount_done")); $("#amount_done").text(amount_done);
            if ((amount_done + parseFloat($("#amount").val())) >= amount) {
                if (confirm('Закрыть финансовую цель?')) {
                    $("#close").attr("checked","checked");
                }
            }
        }
        return true;
    }

    /**
     * Очищает форму
     * @return void
     */
    function clearForm() {
        $('#type,#category,#target').val(0);
        $('#amount,#AccountForTransfer,#comment,#tags,#date').val('');
        $('#amount_target,#amount_done,#forecast_done,#percent_done').text('');
        $('#close').removeAttr('checked');
        $('form').attr('action','/operation/add/');
        $('#type').change();
    }

    /**
     * При переводе со счёта на счёт, проверяем валюты
     * @return void
     */
    function changeAccountForTransfer() {
        if ($('#type :selected').val() == 2 &&
            $('#account :selected').attr('currency') != $('#AccountForTransfer :selected').attr('currency')) {
                $('#operationTransferCurrency').show();
                $.post('/operation/get_currency/', {
                        SourceId : $("#account").val(),
                        TargetId : $("#AccountForTransfer").val()
                    }, function(data){
                        $('#operationTransferCurrency :first-child').html('Курс <b>'+
                            $('#account :selected').attr('abbr')+'</b> к <b>'+$('#AccountForTransfer :selected').attr('abbr')+'</b>');
                        $('#currency').val(data);
                    }, 'json'
                );
        } else {
            $('#operationTransferCurrency').hide();
        }
    }

    /**
     * При изменении типа операции
     */
    function changeTypeOperation() {
        // Расход или Доход
        if ($('#type').val() == 0 || $('#type').val() == 1) {
            $("#category_fields,#tags_fields").show();
            $("#target_fields,#transfer_fields").hide();
        //Перевод со счёта
        } else if ($('#type').val() == 2) {
            $("#category_fields,#target_fields").hide();
            $("#tags_fields,#transfer_fields").show();
            changeAccountForTransfer();
        //Перевод на финансовую цель
        } else if ($('#type').val() == 4) {
            $("#target_fields").show();
            $("#tags_fields,#transfer_fields,#category_fields").hide();
            $('#target').change();
        }
    }
    
    // Выводим окно с операциями, если у нас пользователь авторизирован
    if (inarray(Current_module, Connected_functional.operation)){//////////////////////////////////
        // Автоматически подгружаем теги
        getTags();

        $('#btn_Save').click(function(){ saveOperation(); })
        $('#btn_Cancel').click(function(){ clearForm() });

        $("#addoperation_but").click(function(){
            $(this).toggleClass("act");
            if($(this).hasClass("act")){
                $(".addoperation").show();
            } else {
                $(".addoperation").hide();
            }
        });
        $('#amount').calculator({
            layout: [$.calculator.CLOSE+$.calculator.ERASE+$.calculator.USE,
                    'MR_7_8_9_-' + $.calculator.UNDO,
                    'MS_4_5_6_*' + $.calculator.PERCENT ,
                    'M+_1_2_3_/' + $.calculator.HALF_SPACE,
                    'MC_0_.' + $.calculator.PLUS_MINUS +'_+'+ $.calculator.EQUALS],
            showOn: 'focus' //opbutton
        });

        $.datepicker.setDefaults($.extend({dateFormat: 'dd.mm.yy'}, $.datepicker.regional['ru']));
        $("#date").datepicker();


        $('#amount,#currency').change(function(){
            if ($('#type').val() == 2) {
                //@TODO Дописать округление
                var result = Math.round($('#amount').val() / $('#currency').val());
                if (!isNaN(result) && result != 'Infinity') {
                    $("#convertSumCurrency").html("конвертация: "+result);
                }
            }
        });

        $('#account').change(function(){ changeAccountForTransfer(); });
        $('#AccountForTransfer').change( function(){ changeAccountForTransfer(); });
        $('#type').change(function(){ changeTypeOperation('add'); });
        $('#target').change(function(){
            $("span.currency").each(function(){
                $(this).text(" "+$("#target :selected").attr("currency"));
            });
            $("#amount_done").text(formatCurrency($("#target :selected").attr("amount_done")));
            $("#amount_target").text(formatCurrency($("#target :selected").attr("amount")));
            $("#percent_done").text(formatCurrency($("#target :selected").attr("percent_done")));
            $("#forecast_done").text(formatCurrency($("#target_sel :selected").attr("forecast_done")));
        });
    }
    if(inarray(Current_module, Connected_functional.menu)){//////////////////////////////////
        //верхнее меню
        head = $('#menumain').attr('value');
        if (!head)
            head = '/';
        $('#menumain li').attr('class','');
        $('#menumain li').each(function(){
            if ($(this).find('a').attr('href')==head)
                $(this).attr('class','act');
        });

        // Динамическое меню
        var page_mid = $('.menu3 span').closest('li').attr('id');
        var act_id = page_mid;
        var submenu = {
            'm1':[''],
            'm2':[  '<a href="/accounts/">Счета</a>',
                    '<a href="/operation/">Журнал операций</a>',
                    '<a href="/category/">Категории</a>'],
            'm3':[  /*'<a href="/budget/">Бюджет</a>',
                    '<a href="/targets/">Финансовые цели</a>'*/],
            'm4':[''],
            'm5':[  '<a href="/calendar/">Календарь</a>',
                    '<a href="/periodic/">Регулярные транзакции</a>'],
            'm6':['']
        };
        //@TODO Цикл по submenu и если находит текущую таблицу, то окружает её SPAN

        if ($('.menu4').length == 0) {
            $('div.cct').after('<ul class="menu4" >&nbsp</ul>');
        }

        $('.menu3 span').live('mouseover',function(){
            txt = $(this).text();
            $(this).hide().closest('li').append('<a class="span">'+txt+'</a>');
        })
        $('.mid, .ccb, #footer, #header, #menumain').mouseover(function(){
            $('.menu3 li').removeClass('act');
            txt = $('.menu3 span').text();
            $('.menu3 span').closest('li').html('<span>'+txt+'</span><a class="span">'+txt+'</a>');
            $('.menu3 span').hide().closest('li').addClass('act');
            sm = submenu[page_mid];
            str='';
            l = sm.length;
            k = 0;
            for(k=0; k<l; k++) {
                str = str+'<li>'+sm[k]+'</li>';
            }
            $('ul.menu4 ').html(str);
        })
        $('.menu3 li').live('mouseover',function(){

            act_id = $(this).attr('id');
            if(act_id != page_mid)
            {
                $('.menu3 .span').closest('li').find('span').show();
                $('.menu3 .span').remove();
            }
            $('.menu3 li').removeClass('act');
            $(this).addClass('act');
            //$('menu3') создадим субменю
            sm = submenu[act_id]?submenu[act_id]:'';
            str='';
            l = sm.length;
            k = 0;
            for(k=0; k<l; k++)
            {
                str = str+'<li>'+sm[k]+'</li>';
            }
            $('ul.menu4 ').html(str);
            return false;
        })
    }
    

    // Кнопка сворачивания / разворачивания
    $('li.over3').click(function() {
        //@TODO Сохранять значение в куках и потом читать их из куков
        $(this).closest('div.ramka3').find('div.inside').toggle();
    }).find('a').removeAttr('href');

    // Кнопка закрыть
    $('li.over2').hide();
    $('li.over1').hide();
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

    //скрытие сообщений
    $('#footer #popupreport').hide();
    $('#popupreport .close').click(
        function(){
            $('#popupreport').hide();
        });


    $('#footer .addmessage').click(
        function(){
            $('#footer #popupreport').show();
            $.post(
                '/feedback/r_list/',
                {},
                function (data) {
                    arr={ 9:29,
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
                    for (i=0; i<5; i++)
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
                                    data[i]['user_name'] = arr[data[i]['user_name']];
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


    });

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
///////////////////////////////////////////////////////////////////////////////
// left
// nav bar
/*$(document).ready(function(){
$('.navigation  li ul').hide()
$('.navigation li.act ul').show()
$('.navigation  li span').click(function(){
    $('.navigation  li span').closest('li').removeClass('act');
    $(this).closest('li').addClass('act');
    $('.navigation  li ul').hide()
    $('.navigation li.act ul').show()
})
// tags
$.get('/tags/getCloudTags/', '', function(data) {
            str = '<div class="title">\n\
                        <h2>Теги</h2>\n\
                        <a title="Добавить" class="add">Добавить</a>\n\
                    </div>\n\
                    <ul>';
            for (key in data)
            {
                str = str + '<li><a>'+data[key]['name']+'</a></li>';
            }
            $('.tags_list').html(str+'</ul>');

        }, 'json');
        $('.tags_list li a').live('click', function(){
            $('.edit_tag').dialog('open');
            $('.edit_tag input').val($(this).text());
            $('.edit_tag').dialog({
                width: 260,
                minHeight: 50,
                buttons: {
                    'Сохранить': function() {
                        if($('input#tag').val())
                        $.post('/tags/edit/', $('.edit_tag input'),function(data){$('.edit_tag').dialog('close');},'json');
                    },
                    'Удалить': function() {
                        $.post('/tags/del/', $('.edit_tag input'),function(data){$('.edit_tag').dialog('close');},'json');
                    }

            }});
  
        });
        $('.tags_list .add').live('click', function(){
            $('.edit_tag').show();
            $('.edit_tag').dialog('open');
            $('.edit_tag input').val($(this).text());
            $('.edit_tag').dialog({
                width: 260,
                minHeight: 50,
                buttons: {
                    'Сохранить': function() {
                        if($('input#tag').val())
                        {
                            $.post('/tags/add/', $('.edit_tag input'),function(data){$('.edit_tag').dialog('close');},'json');
                            $('.edit_tag').dialog('close');
                        }
                    }

            }});

        });
//accounts
g_types = [0,0,0,0,0,0,1,2,2,2,3,3,3,3,4,0];//@todo Жуткий масив привязки типов к группам
g_name = ['Деньги','Долги мне','Мои долги','Инвестиции','Имущество'];//названия групп
var arr = ['','','','',''];//содержимое каждой группы
var summ = [0,0,0,0,0];// сумма средств по каждой группе
var val = {};//сумма средств по каждой используемой валюте
        $.post('/accounts/accountslist/',
            {},
            function(data){
                len = data.length;
                div = "<div class='cont'>&nbsp;<ul>\n\
                        <li class='edit'><a></a></li>\n\
                        <li class='del'><a></a></li>\n\
                      </ul></div>";

                $('#operation_list').empty();
                for (key in data )
                {
                    i = g_types[data[key]['type']];
                    str = '<li><a>';
                    str = str + '<div style="display:none" class="type" value="'+data[key]['type']+'" />';
                    str = str + '<div style="display:none" class="id" value="'+data[key]['id']+'" />';
                    str = str + '<span>'+data[key]['fields']['name']+'</span>';
                    str = str + '<b>'+formatCurrency(data[key]['fields']['total_balance']);
                    str = str + data[key]['cur']+ '</b>'+'</a></li>';
                    summ[i] = summ[i]+data[key]['def_cur'];
                    if (!val[data[key]['cur']]) {
                        val[data[key]['cur']]=0;
                    }
                    val[data[key]['cur']] = parseFloat( val[data[key]['cur']] )
                        + parseFloat(data[key]['fields']['total_balance']);
                    
                    arr[i] = arr[i]+str;
                }
                total = 0;
                for(key in arr)
                {
                    total = total+(parseInt(summ[key]*100))/100;
                    s='<ul>'+arr[key]+'</ul>';
                    if (arr[key])
                        $('.accounts #'+key).html(s);
                }
                /////////////////////формирование итогового поля//////////////////////
                for(key in val)
                {
                    str = str+'<tr><td>'+formatCurrency(val[key])+'</td><td>'+key+'</td></tr>';
                }
                str = str+'<tr><td><b>Итого:</b>  '+formatCurrency(total)+'</td><td> руб.</td></tr>';
                str = str + '</table>';
                 $('#operation_list').append(str);
                ////////////////////////////////////////////////////////////////


                $('.item td').hide();
                $('.item td.name').show();
                $('.item td.cur').show().css('width','50px');
                //$('.item td.cat').show();
                $('.item td.def_cur').show();
                //$('.item td.special').show();
                //$('.item td.description').show();
                $('.item td.total_balance').show().css('text-align','right').css('padding-right','0');
                $('.item td.mark').show();
            },
            'json'
        );
});

*/

//Google Analytics
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));try {var pageTracker = _gat._getTracker("UA-10398211-2");pageTracker._trackPageview();} catch(err) {}
