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
var pathName = nhref;
var Current_module = get_array_key(aPath, nhref);
var Connected_functional = {operation:[2,5,6,7,8,11,15,16,19,25],
                            menu:[2,5,6,7,8,11,15,16,17,19,25]};


$(document).ready(function() {
    
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
            amount = parseFloat($("#target_sel option:selected").attr("amount"));$("#amount").text(amount);
            amount_done = parseFloat($("#target_sel option:selected").attr("amount_done"));$("#amount_done").text(amount_done);
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













     /**
     * Получает список тегов
     */
    function op_getTags() {
        $('a#op_tags').click(function(){
            $('.op_tags_could').dialog({
                close: function(event, ui){$(this).dialog( "destroy" )}
            }).dialog("open");
            $('.op_tags_could li').show();
        });
		$('.op_tags input').keyup(function(){
                    $('.op_tags_could li').show();

		})
                $('.op_tags_could li').live('click',function(){
                    txt=$('.op_tags input').val()+$(this).text()+', ';
                    $('.op_tags input').val(txt);
                    $('.op_tags_could').dialog("close");
                });
        // Загружаем теги
        $.get('/tags/getCloudTags/', '', function(data) {
            str = '<ul>';
            for (key in data) {
                k = data[key]['COUNT(name)']/data[0]['COUNT(name)'];
                n = Math.floor(k*5);
                str = str + '<li class="tag'+n+'"><a>'+data[key]['name']+'</a></li>';
            }
            $('.op_tags_could').html(str+'</ul>');

            $('.op_tags_could li').hide();
        }, 'json');
    }

    /**
     * Добавляет новую операцию
     * @return void
     */
    function op_saveOperation() {
        if (!op_validateForm()){
            return false;
        }
        $.post(($('form').attr('action')), {
            id        : $('#op_id').val(),
            type      : $('#op_type').val(),
            account   : $('#op_account').val(),
            category  : $('#op_category').val(),
            date      : $('#op_date').val(),
            comment   : $('#op_comment').val(),
            amount    : $('#op_amount').val(),
            toAccount : $('#op_AccountForTransfer').val(),
            currency  : $('#op_currency').val(),
            target    : $('#op_target').val(),
            close     : $('#op_close:checked').length,
            tags      : $('#op_tags').val()
        }, function(data, textStatus){
            for (var v in data) {
                //@FIXME Дописать обработку ошибок и подсветку полей с ошибками
                alert('Ошибка в ' + v);
            }
            // В случае успешного добавления, закрываем диалог и обновляем календарь
            if (data.length == 0) {
                op_clearForm();
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
    function op_validateForm() {
        $error = '';
        if (isNaN(parseFloat($('#op_amount').val()))){
            alert('Вы ввели неверное значение в поле "сумма"!');
            return false;
        }

        if ($('#op_type') == 4) {
            //@FIXME Написать обновление финцелей
            amount = parseFloat($("#op_target_sel option:selected").attr("amount"));$("#op_amount").text(amount);
            amount_done = parseFloat($("#op_target_sel option:selected").attr("amount_done"));$("#op_amount_done").text(amount_done);
            if ((amount_done + parseFloat($("#op_amount").val())) >= amount) {
                if (confirm('Закрыть финансовую цель?')) {
                    $("#op_close").attr("checked","checked");
                }
            }
        }
        return true;
    }

    /**
     * Очищает форму
     * @return void
     */
    function op_clearForm() {
        $('#op_type,#op_category,#op_target').val(0);
        $('#op_amount,#op_AccountForTransfer,#op_comment,#op_tags,#op_date').val('');
        $('#op_amount_target,#op_amount_done,#op_forecast_done,#op_percent_done').text('');
        $('#op_close').removeAttr('checked');
        $('form').attr('action','/operation/add/');
        $('#op_type').change();
    }

    /**
     * При переводе со счёта на счёт, проверяем валюты
     * @return void
     */
    function op_changeAccountForTransfer() {
        if ($('#op_type :selected').val() == 2 &&
            $('#op_account :selected').attr('currency') != $('#op_AccountForTransfer :selected').attr('currency')) {
                $('#op_operationTransferCurrency').show();
                $.post('/operation/get_currency/', {
                        SourceId : $("#op_account").val(),
                        TargetId : $("#op_AccountForTransfer").val()
                    }, function(data){
                        $('#op_operationTransferCurrency :first-child').html('Курс <b>'+
                            $('#op_account :selected').attr('abbr')+'</b> к <b>'+$('#op_AccountForTransfer :selected').attr('abbr')+'</b>');
                        $('#op_currency').val(data);
                    }, 'json'
                );
        } else {
            $('#op_operationTransferCurrency').hide();
        }
    }

    /**
     * При изменении типа операции
     */
    function op_changeTypeOperation() {
        // Расход или Доход
        if ($('#op_type').val() == 0 || $('#op_type').val() == 1) {
            $("#op_category_fields,#op_tags_fields").show();
            $("#op_target_fields,#op_transfer_fields").hide();
        //Перевод со счёта
        } else if ($('#op_type').val() == 2) {
            $("#op_category_fields,#op_target_fields").hide();
            $("#op_tags_fields,#op_transfer_fields").show();
            op_changeAccountForTransfer();
        //Перевод на финансовую цель
        } else if ($('#op_type').val() == 4) {
            $("#op_target_fields").show();
            $("#op_tags_fields,#op_transfer_fields,#op_category_fields").hide();
            $('#op_target').change();
        }
    }
















    // Выводим окно с операциями, если у нас пользователь авторизирован
    if (inarray(Current_module, Connected_functional.operation)){//////////////////////////////////
        // Автоматически подгружаем теги
        op_getTags();

        $('#op_btn_Save').click(function(){op_saveOperation();})
        $('#op_btn_Cancel').click(function(){op_clearForm()});

        $("#op_addoperation_but").click(function(){
            $(this).toggleClass("act");
            if($(this).hasClass("act")){
                $(".op_addoperation").show();
            } else {
                $(".op_addoperation").hide();
            }
        });
        $('#op_amount').calculator({
            layout: [$.calculator.CLOSE+$.calculator.ERASE+$.calculator.USE,
                    'MR_7_8_9_-' + $.calculator.UNDO,
                    'MS_4_5_6_*' + $.calculator.PERCENT ,
                    'M+_1_2_3_/' + $.calculator.HALF_SPACE,
                    'MC_0_.' + $.calculator.PLUS_MINUS +'_+'+ $.calculator.EQUALS],
            showOn: 'focus' //opbutton
        });

        $.datepicker.setDefaults($.extend({dateFormat: 'dd.mm.yy'}, $.datepicker.regional['ru']));
        $("#op_date").datepicker();


        $('#op_amount,#op_currency').change(function(){
            if ($('#op_type').val() == 2) {
                //@TODO Дописать округление
                var result = Math.round($('#op_amount').val() / $('#op_currency').val());
                if (!isNaN(result) && result != 'Infinity') {
                    $("#op_convertSumCurrency").html("конвертация: "+result);
                }
            }
        });

        $('#op_account').change(function(){op_changeAccountForTransfer();});
        $('#op_AccountForTransfer').change( function(){op_changeAccountForTransfer();});
        $('#op_type').change(function(){op_changeTypeOperation('add');});
        $('#op_target').change(function(){
            $("span.op_currency").each(function(){
                $(this).text(" "+$("#target :selected").attr("currency"));
            });
            $("#op_amount_done").text(formatCurrency($("#op_target :selected").attr("amount_done")));
            $("#op_amount_target").text(formatCurrency($("#op_target :selected").attr("amount")));
            $("#op_percent_done").text(formatCurrency($("#op_target :selected").attr("percent_done")));
            $("#op_forecast_done").text(formatCurrency($("#op_target_sel :selected").attr("forecast_done")));
        });
    }
    if(inarray(Current_module, Connected_functional.menu)){//////////////////////////////////
      ///////////////////////////////////////////////////////////////////////////////
// left
// nav bar


$('.navigation  li ul').hide()
$('.navigation li.act ul').show()
$('.navigation  li span').click(function(){
    $('.navigation  li span').closest('li').removeClass('act');
    $(this).closest('li').addClass('act');
    $('.navigation  li ul').hide()
    $('.navigation li.act ul').show()
})
var res = {tags:['asd'],
    accounts : {1:{type:'1',id:'1',cur:'rur',def_cur:'12',name:'a',total_balance:'123'}},
    periodic:{1:{id:1,title:'asd',date:'12.12.1111',amount:'1231231.12'}},
    user_targets:{1:{title:'asd',amount_done:'134',percent_done:'34',date_end:'12.12.1211'}},
    popup_targets:{1:{title:'asd'}},
    currency:{1:{cost:'12',name:'ero',progress:'down'}},
    flash:{title:'asdad',value:100,color:1}};
// tags
            data = res['tags'];
            str = '<div class="title">\n\
                        <h2>Теги</h2>\n\
                        <a title="Добавить" class="add">Добавить</a>\n\
                    </div>\n\
                    <ul>';
            for (key in data)
            {
                str = str + '<li><a>'+data[key]+'</a></li>';
            }
            $('.tags_list').html(str+'</ul>');

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
        data=res['accounts'];
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
                    str = str + '<span>'+data[key]['name']+'</span>';
                    str = str + '<b>'+formatCurrency(data[key]['total_balance']);
                    str = str + data[key]['cur']+ '</b>'+'</a></li>';
                    summ[i] = summ[i]+data[key]['def_cur'];
                    if (!val[data[key]['cur']]) {
                        val[data[key]['cur']]=0;
                    }
                    val[data[key]['cur']] = parseFloat( val[data[key]['cur']] )
                        + parseFloat(data[key]['total_balance']);
                    
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
                str = '<ul>';
                for(key in val)
                {
                    str = str+'<li><div>'+formatCurrency(val[key])+' '+key+'</div></li>';
                }
                str = str+'<li><div><strong>Итого:</strong> <br>'+formatCurrency(total)+' руб.</div></li>';
                str = str + '</ul>';
                 $('.accounts #l_amount').html(str);


       $('.accounts .add').click(function(){
           document.location='/accounts/#add';
       })
       $('.accounts li a').live('click',function(){
           document.location='/accounts/#?'+$(this).find('div.id').attr('value');
       })
      ///////////////////////periodic/////////////////////////////////////////
      data = res['periodic'];
            c = '<h2>Регулярные транзакции</h2><ul>';
            for(var id in data) {
                c += '<li id="'+id+'">'
                    +'<a href="/periodic/">'+data[id]['title']+'</a>'
                    +'<b>'+ data[id]['amount']+'</b>'
                    +'<span class="date">'+data[id]['date']+'</span></li>';
            }
            c = c + '</ul>';
            $('.transaction').html(c);
      /////////////////////////targets///////////////////////////////////
      data = res['user_targets'];
            s = '<div class="title"><h2>Финансовые цели</h2><a href="#" title="Добавить" class="add">Добавить</a></div><ul>';
            for(v in data)
            {
                        s += '<li><a href="/targets/">'+data[v]['title']+'</a><b>'
                        +data[v]['amount_done']+' руб.</b><span>('
                        +data[v]['percent_done']+'%)</span><span class="date">'
                        +data[v]['date_end']+'</span></li>';
            }
            s = s+'</ul>';
            

            data = res['popup_targets'];
            s = s + '<h2>5 самых популярных</h2><ul class="popular">';
            for(v in data) {
                s += '<li><a href="#">'
                    +data[v]['title']+'</a></li>';
            }
            s = s + '<ul>';
        $('.financobject').append(s);
//////////////////////////////////////////////////////////////////////
//right
//currency
    data = res['currency'];
    str = '';
    for(key in data)
    {
        cost = data[key]['cost'];
        name = data[key]['name'];
        progres = data[key]['progress'];
        str = '<div class="line"><span class="valuta">'+name+'</span><span class="'+progres+'">'+cost+'</span>'
    }
    $('dl.info dd').html(str);
//calendar
    $('.calendar_block .calendar').datepicker();
//flash
    data = res['flash']

            name = (!data['title'])?'':['title'];
            //end = data['value']*3/data[1][i]['color'] ;
            value = data['value'] ;
            xml = '<anychart><gauges><gauge><chart_settings><title>'+
                '<text>'+name+'</text>'+
		"</title></chart_settings><circular><axis radius='50' start_angle='85' sweep_angle='190' size='3'><labels enabled='false'></labels><scale_bar enabled='false'></scale_bar> <major_tickmark enabled='false'/><minor_tickmark enabled='false'/><color_ranges>"+
                "<color_range start='0' end='100' align='Inside' start_size='15' end_size='15' padding='6'>"+
                "<fill type='Gradient'><gradient><key color='Red'/><key color='Yellow'/><key color='Green'/></gradient></fill><border enabled='true' color='#FFFFFF' opacity='0.4'/></color_range></color_ranges></axis><frame enabled='false'></frame><pointers>"+
                "<pointer value='"+value+"'>"+
                "<label enabled='true' under_pointers='true'><position placement_mode='ByPoint' x='50' y='100'/><format>{%Value}</format><background enabled='false'/></label><needle_pointer_style thickness='7' point_thickness='5' point_radius='3'><fill color='Rgb(230,230,230)'/><border color='Black' opacity='0.7'/><effects enabled='false'></effects><cap enabled='false'></cap></needle_pointer_style><animation enabled='false'/></pointer></pointers></circular></gauge></gauges></anychart>";
            chartSample_1 = new AnyChart('/swf/anychart/gauge.swf');
                    chartSample_1.width = '170px';
                    chartSample_1.height = '170px';
                    chartSample_1.setData(xml);
                    chartSample_1.wMode="opaque";
                    chartSample_1.write('flash');
                    chartSample_1 = null;

$('.calculator_block .calculator').calculator({
    layout: [
                    '_7_8_9_+CA',
                    '_4_5_6_-M+',
                    '_1_2_3_/M-',
                    '_0_._=_*MS']});  //
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
            sm = submenu[page_mid]?submenu[page_mid]:'';
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
            str = $('ul.menu4 a[href="'+pathName+'"]').text();
            if (str){
                $('ul.menu4 a[href="'+pathName+'"]').closest('li').html('<span><b>'+str+'</b></span>');
                //$('ul.menu4 a[href="'+pathName+'"]').remove();
            }
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

    $('.listing#c2').css('display', 'block');
    $('ul.control li').click(function(){
        $('ul.control li').removeClass('act');

        $(this).addClass('act');
        id = $(this).attr('id');
        $('.listing').css('display', 'none');
        $('.listing#'+id).css('display', 'block');
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



//Google Analytics
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));try {var pageTracker = _gat._getTracker("UA-10398211-2");pageTracker._trackPageview();} catch(err) {}
