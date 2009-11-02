//Тут только общие функции и события для всех страниц сайта
// $Id$
//conf href to modul

function get_array_key($arr, $val)
{
    var $ret = -1;
    for(var key in $arr)
    {
        if ($val == $arr[key])
        {
            $ret = key;
            break;
        }
    }
    return $ret;
}
var aPath=['//',
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
var b=0;
var nhref=href.match(/\/[a-z]{0,}\//);
var pathName = nhref[0];
var Current_module = get_array_key(aPath, nhref);
var Connected_functional = {operation:[2,5,6,7,8,11,15,16,19,25],
                            menu:[2,5,6,7,8,11,15,16,17,19,25]};

var TransferSum = 0; //глобальная переменная которая передаст. сумму при переводе на другую валюту.
function FloatFormat(obj, in_string )
{
    //'.'
    var l = in_string.length;
    var rgx = /[0-9]/;
    var c=0;
    var p =1;
    var newstr ='';
    var i = 0;
    
    for(var a=1;a<=l;a++)
    {
        i=l-a+1;
        if (rgx.test(in_string.substr(i,1)))
        {
            if (c == 3)
            {
                newstr = ' ' + newstr;
                c = 0
            }
            newstr =in_string.substr(i,1)+newstr
            c++;
        }
        if (in_string.substr(i,1)=='.' || in_string.substr(i,1)==',')
        {
            if (p){
                newstr = newstr.substr(0,2)
                newstr ='.'+newstr;
            }
            c=0;
            p = 0;
        }
    }
    if (in_string.substr(1,1) == '-')newstr ='-'+newstr;
    $(obj).val(newstr)
}

function tofloat(s)
{
    if (s != null) {
        s = s.toString();
        return s.replace(/[ ]/gi, '');
    } else {
        return '';
    }
}

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

//запланировано 

$(document).ready(function() {
    $.datepicker.setDefaults($.extend({dateFormat: 'dd.mm.yy'}, $.datepicker.regional['ru']));

    // *** Функции ***

    if (res['errors'] != null && res['errors'].length > 0) {
        for (v in res['errors']) {
            $.jGrowl(res['errors'][v], {theme: 'red'});
        }
    }

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

    // Выводим окно с операциями, если у нас пользователь авторизирован
    if (inarray(Current_module, Connected_functional.operation)){//////////////////////////////////        
        // init main finance gauge
        var flashvars = {title: "", value: res['flash']['value'], bgimage: "/img/i/gauge107.gif"};
        var params = {wmode: "transparent"};
        var attributes = {id: "gaugeMain"};
        swfobject.embedSWF("/swf/efGauge.swf", "divGaugeMain", "107", "107", "9.0.0", false, flashvars, params, attributes);
        //
        // инициализируем виджет добавления и редактирования операции
        easyFinance.widgets.operationEdit.init('.op_addoperation', easyFinance.models.accounts);
        
        ////////////////////////////////////add to calendar
        
        $('#op_addtocalendar_but').click(function(){
            $('button#remove_event').remove();//@deprecate IE7 opera8 @todo delete when no use;
            add2call();
        });

        function ac_save() {
            /*
             *@TODO Проверить вводимые значения ui-tabs-selected
             */
            if ($('#cal_href').val() == '' || $('#cal_href').val() == 0) {
                if ($('#cal_mainselect .act').attr('id')=='periodic') {
                    href = '/periodic/add/';
                } else {
                    href = '/calendar/add/';
                }
            } else {
                href = $('#cal_href').val();
            }

            var infinity;
            if ( $('.rep_type:checked').attr('rep') == 2 ) {
                infinity = 1;
            } else {
                infinity = 0;
            }
            var d = {
                id:         $('#op_dialog_event #cal_key').attr('value'),
                title:      $('#op_dialog_event #cal_title').attr('value'),
                date_end:   $('#op_dialog_event #cal_date_end').attr('value'),
                date:       $('#op_dialog_event #cal_date').attr('value'),
                time:       $('#op_dialog_event #cal_time').attr('value'),
                repeat:     $('#op_dialog_event #cal_repeat option:selected').attr('value'),
                count:      $('#op_dialog_event #cal_count').attr('value'),
                comment:    $('#op_dialog_event #cal_comment').attr('value'),
                infinity:   infinity,
                amount:     $('#op_dialog_event #cal_amount').val(),
                category:   $('#op_dialog_event #cal_category').val(),
                type:       $('#op_dialog_event #cal_type').val(),
                account:    $('#op_dialog_event #cal_account').val(),
                rep_type:   $('#op_dialog_event .rep_type:checked').attr('rep'),
                mon:        $('.week #mon').attr('checked') ? 1 : 0,
                tue:        $('.week #tue').attr('checked') ? 1 : 0,
                wed:        $('.week #wed').attr('checked') ? 1 : 0,
                thu:        $('.week #thu').attr('checked') ? 1 : 0,
                fri:        $('.week #fri').attr('checked') ? 1 : 0,
                sat:        $('.week #sat').attr('checked') ? 1 : 0,
                sun:        $('.week #sun').attr('checked') ? 1 : 0
            };
            $.post( href, d, function(data){
                // В случае успешного добавления, закрываем диалог и обновляем календарь
                if (data.length == 0) {
                    $('#op_dialog_event').dialog('close');
                    $.jGrowl("Данные сохранены", {theme: 'green'});
                     $(window).trigger("saveSuccess", [d]);
                } else {
                    var e = '';
                    for (var v in data) {
                        e += data[v]+"\n";
                    }
                    $.jGrowl("Ошибки при сохранении : \n" + e, {theme: 'red', stick: true});
                }
            }, 'json');
        }

        $('#op_pcount').select(function(){
            $('#op_pcounts').removeAttr('disable');
        })
        $('#op_pinfinity').select(function(){
            $('#op_pcounts').Attr('disable','disable')
        })

        function add2call() {
            $('#cal_amount').keyup(function(e){
                FloatFormat(this,String.fromCharCode(e.which) + $(this).val())
            });
            $('input#cal_date,input#cal_date_end').datepicker();
            $('#cal_time').timePicker().mask('99:99');
            $('#cal_repeat').change(function(){
                if ($('#cal_repeat').val()=="7"){ // Неделя
                    $('#week.week').closest('.line').show();
                    $('.repeat').closest('.line').show()
                }else if($('#cal_repeat').val()=="0"){ // Не повторять
                    $('#week.week').closest('.line').hide();
                    $('.repeat').closest('.line').hide()
                }else{ // Иначе
                    $('#week.week').closest('.line').hide();
                    $('.repeat').closest('.line').show()
                }
            });
            $('.repeat .rep_type').change(function(){
                $('#cal_count,#cal_infinity,#cal_date_end').attr('disabled','disabled');
                $('.repeat .rep_type:checked').closest('div').find('input,select').removeAttr('disabled');
            })
            $('#cal_type').change(function(){
               toggleVisibleCategory('cal_category', $('option[selected]',this).val());
            });
            $('#op_dialog_event div.line').hide();
            $('#op_dialog_event div.line').hide();
            $('#op_dialog_event .event').show();
            var k;
            $('#cal_mainselect li').click(function(){
                $('#op_dialog_event div.line').hide();
                $('#cal_mainselect li').removeClass('act');
                $('#op_dialog_event .'+$(this).addClass('act').attr('id')).show();
                if ($('#cal_repeat').val()=="7"){
                    $('#week.week').closest('.line').show();
                    $('.repeat').closest('.line').show()
                }else if($('#cal_repeat').val()=="0"){
                    $('#week.week').closest('.line').hide();
                    $('.repeat').closest('.line').hide()
                }else{
                    $('#week.week').closest('.line').hide();
                    $('.repeat').closest('.line').show()
                }
            });
            $('#week.week').closest('.line').hide();
            $('.repeat').closest('.line').hide()

            /**
             * Изменение типа операции. показываем расходные и доходные категории.+универсальные
             */
            $('#cal_type').change(function(){
                if ($('#cal_type').val() == 1)
                    toggleVisibleCategory($('#cal_category'),1);
                if ($('#cal_type').val() == 0)
                    toggleVisibleCategory($('#cal_category'),-1);//отображает в списке категорий для добавления операции доходные

            });


            $('#op_dialog_event').css({width:'500px',height:'auto',overflow:'visible'});
            //$('.repeat input:not[.rep_type],.repeat select').attr('disabled','disabled');
            $('#op_dialog_event').dialog({
                bgiframe: true,
                autoOpen: false,
                width: 447,
                //height: 350,
                buttons: {
                    'Сохранить': function() {
                        ac_save();
                    },
                    'Отмена': function() {
                        $(this).dialog('close');
                    }
                },
                close : function(){
                    $('#cal_mainselect').removeAttr('disabled')
                    $('input[type="text"],select,textarea','#op_dialog_event').val('');
                    $('#op_dialog_event #cal_repeat').val(0);
                }
            });
            
            $('#op_dialog_event').dialog();
//            for(i = 1; i < 31; i++){
//                $('#op_dialog_event #op_acount').append('<option>'+i+'</option>').val(i);
//                $('#op_dialog_event #op_pcounts').append('<option>'+i+'</option>').val(i);
//            }
            $('#op_dialog_event').dialog('open');
            $('.ui-dialog-buttonpane').css('margin-top','30px');
            //$('#op_adate,#op_pdate').val(dateText);   
        }
    }

    if(inarray(Current_module, Connected_functional.menu)){
        $('.listing').hide();
        $('.navigation  li ul').hide()
        $('.navigation li.act ul').show()
        $('.navigation  li span').click(function(){
            $('.navigation  li span').closest('li').removeClass('act');
            $(this).closest('li').addClass('act');
            $('.navigation  li ul').hide()
            $('.navigation li.act ul').show()
        });

/**
 * Загружаем теги для левой панели
 */
function loadLPTags(){
    var data = res['tags'];
    var str = '<div class="title"><h2>Теги</h2><a title="Добавить" class="add">Добавить</a></div><ul>';
    for (var key in data)
    {
        str = str + '<li><a>'+data[key]+'</a></li>';
    }
    str += '</ul>';
    $('.tags_list').empty().append(str);

}
loadLPTags();
$('.tags_list li a').live('click', function(){
    $('.edit_tag').dialog('open');
    $('.edit_tag input').val($(this).text());
    $('.edit_tag').dialog({
        width: 260,
        minHeight: 50,
        buttons: {
            'Сохранить': function() {
                    $.post('/tags/edit/', {
                        tag: $('.edit_tag').find('#tag').val(),
                        old_tag: $('.edit_tag #old_tag').val()
                    },function(data){
                        if (data) {
                            $.jGrowl('Тег успешно сохранён', {theme: 'green'});
                            res.tags = null;
                            var tags = {tags: data}
                            res = $.extend(res, tags);
                            loadLPTags();
                            $('.edit_tag').dialog('close');
                            $('.edit_tag #tag,.edit_tag #old_tag').val('');
                        } else {
                            $.jGrowl('Ошибка при сохранении тега', {theme: 'red'});
                        }
                    },'json');
            },
            'Удалить': function() {
                if (confirm('Тег "'+$('.edit_tag #old_tag').val()+'" будет удалён. Удалить?')) {
                    var tag = $('.edit_tag #old_tag').val();
                    $.post('/tags/del/', {
                        tag: tag
                        },function(data){
                            if (!data) {
                                data={};
                            }
                                $.jGrowl('Тег удалён', {theme: 'green'});

                                $('.edit_tag #tag,.edit_tag #old_tag').val(0);
                                delete res.tags;
                                var tags = {tags: data}
                                res = $.extend(res, tags);
                                loadLPTags();
                                $('.edit_tag').dialog('close');
                        },
                        'json'
                    );
                }
            }
    }})
})

$('.tags_list .add').live('click', function(){
    var add = $('.add_tag');
    $(add).show().dialog('open').dialog({
        width: 260,
        minHeight: 50,
        buttons: {
            'Сохранить': function() {
                if($('input#tag',add).val()) {
                    $.post('/tags/add/', {
                        tag:$('.add_tag input').val()
                    }, function(data){
                        if (data) {
                            delete res['tags'];
                            var tags = {tags: data}
                            res = $.extend(res, tags);
                            loadLPTags();
                            $('.add_tag').dialog('close');
                            $('.add_tag input').val('');
                            $.jGrowl('Новый тег успешно добавлен', {theme: 'green'});
                        } else {
                            $.jGrowl('Ошибка при добавлении тега', {theme: 'red'});
                        }
                        $('.add_tag').dialog('close');
                    },'json');
                    $('.add_tag').dialog('close');
                }
            }
        }
    })
})

    // accounts
    // modified by Jet 29.10.2009, ticket 337.
    /*easyFinance.models.accounts.load(function(model) {
        easyFinance.widgets.accountsPanel.init('.accounts', model);
    });*/

      ///////////////////////periodic/////////////////////////////////////////
      var data = res['events'];
      //events:{
      //"5694":{
      //    "id":"5694",
      //    "chain":"0",
      //    "title":"1234",
      //    "date":"10.10.2009",
      //    "comment":"",
      //    "event":"cal",
      //    "amount":"0.00",
      //    "category":"0",
      //    "diff":"0",
      //    "account":"0",
      //    "drain":"0"}
            var p = '';var c = '';
            for(var id in data) {
                if (data[id]['event']=='cal') {
                    c += '<li id="'+id+'">'
                        +'<a href="/calendar/">'+data[id]['title']+'</a>'
                        +'<span class="date">'+data[id]['date']+'</span></li>';
                } else {
                    p += '<li id="'+id+'">'
                        +'<a href="/periodic/">'+data[id]['title']+'</a>'
                        +'<b>'+ data[id]['amount']+'</b>'
                        +'<span class="date">'+data[id]['date']+'</span></li>';
                }

            }
            if (c != '') {
                c = '<h2>События календаря</h2><ul>' + c + '</ul>';
            }
            if (p != '') {
                p = '<h2>Регулярные транзакции</h2><ul>' + p + '</ul>';
            }
            if ((c+p) != '') {
                $('.transaction').html(c+p+'&nbsp;<a href="#" id="AshowEvents">Показать события</a>');
            }
      /////////////////////////targets///////////////////////////////////
      data = res['user_targets'];
            s = '<div class="title"><h2>Финансовые цели</h2><a href="/targets/#add" title="Добавить" class="add">Добавить</a></div><ul>';
            for(v in data)
            {
                if (data[v]['done'] == 0){
                        s += '<li ><a href="/targets/#edit/'+v+'">'+data[v]['title']+'</a><b>'
                        +data[v]['amount_done']+' руб.</b><span>('
                        +data[v]['percent_done']+'%)</span><span class="date">'
                        +data[v]['date_end']+'</span></li>';
                }
            }
            s = s+'</ul>';
            

            data = res['popup_targets'];
            s = s + '<h2>5 самых популярных</h2><ul class="popular">';
            var popular=0;
            for(v in data) {
                popular++;
                if (popular<=5)
                s += '<li><a href="#">'
                    +data[v]['title']+'</a></li>';
            }
            s = s + '<ul>';
        $('.financobject').append(s);
        $('.financobject div.title a').live('click',function(){
            $("div.financobject_block .add span").click()
        })
        $('.financobject ul a').live('click',function(){
            var id = $(this).attr('href');
            window.location = id;
            var str = id.substr(15);
            var f = $('.object[tid="'+str+'"]');
            $('input,textarea','#tpopup').val('');
            $('#key').val(f.attr('id'));
            $('#type').val(f.attr('type'));
            $('#title').val(f.attr('title'));
            $('#name').val(f.attr('name'));
            $('#targets_category').val(f.attr('category'));
            $('#tg_amount').val(f.attr('amount'));
            $('#start').val(f.attr('start'));
            $('#end').val(f.attr('end'));
            $('#photo').val(f.attr('photo'));
            $('#url').val(f.attr('url'));
            $('#comment').val(f.attr('comment'));
            $('#account').val(f.attr('account'));
            $('#visible').val(f.attr('visible'));
            $('#tpopup').dialog('open');
            //return false;
        })
        //$('.financobject ')
//////////////////////////////////////////////////////////////////////
//right
//currency
    data = res['currency'];
    str = '';
    var cost,name,progres;
    var fir = 0;//первая валюта в правом списке
    for(key in data) 
    {
        if (fir != 0) // валюта по умолчанию первая в списке ! не показываем её в правой панели
        {
        cost = data[key]['cost'];
        name = data[key]['name'];
        progres = data[key]['progress'];
        if (!cost){continue;}
        str += '<div class="line"><span class="valuta">'+name+'</span><span class="'+progres+'">'+cost+'</span></div>'
        }
        fir++;
    }
    $('dl.info dd').html(str);
//calendar
    $('.calendar_block .calendar').datepicker();
    $('.calendar_block .calendar a span').css('left',0).css('text-indent','0');
    $('.calendar_block .calendar a.ui-datepicker-prev ').css('display','block').css('left','15px');
    $('.calendar_block .calendar a.ui-datepicker-next ').css('display','block').css('right','15px');

    //$('.calendar_block .calendar a span').click(function(){
        setInterval(function(){//@todo !избавиться как только будет время от этой конструкции!
            $('.calendar_block .calendar a span').css('left',0).css('text-indent','0');
            $('.calendar_block .calendar a.ui-datepicker-prev ').css('display','block').css('left','15px');
            $('.calendar_block .calendar a.ui-datepicker-next ').css('display','block').css('right','15px');
        },100);
    //})

//    $('.calendar_block .ui-datepicker-prev').live('click',function(){
//        alert('a');
//        $('.calendar_block .calendar').datepicker('option', 'defaultDate', '-1M');
//
//        $('.calendar_block .calendar a span').css('left',0).css('text-indent','0');
//        $('.calendar_block .calendar a.ui-datepicker-prev ').css('display','block').css('left','15px');
//        $('.calendar_block .calendar a.ui-datepicker-next ').css('display','block').css('right','15px');
//        return false;
//    })

// Старый тахометр flash
            //data = res['flash'];
            //name = (!data['title'])?'':['title'];
            //end = data['value']*3/data[1][i]['color'] ;
            //value = data['value'] ;
            /*
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
                    chartSample_1 = null;//
         */
        $('.calculator_block .calculator').live('keyup',function(e){
            FloatFormat(this,String.fromCharCode(e.which) + $(this).val())
        })
       $('.calculator_block .calculator').calculator({
            layout: [
                    '_7_8_9_+CA',
                    '_4_5_6_-M+',
                    '_1_2_3_/M-',
                    '_0_._=_*MS']});

        // vvv Jet. Тикет 266. Новое выпадающее меню vvv
        var topmenu = '<div class="menu3"> \
            <ul class="dropdown"> \
                <li id="m1"> \
                        <a href="/info/"></a> \
                </li> \
                <li id="m2"> \
                        <a href="/accounts/"></a> \
                        <ul> \
                                <li><a href="/operation/">Операции</a></li> \
                                <li><a href="/category/">Категории</a></li> \
                                <li class="last"><img src="/img/i/menu3_submenu_bottom.png"/></li> \
                        </ul> \
                </li> \
                <li id="m3"> \
                        <a href="/budget/"></a> \
                        <ul> \
                                <!-- li><a href="/budget/">Бюджет</a></li> --> \
                                <li><a href="/targets/">Фин. цели</a></li> \
                                <li class="last"><img src="/img/i/menu3_submenu_bottom.png"/></li> \
                        </ul> \
                </li> \
                <li id="m4"> \
                        <a href="/report/"></a> \
                </li> \
                <li id="m5"> \
                        <a href="/calendar/"></a> \
                        <ul> \
                                <li><a href="/calendar/">Календарь</a></li> \
                                <li><a href="/periodic/">Рег. транзакции</a></li> \
                                <li class="last"><img src="/img/i/menu3_submenu_bottom.png"/></li> \
                        </ul> \
                </li> \
            </ul> \
        </div>';

        $('#mainwrap').prepend(topmenu);

        var pathtoid = {
            '/accounts/' :'m2',
            '/budget/':'m3',
            '/calendar/':'m5',
            '/category/':'m2',
            '/experts/':'m6',
            '/info/':'m1',
            '/mail/':'m0',
            '/operation/':'m2',
            '/periodic/':'m5',
            '/profile/':'m0',
            '/report/':'m4'};

        var page_mid = pathtoid[pathName];
        $('div#mainwrap #'+page_mid).addClass('cur act').children('a').addClass('cur');

        // код для переключения внешнего вида вкладок
        $('.dropdown').children('li')
            .mouseover(
                function(){
                    // act - делает вкладку активной
                    // over - показывает подменю
                    $(this).addClass('act over');

                    // если мышь на закладке раздела, отличного от текущего
                    // подсвечиваем вкладку текущего раздела зелёным
                    if (!$(this).hasClass('cur'))
                        $(this).siblings('.cur').removeClass('act');
                })
            .mouseout(
                function(){
                    // скрываем подменю
                    $(this).removeClass('over');

                    // если мышь на закладке раздела, отличного от текущего
                    // делаем вкладку текущего раздела активной
                    if (!$(this).hasClass('cur')){
                        $(this).removeClass('act');
                        $(this).siblings('.cur').addClass('act');
                    }
                }
        );
        // ^^^ Jet. Тикет 266. Новое выпадающее меню ^^^

        // Старое динамическое меню
        /*
        pathtoid = {
            '/accounts/' :'m2',
            '/budget/':'m3',
            '/calendar/':'m5',
            '/category/':'m2',
            '/experts/':'m6',
            '/info/':'m1',
            '/mail/':'m0',
            '/operation/':'m2',
            '/periodic/':'m5',
            '/profile/':'m0',
            '/report/':'m4'}
       var page_mid = pathtoid[pathName];
       $('div#mainwrap #'+page_mid).addClass('cur act').children('a').addClass('cur');
       */
            //mmenu ='<div class="menu3"><ul><li id="m1"><a href="/info/" title="Инфо-панель">Инфо-панель</a></li><li id="m2"><a href="/accounts/" title="Счета">Счета</a></li><li id="m3"><a href="/targets/" title="Бюджет">Бюджет</a></li><li id="m4"><a href="/report/" title="Отчеты">Отчеты</a></li><li id="m5"><a href="/calendar/" title="Календарь">Календарь</a></li></ul></div>'
            //if(!$('#menu3').length){
                //$('div#mainwrap').prepend(mmenu);
            //}

        //$('div#mainwrap #'+page_mid).html('<span></span><a class="span" style="display:none;"></a>');
        //$('.menu3 span').closest('li').attr('id');
        //$('.mid, .ccb, #footer, #header, #menumain').mouseover();
        /*
        var act_id = page_mid;
        var submenu = {
            //'m0':[''],
            'm1':['<a></a>'],
            'm2':[  '<a href="/accounts/">Счета</a>',
                    '<a href="/operation/">Журнал операций</a>',
                    '<a href="/category/">Категории</a>'],
            'm3':[
//                    '<a href="/budget/">Бюджет</a>',
                    '<a href="/targets/">Фин цели</a>'],
            'm4':['<a></a>'],
            'm5':[  '<a href="/calendar/">Календарь</a>',
                    '<a href="/periodic/">Регулярные транзакции</a>'],
            'm6':['<a></a>']
        };
        */
        /*
        //@TODO Цикл по submenu и если находит текущую таблицу, то окружает её SPAN
        */
       /*
        if ($('.menu4').length == 0) {
            $('div.cct').after('<ul class="menu4" >&nbsp</ul>');
        }

        $('.menu3 span').live('mouseover',function(){
            $(this).hide().closest('li').find('a').hasClass("span");
        })
        $('.mid, .ccb, #footer, #header, #menumain').mouseover(function(){
            $('.menu3 li').removeClass('act');
            $('.menu3 span').hide();
            $('.menu3 .span').show();
            $('div#mainwrap #'+page_mid).addClass('act').show();
            sm = submenu[page_mid]?submenu[page_mid]:'';
            str='';
            l = sm.length;
            k = 0;
            for(k=0; k<l; k++) {
                str = str+'<li>'+sm[k]+'</li>';
            }
            $('ul.menu4 ').html(str);
            str = $('ul.menu4 a[href="'+pathName+'"]').text();
            if (str){
                $('ul.menu4 a[href="'+pathName+'"]').closest('li').addClass('act').html('<span><b>'+str+'</b></span>');
            }

        })
        $('.menu3 li').live('mouseover',function(){

            act_id = $(this).attr('id');
            $('.menu3 .span').show();
            if(act_id != page_mid)
            {
                $('.menu3 .span').closest('li').find('span').show();
                $('.menu3 .span').hide();
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
                $('ul.menu4 a[href="'+pathName+'"]').closest('li').addClass('act').html('<span><b>'+str+'</b></span>');
            }
            return false;
        })
        */
    }

    // Кнопка сворачивания / разворачивания
    $('li.over3,li.uparrow').addClass('uparrow').toggleClass('uparrow').click(function() {
        /*
        //@TODO Сохранять значение в куках и потом читать их из куков
        */
        $(this).closest('div.ramka3').find('div.inside').toggle();
        $(this).toggleClass('uparrow').toggleClass('over3');
        var title = $(this).find('a').attr('title') == 'свернуть' ? 'развернуть' : 'свернуть'
        $(this).find('a').attr('title',title);
        //$(this).closest('div.ramka3').className("over2");
    }).find('a').removeAttr('href');

    // Кнопка закрыть
    $('li.over2').hide();
    $('li.over1').hide();
    $('li.over2').click(function() {
        /*
        //@TODO Сохранять значение в куках и потом читать их из куков
        */
        $(this).closest('div.ramka3').hide();
    }).find('a').removeAttr('href');

    // Кнопка настроек виджета
    $('li.over1').click(function() {
        /*
        //@TODO Сохранять значение в куках и потом читать их из куков
        //@TODO Сделать нормальную
        */
        $(this).closest('div.ramka3').slideDown('slow').slideUp('slow');
    }).find('a').removeAttr('href');

    //$('.listing.c1').css('display', 'block');
    $('ul.control li').click(function(){
        $('ul.control li').removeClass('act');
        $(this).addClass('act');
        id = $(this).attr('id');
        $('.listing').hide();
        s = '.'+id+'.listing';
        $(s).show();

        if (this.id == "c2")
            easyFinance.widgets.accountsPanel.redraw();

        return false;
    });
    setTimeout(function(){$('ul.control li#c1').click()},500);
    /*
    @todo account hack
    */
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
            $('#footer #popupreport').css({top : '30%',position:'fixed',left:'0px'}).toggle();
        });

    //отправление сообщения
    $('#footer .but').click(
        function (){
            var num_of_plugins = navigator.plugins.length;
            var str='';
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
    
	/*
	*Открытие окна для авторизации. 
	*
	*@todo Заменить на адекватную авторизацию в отдельной странице.
	*/
	
        if (pathName=='/login/' && window.location.protocol=='https:') {
            $('#login').show();
        }

        $('#show_login').click(function() {
            if (window.location.host.toString().substr(0, 5) == "demo.") {
                window.location.href='http://' + window.location.host + '/login/';
            } else {
                if (window.location.protocol!='https:') {
                    window.location.href='https://' + window.location.host + '/login/';
                } else {
                    $('#login').show();
                }
            }
            return false;
        });
        
        $('#login .close').click(function(){
            $('#login').hide();
        });
    //}
    //});

    /**
     * Вслывающее окно с регулярными транзакциями и событиями календаря
     */
    // Щелчок по кнопке закрытия окна
    $('#popupcalendar .inside .close').click(function(){
        $('#popupcalendar').hide();
        $.jGrowl('В текущей сессии окно с событиями не будет показываться', {theme: ''});
        $.cookie('events_hide', 1, {path: '/'});
    });
    $('#AshowEvents').live('click',function(){
         $.cookie('events_hide', null, {path: '/'});
         ShowEvents();
         return false;
    });
    // Щелчок по кнопке "Подтвердить"
    $('#btnAccept').click(function(){
        var ch = $('#events_periodic tbody .chk input:checked, #events_calendar tbody .chk input:checked');
        if ($(ch).length > 0 && confirm('Подтвердить операции с отмеченными элементами?')) {
            var obj = new Array ();
            $(ch).each(function(){
                obj.push($(this).closest('tr').attr('value'));
            });
            $.post('/calendar/events_accept/', {
                ids: obj.toString()
            }, function(data){
                for(v in obj) {
                    delete res.events[obj[v]];
                }
                $.jGrowl('Отмеченные события подтверждены', {theme: 'green'});
                ShowEvents();

            }, 'json');
        }
    });

    // Щелчок по кнопке "Пропустить"
    $('#btnContinue').click(function(){

    });
    // Щелчок по кнопке "Редактировать"
    $('#btnEdit').click(function(){

    });
    // Щелчок по кнопке "Удалить"
    $('#btnDel').click(function(){
        var ch = $('#events_periodic tbody .chk input:checked, #events_calendar tbody .chk input:checked');
        if ($(ch).length > 0 && confirm('Удалить отмеченные?')) {
            var obj = new Array ();
            $(ch).each(function(){
                obj.push($(this).closest('tr').attr('value'));
            });
            $.post('/calendar/events_del/', {
                ids: obj.toString()
            }, function(data){
                for(v in obj) {
                    delete res.events[obj[v]];
                }
                $.jGrowl('Отмеченные события удалены', {theme: 'green'});
                ShowEvents();

            }, 'json');
        }
    });
    // При щелчке по родительскому чекбоксу
    $('#events_periodic thead .chk input,#events_calendar thead .chk input').click(function(){
        var parentCheckbox = this;
        $('tbody .chk input', $(parentCheckbox).closest('table')).each(function(){
            if ($(parentCheckbox).attr('checked')) {
                $(this).attr('checked','checked');
            } else {
                $(this).removeAttr('checked');
            }
        });
    });

    if (window.location.host.toString().substr(0, 5) != "demo.") 
        ShowEvents();
    
    /**
     * Выводит окошко пользователя для управления событиями
     */
    function ShowEvents(type,page) {
        if (type == null) {type = '';}
        if (page == null) {page = 1 ;}
        if ((res['events']) != null) {

            var ptr = '',ctr = '',p = 0,c = 0,pc = 0,cc = 0;
            var counti = 4;//не работает count
            var start = (page-1) * counti;
            var end   = counti * page;
            for (var v in res['events']) {

                if (res['events'][v]['event'] == 'per') {
                    if (p >= start && p <= end) {
                        var cat_name = $('#ca_'+parseInt(res['events'][v]['category'])).attr('title');
                        if (cat_name === undefined) { 
                            cat_name = ' ';
                        }

                        var account_name = '';
                        if (res.accounts[res.events[v]['account']] !== undefined) {
                            account_name = res.accounts[res.events[v]['account']]['name'];
                        }
                        
                        ptr += '<tr value="'+res['events'][v]['id']+'"><td class="chk"><input type="checkbox" /></td>'
                                    +'<td>'+res['events'][v]['date']+'</td>'
                                    +'<td>'+res['events'][v]['title']+'</td>'
                                    +'<td>'+res['events'][v]['diff']+'</td>'
                                    +'<td>'+cat_name+'</td>'
                                    +'<td>'+account_name+'</td>'
                                    +'<td class="money">'+res['events'][v]['amount']+'</td></tr>';
                        p++;
                    }
                    pc++;
                } else if (res['events'][v]['event'] == 'cal') {
                    if (c >= start && c <= end) {
                        if (res['events'][v]['drain'] == 1) {
                            drain = 'Расход';
                        } else {
                            drain = 'Доход';
                        }
                        ctr += '<tr value="'+res['events'][v]['id']+'"><td class="chk"><input type="checkbox" /></td>'
                                    +'<td>'+res['events'][v]['date']+'</td>'
                                    +'<td>'+res['events'][v]['comment']+'</td>'
                                    +'<td>'+res['events'][v]['diff']+'</td>'
                                    +'<td>'+drain+'</td></tr>';
                        c++;
                    }
                    cc++;
                }
            }
            $('#events_periodic tbody').html(ptr);
            $('#events_calendar tbody').html(ctr);
//            ppages = parseInt(pc / p);
//            cpages = parseInt(cc / c);
//            for (i = 0; i < ppages; i++) {
//
//            }
//            $('#pages_periodic').html();
            if (pc > 0 || cc > 0) {
                $('#events_periodic thead .chk input,#events_calendar thead .chk input').removeAttr('checked');
                if ($.cookie('events_hide') != 1) {
                    $('#popupcalendar').show();
                }
                //$('#popupcalendar .inside').css('width', 'auto');
            } else {
                $('#popupcalendar').hide();
            }
//            <th>Пр. дней</th>
//            <th>Категория</th>
//            <th>Счет</th>
//            <th class="money">Сумма</th>
        }
    }



    // Установить куки
    // del если 1 то удаляем
function setCookie(name, value, del) {
      var valueEscaped = escape(value);
      var expiresDate = new Date();
      expiresDate.setTime(expiresDate.getTime() + 30 * 24 * 60 * 60 * 1000); // срок - 1 год, но его можно изменить
      var expires = expiresDate.toGMTString();
      if (del == 1){
          var newCookie = name + "=" + valueEscaped + "; path=/; expires=Thu, 01-Jan-70 00:00:01 GMT; ";
      }else{
        var newCookie = name + "=" + valueEscaped + "; path=/; expires=" + expires;
      }
      if (valueEscaped.length <= 4000) document.cookie = newCookie + ";";
}

// Получить куки
function getCookie(name) {
      var prefix = name + "=";
      var cookieStartIndex = document.cookie.indexOf(prefix);
      if (cookieStartIndex == -1) return null;
      var cookieEndIndex = document.cookie.indexOf(";", cookieStartIndex + prefix.length);
      if (cookieEndIndex == -1) cookieEndIndex = document.cookie.length;
      return unescape(document.cookie.substring(cookieStartIndex + prefix.length, cookieEndIndex));
}


    //Функция показывает гид.
    /*$.post('/accounts/countacc/', {},
    function(data){
        if (data[0]['cou'] == 0){
            setCookie('guide','uyjsdhf');
        }
    }, 'json');*/
    if ( getCookie('guide') == "uyjsdhf")
        ShowGuide();


    function ShowGuide(){
        //alert('гид!');
        //$("#tabs").tabs();
        $('#guide').tabs();
        $('#guide').show();
        $('#dial').show();
        $('#dial').dialog(
        {  width: 600}
        );
        $('.dial').bind('dialogclose', function(event, ui) {
            //setCookie2('guide','',0,COOKIE_DOMEN);
            $.post('/profile/cook/');
            $.jGrowl('Гид отключён. Включить его Вы всегда можете в настройках профиля.', {theme: 'green', stick: true});
            /*$('#conf').bind('dialogclose', function(event, ui) {
                setCookie('guide','',1);
                /*if ($('#ch').attr('checked')){
                    //не показываем очень-очень долго
                    setCookie('guide','uyjsdhf');
                }else{
                    //не показываем в эту сессию
                    setCookie('guide','');
                }*/
            //});*/
            /*$('#conf').show();
            $('#conf').dialog({
                //modal: true
            })*/
        });
        
        //$("#guide").draggable();
        //$("#guide").resizable();
    }
})

    // Jet. Рефакторинг 23.10.2009
    // этот функционал перенесён в виджеты
    // operations/operationEdit, operations/operationCalendarEdit


    /**
     * При переводе со счёта на счёт, проверяем валюты
     * @return void
     */
    /*
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
    */

    /**
     * При изменении типа операции
     */
    /*
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
    */

       /**
     * Получает список тегов
     */
    /* Jet: deprecated!
   function getTags(ltags) {
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

        if (ltags) {
            var str = '<ul>';
                var n,k;
            for (var key in data) {
                k = data[key]['cnt']/data[0]['cnt'];
                n = Math.floor(k*5);
                str = str + '<li class="tag'+n+'"><a>'+data[key]['name']+'</a></li>';
            }
        } else {
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
    }
    */

    /**
     * Добавляет новую операцию
     * @return void
     */
    /*
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
        }, function(data){
            for (var v in data) {
                //@FIXME ;Дописать ;обработку ;ошибок ;и ;подсветку ;олей ;с ;ошибками
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
    */
       /**
     * Проверяет валидность введённых данных
     */
    /*
    function validateForm() {
        $error = '';
        if (isNaN(parseFloat($('#amount').val()))){
            alert('Вы ввели неверное значение в поле "сумма"!');
            return false;
        }

        if ($('#type') == 4) {
            //FIXME Написать обновление финцелей
            amount = parseFloat($("#target_sel option:selected").attr("amount"));
            $("#amount").text(amount);
            amount_done = parseFloat($("#target_sel option:selected").attr("amount_done"));$("#amount_done").text(amount_done);
            if ((amount_done + parseFloat($("#amount").val())) >= amount) {
                if (confirm('Закрыть финансовую цель?')) {
                    $("#close").attr("checked","checked");
                }
            }
        }
        return true;
    }
    */

       /**
     * Переключает видимость категорий
     * @param field Gj
     * @param type 1 - доход, -1 - расход
     */
    /*
    function toggleVisibleCategory(field, type) {
        $('option',field).each(function(){
            var opt = this;
            if ( ($(this).attr('iswaste') == type) || ($(opt).attr('iswaste') == '0') ) {
                $(opt).css('display','block');
            } else {
                $(opt).css('display','none');
            }
        });
    }
    */
       /**
     * Очищает форму
     * @return void
     */
    /*
    function clearForm() {
        $('#type,#category,#target').val(0);
        $('#amount,#AccountForTransfer,#comment,#tags,#date').val('');
        $('#amount_target,#amount_done,#forecast_done,#percent_done').text('');
        $('#close').removeAttr('checked');
        $('form').attr('action','/operation/add/');
        $('#type').change();
    }
    */