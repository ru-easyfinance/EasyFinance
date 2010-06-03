// {* $Id: targets.js 128 2009-08-07 15:20:49Z ukko $ *}
$(document).ready(function(){
    $.get('/targets/user_list/', '', function(data){
            //showall = 1;// показать все
            loadTargets(data);
        }, 'json');
    var showall = 0;// показать все .
// <editor-fold defaultstate="collapsed" desc=" Инициализация объектов ">
    $('#tg_amount,#amountf').live('keyup',function(e) {
        FloatFormat(this,String.fromCharCode(e.which) + $(this).val())
    })

    $("#start,#end").datepicker({dateFormat: 'dd.mm.yy'});

    // Добавить фин.цель
    $("div.financobject_block .add span").click(function(){
        clearForm();
        _urlAction = '/targets/add/';
        $('#tpopup').dialog('open');
        $('#visible').attr('checked','checked');
    });

    // Присоединиться к популярной финансовой цели
    $(".join, .name").live('click', function(){
        var f = $(this).closest('li');
        $('#targets_category').val(f.attr('category'));
        $('#name').val($(this).closest('li').find('a:first').html());
        _urlAction = '/targets/add/';
        $('#tpopup').dialog('open');
        $('#visible').attr('checked','checked');
        return false;
    });



    // Редактируем одну из наших целей
    $(".f_f_edit,div.descr a").live('click', function(){
       var f = $(this).closest('.object');
        clearForm();

        // #797. disable account changing
        $('#account').attr("disabled", "disabled");

        $('#key').val(f.attr('tid'));
        $('#type').val(f.attr('type'));
        $('#targets_category').val(f.attr('category'));
        a = f.attr('name').replace("%20"," ");
        for (i=5; i>0; i--)
        a = a.replace("%20"," ");
        $('#name').val(a);
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
        _urlAction = '/targets/edit/';
        $('#tpopup').dialog('open');
        return false;
    });

    //$('#targets_category').empty();
    for (var v in res['targets_category']) {
        $('#targets_category').append('<option value="'+v+'">'+res['targets_category'][v]+'</option>');
        //$('#targets_category').val(3);
    }

    // Удаляем цель
    $(".f_f_del").live('click', function(){
        var o = $(this).closest('.object');
        var deletedId = o.attr('tid');
        if (confirm("Вы уверены, что хотите удалить финансовую цель '"+$(this).closest('.object .descr a').text()+"'?")) {
            $.post('/targets/del/', {
                id: deletedId
            }, function(data){
                // удаляем из списка фин. целей
                for (var key in res.user_targets) {
                    if (res.user_targets[key].id == deletedId) {
                        delete res.user_targets[key];
                    }
                }

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
        a = f.attr('name').replace("%20"," ");
        for (i=5; i>0; i--)
        a = a.replace("%20"," ");
        $('#name').val(a);
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
        _urlAction = '/targets/add/';
        $('#tpopup').dialog('open');
        return false;
    });

    // Загружаем и показываем ВСЕ цели пользователя
    $('div.show_all span').click(function() {
        $.get('/targets/user_list/', '', function(data){
            showall = 1;// показать все
            loadTargets(data);
            $('div.show_all').hide();
        }, 'json');
    });

    // Диалог редактирования финансовой цели
    $("#tpopup").dialog({
        bgiframe: true,
        autoOpen: false,
        width: 450,
        buttons: {
            'Отмена': function() {
                clearForm();
                $('#tpopup').dialog('close');
            },
            'Сохранить': function() {
                saveTarget();
            }
        }
    });

// </editor-fold>

// <editor-fold defaultstate="collapsed" desc=" Функции ">
    /**
     * Очищает форму для добавления финансовой цели
     */
    function clearForm() {
        $('#tid,#type,#targets_category,#name,#tg_amount,#amountf,#start,#end,#photo,#url,#comment,#account,#visible').val('');
        $('#account').removeAttr("disabled");
    }

    /**
     * Показывает финцели пользователя
     */
    function loadTargets(data) {
        res.user_targets = data;

        var s = '';
        for(v in data) {
            if ( (data[v]["done"] == 0) || showall){
            a = data[v]['title'].replace(" ","%20");
            for (i=5; i>0; i--)
            a = a.replace(" ","%20");
            s += '<div class="object" tid='+data[v]["id"]+' category='+data[v]["category"]+  ' name='+a+' amount=' +data[v]["amount"]+ ' start='+data[v]["start"]+' end='+data[v]["end"]+' money='+data[v]["money"]+' account='+data[v]["account"]+ ' visible='+data[v]["visible"]+' comment=' + data[v]["comment"] + '><div class="ban"></div>'
                +'<div class="descr">';
                //alert(data[v]['category']);
                targetcurrency = easyFinance.models.accounts.getAccountCurrency( data[ v ]["account"]).text;
                if (data[v]['category']==2)
                    s += '<img src="/img/i/avto.png" alt="" />'
                else if (data[v]['category']==3)
                    s += '<img src="/img/i/rest.png" alt="" />'
                else if (data[v]['category']==1)
                    s += '<img src="/img/i/home.png" alt="" />'
                else if (data[v]['category']==6)
                    s += '<img src="/img/i/wedd.png" alt="" />'
                else if (data[v]['category']==7)
                    s += '<img src="/img/i/bitv.png" alt="" />'
                else if (data[v]['category']==8)
                    s += '<img src="/img/i/comp.png" alt="" />'
                else
                s += (data[v]['photo']!='')? '<img src="/img/i/fintarget1.jpg" alt="" />' : '<img src="/img/images/pic2.gif" alt="" />';
                    s += '<a href="#">'+data[v]['title']+'</a>'+data[v]['comment']
                    +'</div><div class="indicator_block"><div class="money">'
                    +data[v]['amount'] + ' '+ targetcurrency +' <br /><span>'
                    +data[v]['amount_done'] + ' ' + targetcurrency +' </span></div><div class="indicator">'
                    +'<div style="width:'+data[v]['percent_done']+'%;"><span>'+data[v]['percent_done']
                    +'%</span></div></div></div><div class="date">Целевая дата: '
                    +data[v]['end']+' &nbsp;&nbsp;&nbsp;</div><ul><li><a href="#" class="f_f_edit">редактировать</a></li>'
                    +'<li><a href="#" class="f_f_copy">копировать</a></li><li><a href="#" class="f_f_del">удалить</a></li></ul></div>';
            }
        }
        MakeOperation();
        //$('div.object').remove();
        $('div.object').remove();
        if( s != '')
            $(s).prependTo('div.financobject_block div.content');
       // $('div.show_all').show();
    }
    /**
     * Заполняет поля формы результатами из массива data
     * @param data Массив с данными финансовой цели
     * @return void
     */
    function fillForm(data) {
        $('#tkey').val(data.id);
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
        _urlAction = '/targets/edit/';
    }

    function ValidateForm(){
        var che = 1;
        var str = '';
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
    }

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
            _urlAction,
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
                $('#tpopup').dialog('close');
                $.jGrowl("Финансовая цель сохранена", {theme: 'green'});

                // #1455. обновляем данные по фин. целям
                loadTargets(data['user_targets']);

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
                if (data.list[v]['cat_id']==1)
                    s += '<li category='+data.list[v]['cat_id']+'><img src="/img/i/home.png"  alt="" /><span class="num">'
                else if (data.list[v]['cat_id']==2)
                    s += '<li category='+data.list[v]['cat_id']+'><img src="/img/i/avto.png"  alt="" /><span class="num">'
                else if (data.list[v]['cat_id']==3)
                    s += '<li category='+data.list[v]['cat_id']+'><img src="/img/i/rest.png"  alt="" /><span class="num">'
                else if (data.list[v]['cat_id']==6)
                    s += '<li category='+data.list[v]['cat_id']+'><img src="/img/i/wedd.png"  alt="" /><span class="num">'
                else if (data.list[v]['cat_id']==7)
                    s += '<li category='+data.list[v]['cat_id']+'><img src="/img/i/bitv.png"  alt="" /><span class="num">'
                else if (data.list[v]['cat_id']==8)
                    s += '<li category='+data.list[v]['cat_id']+'><img src="/img/i/comp.png"  alt="" /><span class="num">'
                else
                    s += '<li category='+data.list[v]['cat_id']+'><img src="/img/i/fintarget.jpg"  alt="" /><span class="num">'
                    s += c+'.</span><a href="#" class="name">'
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
        //alert (s.substr(6));
        $('.object[tid="'+ s.substr(6) +'"] .f_f_edit').click();
        return false;
    }



    //функция проверяет есть ли у пользователя закрытые цели. !но по которым физической операции
    //расхода денег на категорию он не совершил. ну и предлагает сделать .
//    MakeOperation();


    ///////////////////////////////////////////////////////////
    function tofloat(s)
    {
        if (s != null) {
            s = s.toString();
            return s.replace(/[ ]/gi, '');
        } else {
            return '';
        }
    }
});

