// {* $Id: targets.js 128 2009-08-07 15:20:49Z ukko $ *}
$(document).ready(function(){
// <editor-fold defaultstate="collapsed" desc=" Инициализация объектов ">
    $('#amount,#amountf').live('keyup',function(e) {
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
    $(".join").live('click', function(){
        clearForm();
        $('#title').val($(this).closest('li').find('a:first').html());
        $('#tpopup form').attr('action','/targets/add/');
        $('#tpopup').dialog('open');
        return false;
    });

    // Редактируем одну из наших целей
    $(".f_f_edit").live('click', function(){
        f = $(this).closest('.object');
        clearForm();
        $('#key').val(f.attr('tid'));
        $('#type').val(f.attr('type'));
        $('#title').val(f.attr('title'));
        $('#amount').val(f.attr('amount'));
        $('#start').val(f.attr('start'));
        $('#end').val(f.attr('end'));
        $('#photo').val(f.attr('photo'));
        $('#url').val(f.attr('url'));
        $('#comment').val(f.attr('comment'));
        $('#account').val(f.attr('account'));
        $('#visible').val(f.attr('visible'));
        $('#tpopup').dialog('open');
        return false;
    });

    // Удаляем цель
    $(".f_f_del").live('click', function(){
        o = $(this).closest('.object');
        if (confirm("Вы уверены, что хотите удалить финансовую цель '"+$(this).closest('.object .descr a').text()+"'?")) {
            $.post('/targets/del/', {
                id: o.attr('tid')
            }, function(){
                o.remove();
            }, 'json');
            return false;
        }
    });

    // Копируем
    $('.f_f_copy').live('click', function(){
        f = $(this).closest('.object');
        clearForm();
        $('#type').val(f.attr('type'));
        $('#title').val(f.attr('title'));
        $('#photo').val(f.attr('photo'));
        $('#url').val(f.attr('url'));
        $('#comment').val(f.attr('comment'));
        $('#account').val(f.attr('account'));
        $('#visible').val(f.attr('visible'));
        $('#tpopup').dialog('open');
        return false;
    });

    // Загружаем и показываем ВСЕ цели пользователя
    $('div.show_all span').click(function() {
        $.get('/targets/user_list/', '', function(data){
            s = '';
            for(v in data) {
                s += '<div class="object"><div class="ban"></div>'
                    +'<div class="descr">';
                    s += (data[v]['photo']!='')? '<img src="/img/i/fintarget1.jpg" alt="" />' : '<img src="/img/images/pic2.gif" alt="" />';
                        s += '<a href="#">'+data[v]['title']+'</a>'+data[v]['comment']
						+'</div><div class="indicator_block"><div class="money">'
						+data[v]['amount']+' руб.<br /><span>'
                        +data[v]['amount_done']+' руб.</span></div><div class="indicator">'
                        +'<div style="width:'+data[v]['percent_done']+'%;"><span>'+data[v]['percent_done']
                        +'%</span></div></div></div><div class="date">Целевая дата: '
                        +data[v]['date_end']+' &nbsp;&nbsp;&nbsp;</div><ul><li><a href="#" class="f_f_edit">редактировать</a></li>'
                        +'<li><a href="#" class="f_f_copy">копировать</a></li><li><a href="#" class="f_f_del">удалить</a></li></ul></div>';
            }
            $('div.object,div.show_all').remove();
            $('div.financobject_block').append(s);

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
        $('#id,#type,#category,#title,#amount,#start,#end,#photo,#url,#comment,#account,#visible').val('');
    }

    /**
     * Заполняет поля формы результатами из массива data
     * @param array data Массив с данными финансовой цели
     * @return void
     */
    function fillForm(data) {
        $('#key').val(data.id);
        $('#category').val(data.category); //
        $('#title').val(data.title);
        $('#type').val(data.type); //
        $('#amount').val(data.amount);
        $('#start').val(data.start); //
        $('#end').val(data.end); //
        $('#visible').val(data.visible); //
        $('#photo').val(data.photo);
        $('#url').val(data.url);
        $('#comment').val(data.comment);
        $('#account').val(data.account);
    }

    /**
     * Cохранение объекта
     */
    function saveTarget() {
        //TODO Проверяем валидность и сабмитим
        $.post(
            $('#tpopup form').attr('action'),
            {
                id       : $('#key').attr('value'),
                type     : $('#type').attr('value'),
                category : $('#category').attr('value'),
                title    : $('#title').attr('value'),
                amount   : tofloat($('#amount').val()),
                start    : $('#start').attr('value'),
                end      : $('#end').attr('value'),
                photo    : $('#photo').attr('value'),
                url      : $('#url').attr('value'),
                comment  : $('#comment').attr('value'),
                account  : $('#account').attr('value'),
                visible  : $('#visible:checked').length
            }, function(data){
                for (var v in data) {
                    //@FIXME Дописать обработку ошибок и подсветку полей с ошибками
                    alert('Ошибка в ' + v);
                }
                // В случае успешного добавления, закрываем диалог и обновляем календарь
                if (data.length == 0) {
                    $('#tpopup').dialog('close');
                }

                s = '<div class="object"><div class="ban"></div>'
                    +'<div class="descr">';
                    s += ($('#photo').attr('value'))? '<img src="/img/i/fintarget1.jpg" alt="" />' : '<img src="/img/i/fintarget1.jpg" alt="" />';
                        s += '<a href="#">'+$('#title').attr('value')+'</a>'+$('#comment').attr('value')
						+'</div><div class="indicator_block"><div class="money">'
						+$('#amount').attr('value')+' руб.<br /><span>'
                        +'0 руб.</span></div><div class="indicator">'
                        +'<div style="width:0%;"><span>0'
                        +'%</span></div></div></div><div class="date">Целевая дата: '
                        +$('#end').attr('value')+' &nbsp;&nbsp;&nbsp;</div><ul><li><a href="#" class="f_f_edit">редактировать</a></li>'
                        +'<li><a href="#" class="f_f_copy">копировать</a></li><li><a href="#" class="f_f_del">удалить</a></li></ul></div>';

                $('div.financobject_block').append(s);
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
            s = '';
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
    if (s=='#add')
    {
        $("div.financobject_block .add span").click()
    }
    if(s.substr(0,6)=='#edit/')
    {
        var id = s.substr(6)
        
        var f = $('.object[tid="'+id+'"]');
        clearForm();
        $('#key').val(f.attr('tid'));
        $('#type').val(f.attr('type'));
        $('#title').val(f.attr('title'));
        $('#amount').val(f.attr('amount'));
        $('#start').val(f.attr('start'));
        $('#end').val(f.attr('end'));
        $('#photo').val(f.attr('photo'));
        $('#url').val(f.attr('url'));
        $('#comment').val(f.attr('comment'));
        $('#account').val(f.attr('account'));
        $('#visible').val(f.attr('visible'));
        $('#tpopup').dialog('open');
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

    