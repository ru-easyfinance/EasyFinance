// {* $Id$ *}
$(document).ready(function(){
    // Init
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
    $("#button_add_target").click(function(){
        clearForm();
        $('#dialog_event').dialog('open');
    });
    $("input[type=button]#button_save_cancel").click(function(){
        if (confirm("Отказаться от сохранения цели?")) {
            clearForm();
            $('#dialog_event').dialog('close');
        }
    });
    // Присоединиться к популярной финансовой цели
    $("[action=join]").click(function(event){
        clearForm();
        $('#category').val($(event.target).parents("td").prev().prev().text());
        $('#title').val(title = $(event.target).parents("td").prev(":first").text());
        $('#dialog_event').dialog('open');
    });
    // Редактируем одну из наших целей
    $("[action=edit]").click(function(event){
        clearForm();
        var id = $(event.target).parents("tr[target_id]").attr("target_id");
        $.getJSON('/targets/get/'+id,'',function(data) {
            alert(data);
            $('#dialog_event').dialog('open');
        });
        
        //@TODO Дописать подбор параметров

    });
    $("[action=del]").click(function(event){
        var title = $(event.target).parents("tr[target_id]").children().eq(1).text(); //TODO Упростить
        if (confirm("Вы уверены, что хотите удалить финансовую цель '"+title+"'?")) {
            $.post('/targets/del/', {
                id:$(event.target).parents("tr[target_id]").attr("target_id")
            }, function(){
                $(event.target).parents("tr[target_id]").remove();
            }, 'json');
        }
    });
    $("#button_save_target").click(function(){
        //TODO Проверяем валидность и сабмитим
        $.post('/targets/add/', {
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
            $('#dialog_event').dialog('close');
        }, 'json')
    });
    $("input#url").change(function(event){
        $("#url_click").attr("href",$("input#url").val());
    });

    /**
     * Очищает форму для добавления финансовой цели
     */
    function clearForm() {

    }

    $("#dialog_event").dialog({
        bgiframe: true,
        autoOpen: false,
        width: 450,
        modal: true,
        buttons: {
            'Сохранить': function() {
                //@TODO Проверить вводимые значения
                $.post(
                    $('form').attr('action'),
                    {
                        id: $('form #id').attr('value'),
                        title: $('form #title').attr('value'),
                        start: $('form #date_start').attr('value'),
                        end: $('form #date_end').attr('value'),
                        comment: $('form #comment').attr('value'),
                    }, function(data, textStatus){
                        for (var v in data) {
                            //@FIXME Дописать обработку ошибок и подсветку полей с ошибками
                            alert('Ошибка в ' + v);
                        }
                        // В случае успешного добавления, закрываем диалог и обновляем календарь
                        if (data.length == 0) {
                            $('#dialog_event').dialog('close');
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
                            $('#dialog_event').dialog('close');
                            $('#calendar').fullCalendar('refresh');
                        }, 'json');
                    } else {
                        $.post('/calendar/del/', {id:$('form #key').val(), chain: false}, function(){
                            $('#dialog_event').dialog('close');
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
});