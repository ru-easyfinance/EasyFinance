// {* $Id: targets.js 128 2009-08-07 15:20:49Z ukko $ *}
$(document).ready(function(){
// <editor-fold defaultstate="collapsed" desc=" Инициализация объектов ">

    $('#amount,#amountf').calculator({
        layout: [
            $.calculator.CLOSE+$.calculator.ERASE+$.calculator.USE,
            'MR_7_8_9_-' + $.calculator.UNDO,
            'MS_4_5_6_*' + $.calculator.PERCENT ,
            'M+_1_2_3_/' + $.calculator.HALF_SPACE,
            'MC_0_.' + $.calculator.PLUS_MINUS +'_+'+ $.calculator.EQUALS]
        //showOn: 'opbutton',
    });
    $('#calculator-div').css('z-index', 1005);
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
    $(".f_f_edit").click(function(){
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

    $(".f_f_del").live('click', function(){
        $(this).closest('.object').attr('tid')
        if (confirm("Вы уверены, что хотите удалить финансовую цель '"+$(this).closest('.object .descr a').text()+"'?")) {
            $.post('/targets/del/', {
                id: $(this).closest('.object').attr('tid')
            }, function(){
                $(this).closest('.object').remove();
            }, 'json');
        }
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
        bgiframe: true,
        autoOpen: false,
        width: 450,
        modal: true,
        buttons: {
            'Сохранить': function() {
                //TODO Проверяем валидность и сабмитим
                $.post(
                    $('#tpopup form').attr('action'),
                    {
                        id       : $('#key').attr('value'),
                        type     : $('#type').attr('value'),
                        category : $('#category').attr('value'),
                        title    : $('#title').attr('value'),
                        amount   : $('#amount').attr('value'),
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
                    },
                    'json'
                );
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
     * @param <array> data Массив с данными финансовой цели
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
// </editor-fold>
});