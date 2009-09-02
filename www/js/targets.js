// {* $Id: targets.js 128 2009-08-07 15:20:49Z ukko $ *}
$(document).ready(function(){

// <editor-fold defaultstate="collapsed" desc=" Инициализация объектов ">

    $('#amount').calculator({
        layout: [
            $.calculator.CLOSE+$.calculator.ERASE+$.calculator.USE,
            'MR_7_8_9_-' + $.calculator.UNDO,
            'MS_4_5_6_*' + $.calculator.PERCENT ,
            'M+_1_2_3_/' + $.calculator.HALF_SPACE,
            'MC_0_.' + $.calculator.PLUS_MINUS +'_+'+ $.calculator.EQUALS],
        showOn: 'opbutton',
        buttonImageOnly: true,
        buttonImage: '/img/calculator.png'
    });
   
    $("#start,#end").datepicker({dateFormat: 'dd.mm.yy'});

    $("div.financobject_block .add span").click(function(){
        clearForm();
        $('#popupaddobject form').attr('action','/targets/add/');
        $('#tpopup').dialog('open');
    });

    $("input[type=button]#button_save_cancel").click(function(){
        if (confirm("Отказаться от сохранения цели?")) {
            clearForm();
            $('#tpopup').dialog('close');
        }
    });

    // Присоединиться к популярной финансовой цели
    $("[action=join]").click(function(event){
        clearForm();
        $('#category').val($(event.target).parents("td").prev().prev().text());
        $('#title').val(title = $(event.target).parents("td").prev(":first").text());
        $('form#target').attr('action','/targets/add/');
        $('#tpopup').dialog('open');
    });

    // Редактируем одну из наших целей
    $("[action=edit]").click(function(event){
        clearForm();
        var id = $(event.target).parents("tr[target_id]").attr("target_id");
        $.getJSON('/targets/get/'+id,'',function(data) {
            fillForm(data);
            $('form#target').attr('action','/targets/edit/');
            $('#tpopup').dialog('open');
        });
    });

    $(".f_f_del").live('click', function(event){
        $(this).closest('.object').attr('tid')
        if (confirm("Вы уверены, что хотите удалить финансовую цель '"+$(this).closest('.object .descr a').text()+"'?")) {
            $.post('/targets/del/', {
                id: $(this).closest('.object').attr('tid')
            }, function(){
                $(this).closest('.object').remove();
            }, 'json');
        }
    });

    $("#button_save_target").click(function(){
        //TODO Проверяем валидность и сабмитим
        $.post($('form#target').attr('action'), {
            id      : $('#id').val(),
            type    : $('#type').val(),
            title   : $('#title').val(),
            amount  : $('#amount').val(),
            start   : $('#start').val(),
            end     : $('#end').val(),
            photo   : $('#photo').val(),
            url     : $('#url').val(),
            comment : $('#comment').val(),
            account : $('#account').val(),
            visible : $('#visible').val()
        }, function(){
            $('#tpopup').dialog('close');
        }, 'json')
    });

    $("input#url").change(function(event){
        $("#url_click").attr("href",$("input#url").val());
    });

    $('div.show_all span').click(function() {
        $.get('/targets/user_list/', '', function(data){
            s = '';
            for(v in data) {
                s += '<div class="object"><div class="ban"></div>'
                    +'<div class="descr">';
                    s += (data[v]['photo']!='')? '<img src="/img/images/pic6.jpg" alt="" />' : '<img src="/img/images/pic2.gif" alt="" />';
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

    $("#tpopup").dialog({
        autoOpen: false,
        width: 450,
        modal: true,
        buttons: {
            'Сохранить': function() {
                $.post(
                    $('form#target').attr('action'),
                    {
                        id       : $('form #id').attr('value'),
                        type     : $('form #type').attr('value'),
                        category : $('form #category').attr('value'),
                        title    : $('form #title').attr('value'),
                        amount   : $('form #amount').attr('value'),
                        start    : $('form #start').attr('value'),
                        end      : $('form #end').attr('value'),
                        photo    : $('form #photo').attr('value'),
                        url      : $('form #url').attr('value'),
                        comment  : $('form #comment').attr('value'),
                        account  : $('form #account').attr('value'),
                        visible  : $('form #visible:checked').length
                    }, function(data, textStatus){
                        for (var v in data) {
                            //@FIXME Дописать обработку ошибок и подсветку полей с ошибками
                            alert('Ошибка в ' + v);
                        }
                        // В случае успешного добавления, закрываем диалог и обновляем календарь
                        if (data.length == 0) {
                            $('#tpopup').dialog('close');
                        }
                    },
                    'json'
                );
            },
            'Отмена': function() {
                $(this).dialog('close');
            },
            'Удалить': function () {
                if (confirm('Удалить событие?')) {
                    if (($('form #chain').val() > 0 || el[0].date < el[0].last_date || el[0].infinity == 1) &&
                     confirm("Это событие не единично.\nУдалить цепочку последующих событий?")) {
                        $.post('/calendar/del/', {id:$('form #key').val(), chain: $('form #chain').val()}, function(){
                            $(this).dialog('close');
                            $('#calendar').fullCalendar('refresh');
                        }, 'json');
                    } else {
                        $.post('/calendar/del/', {id:$('form #key').val(), chain: false}, function(){
                            $('#tpopup').dialog('close');
                            $('#calendar').fullCalendar('refresh');
                        }, 'json')
                    }
                }
            }
        },
        close: function() {
            //alert('close');
            //allFields.val('').removeClass('ui-state-error');
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
     * @param <array> data Массив с данными финансовой цели
     * @return void
     */
    function fillForm(data) {
        $('#id').val(data.id);
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
     * 
     */
    function loadUserTargets() {

    }
// </editor-fold>
});