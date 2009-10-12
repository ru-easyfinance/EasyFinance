// {* $Id: targets.js 128 2009-08-07 15:20:49Z ukko $ *}
$(document).ready(function(){
// <editor-fold defaultstate="collapsed" desc=" Инициализация объектов ">
    $('#tg_amount,#amountf').live('keyup',function(e) {
        FloatFormat(this,String.fromCharCode(e.which) + $(this).val())
    })

    $("#start,#end").datepicker({dateFormat: 'dd.mm.yy'});

    // Добавить фин.цель
    $("div.financobject_block .add span").click(function(){
        clearForm();
        $('#tpopup form').attr('action','/targets/add/');
        $('#tpopup').dialog('open');
    });

    // Присоединиться к популярной финансовой цели
    $(".join, .name").live('click', function(){
        clearForm();
        $('#name').val($(this).closest('li').find('a:first').html());
        $('#tpopup form').attr('action','/targets/add/');
        $('#tpopup').dialog('open');
        return false;
    });

    // Редактируем одну из наших целей
    $(".f_f_edit,div.descr a").live('click', function(){
       var f = $(this).closest('.object');
        clearForm();
        $('#key').val(f.attr('tid'));
        $('#type').val(f.attr('type'));
        $('#targets_category').val(f.attr('category'));
        $('#name').val(f.attr('name'));
        $('#tg_amount').val(f.attr('amount'));
        $('#amountf').val(f.attr('money'));
        $('#start').val(f.attr('start'));
        $('#end').val(f.attr('end'));
        $('#photo').val(f.attr('photo'));
        $('#url').val(f.attr('url'));
        $('#comment').val(f.attr('comment'));
        $('#account').val(f.attr('account'));
        if (f.attr('visible')==1){
            $('#visible').attr('checked','checked');
        }else{
            $('#visible').removeAttr('checked');
        }
        $('#tpopup form').attr('action','/targets/edit/');
        $('#tpopup').dialog('open');
        return false;
    });

    //$('#targets_category').empty();
    for (var v in res['targets_category']) {
        $('#targets_category').append('<option value="'+v+'">'+res['targets_category'][v]+'</option>');
    }

    // Удаляем цель
    $(".f_f_del").live('click', function(){
        var o = $(this).closest('.object');
        if (confirm("Вы уверены, что хотите удалить финансовую цель '"+$(this).closest('.object .descr a').text()+"'?")) {
            $.post('/targets/del/', {
                id: o.attr('tid')
            }, function(){
                o.remove();
                $.jGrowl("Финансовая цель удалена", {theme: 'green'});
            }, 'json');
            return false;
        }
    });

    // Копируем
    $('.f_f_copy').live('click', function(){
       var f = $(this).closest('.object');
        clearForm();
        $('#key').val(f.attr('tid'));
        $('#type').val(f.attr('type'));
        $('#targets_category').val(f.attr('category'));
        $('#name').val(f.attr('name'));
        $('#tg_amount').val(f.attr('amount'));
        $('#amountf').val(f.attr('money'));
        $('#start').val(f.attr('start'));
        $('#end').val(f.attr('end'));
        $('#photo').val(f.attr('photo'));
        $('#url').val(f.attr('url'));
        $('#comment').val(f.attr('comment'));
        $('#account').val(f.attr('account'));
        if (f.attr('visible')==1){
            $('#visible').attr('checked','checked');
        }else{
            $('#visible').removeAttr('checked');
        }
        $('#tpopup form').attr('action','/targets/add/');
        $('#tpopup').dialog('open');
        return false;
    });

    // Загружаем и показываем ВСЕ цели пользователя
    $('div.show_all span').click(function() {
        $.get('/targets/user_list/', '', function(data){
            loadTargets(data);
        }, 'json');
    });

    // Диалог редактирования финансовой цели
    $("#tpopup").dialog({
        bgiframe: true,
        autoOpen: false,
        width: 450,
        buttons: {
            'Сохранить': function() {
                saveTarget();
            },
            'Отмена': function() {
                clearForm();
                $('#tpopup').dialog('close');
            }
        }
    });

// </editor-fold>

// <editor-fold defaultstate="collapsed" desc=" Функции ">
    /**
     * Очищает форму для добавления финансовой цели
     */
    function clearForm() {
        $('#id,#type,#targets_category,#name,#tg_amount,#amountf,#start,#end,#photo,#url,#comment,#account,#visible').val('');
    }

    /**
     * Показывает финцели пользователя
     */
    function loadTargets(data) {
        var s = '';
        for(v in data) {
            s += '<div class="object" tid='+data[v]["id"]+' category='+data[v]["category"]+  ' name='+data[v]["title"]+' amount=' +data[v]["amount"]+ ' start='+data[v]["start"]+' end='+data[v]["end"]+' money='+data[v]["money"]+' account='+data[v]["account"]+ ' visible='+data[v]["visible"]+' comment=' + data[v]["comment"] + '><div class="ban"></div>'
                +'<div class="descr">';
                s += (data[v]['photo']!='')? '<img src="/img/i/fintarget1.jpg" alt="" />' : '<img src="/img/images/pic2.gif" alt="" />';
                    s += '<a href="#">'+data[v]['title']+'</a>'+data[v]['comment']
                    +'</div><div class="indicator_block"><div class="money">'
                    +data[v]['amount']+' руб.<br /><span>'
                    +data[v]['amount_done']+' руб.</span></div><div class="indicator">'
                    +'<div style="width:'+data[v]['percent_done']+'%;"><span>'+data[v]['percent_done']
                    +'%</span></div></div></div><div class="date">Целевая дата: '
                    +data[v]['end']+' &nbsp;&nbsp;&nbsp;</div><ul><li><a href="#" class="f_f_edit">редактировать</a></li>'
                    +'<li><a href="#" class="f_f_copy">копировать</a></li><li><a href="#" class="f_f_del">удалить</a></li></ul></div>';
        }
        $('div.object,div.show_all').remove();
        $('div.financobject_block').append(s);
    }
    /**
     * Заполняет поля формы результатами из массива data
     * @param array data Массив с данными финансовой цели
     * @return void
     */
    function fillForm(data) {
        $('#key').val(data.id);
        $('#category').val(data.category);
        $('#targets_category').val(data.category);
        $('#name').val(data.title);
        $('#type').val(data.type);
        $('#tg_amount').val(data.amount);
        $('#start').val(data.start);
        $('#end').val(data.end);
        $('#visible').val(data.visible);
        $('#photo').val(data.photo);
        $('#url').val(data.url);
        $('#comment').val(data.comment);
        $('#account').val(data.account);
        $('#amountf').val(data.money);
        $('#tpopup form').attr('action','/targets/edit/');
    }

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

    function ValidateForm(){
        che = 1;
        str = '';
        if ($('#category').val() == ''){
            str += "Неверно введена категория<br>"
            //$.jGrowl("Неверно введена категория!!!", {theme: 'red', sticky: true});
            che = 0;
        }
        if ($('#tg_amount').val() == ''){
            str += "Неверно введена сумма финцели<br>"
            //$.jGrowl("Неверно введена сумма финцели!!!", {theme: 'red', sticky: true});
            che = 0;
        }
        a = tofloat($('#amountf').val());
        b = tofloat($('#tg_amount').val());
        if ( a-b > 0){
            str += "Неверно введена нач. сумма<br>";
            che = 0;
        }
        if ($('#name').val() == ''){
            str += "Неверно введено имя<br>"
            //$.jGrowl("Неверно введено имя!!!", {theme: 'red', sticky: true});
            che = 0;
        }
        if ($('#start').val() == ''){
            str += "Неверно введена начальная дата<br>"
            //$.jGrowl("Неверно введена начальная дата!!!", {theme: 'red', sticky: true});
            che = 0;
        }
        if ($('#end').val() == ''){
            str += "Неверно введена конечная дата<br>"
            //$.jGrowl("Неверно введена конечная дата!!!", {theme: 'red', sticky: true});
            che = 0;
        }
        /*if ($('#amountf').val() == ''){
            str += "Неверно введены начальная сумма<br>"
            //$.jGrowl("Неверно введены начальная сумма!!!", {theme: 'red', sticky: true});
            che = 0;
        }*/
        if (che == 1)
            return true;
        else{
            $.jGrowl(str, {theme: 'red', sticky: true});
            return false;
        }
    };

    /**
     * Cохранение объекта
     */
    function saveTarget() {
        //TODO Проверяем валидность и сабмитим
        /*if ( ($('#amountf').val()) > ($('#tg_amount').val()) ){
            $.jGrowl("Сумма начального платежа превышает накопительную сумму!!!", {theme: 'red'});
            return false;
        }*/
        if (!ValidateForm()){
            //$.jGrowl("Неверно введены входные данные!!!", {theme: 'red'});
            return false;
        }
        $.jGrowl("Финансовая цель сохраняется", {theme: 'green'});
        $.post(
            $('#tpopup form').attr('action'),
            {
                id       : $('#key').attr('value'),
                type     : $('#type').attr('value'),
                category : $('#targets_category').attr('value'),
                title    : $('#name').attr('value'),
                amount   : tofloat($('#tg_amount').val()),
                money    : tofloat($('#amountf').val()),
                start    : $('#start').attr('value'),
                end      : $('#end').attr('value'),
                photo    : $('#photo').attr('value'),
                url      : $('#url').attr('value'),
                comment  : $('#comment').attr('value'),
                account  : $('#account').attr('value'),
                visible  : $('#visible:checked').length
            }, function(data){
                // В случае успешного добавления, закрываем диалог и обновляем календарь
//                if (data.length == 0) {
                    $('#tpopup').dialog('close');
                    $.jGrowl("Финансовая цель сохранена", {theme: 'green'});
                    loadTargets(data['user_targets']);
//                } else {
//                    for (var v in data) {
//                        alert('Ошибка в ' + v);
//                    }
//                }
                if ($('#visible:checked').length == 1) {
                    loadPopular();
                }
                clearForm();
            },
            'json'
        );
    }

    /**
     * Загружаем список из популярных целей пользователей
     */
    function loadPopular(i) {
        if (isNaN(i)) {
            i = 0;
        }
        $.get('/targets/pop_list/'+i, '', function(data){
            var s = '',c;
            for(v in data.list) {
                c = 1+parseInt(v);
                s += '<li><img src="/img/i/fintarget.jpg" alt="" /><span class="num">'
                    +c+'.</span><a href="#" class="name">'
                    +data.list[v]['title']+'</a><a href="#" class="join">Присоединиться</a>'
                    +'<div class="statistics"><div><span class="green">'
                    +data.list[v]['count']+'</span> Последователей<br/>'
                    +'<span class="red">'+data.list[v]['cl']+'</span> Достигло цель</div></div></li>';
            }
            
            $('ul.popularobject').empty().append(s);
        }, 'json');
    }
    
    loadPopular();
    ////////////////////////////////////////////////hash api
    s = location.hash;
    if (s=='#add') {
        $("div.financobject_block .add span").click()
    }
    if(s.substr(0,6)=='#edit/') {
        $('.object[tid="'+ s.substr(6) +'"] .f_f_edit').click();
        return false;
    }

    ///////////////////////////////////////////////////////////
    function tofloat(s)
    {
        if (s != null) {
            return s.replace(/[ ]/gi, '');
        } else {
            return '';
        }
    }
});

    