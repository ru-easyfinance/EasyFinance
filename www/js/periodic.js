// {* $Id: periodic.js 108 2009-07-23 10:21:50Z ukko $ *}
$(document).ready(function() {
    var pobj;
    loadPeriodic();

    // BIND
    /**
     * При добавлении новой транзакции
     */
    $('#add_periodic').click(function(){
        $('#op_addtocalendar_but').click();
        $('#periodic').click();
        $('#cal_href').val('/periodic/add/');
    });

    /**
     *
     * События Всплывающей панели инструментов
     *
     */
    // Удалить
    $('li.del a').live('click',function(){
        if (confirm('Удалить регулярную транзакцию и все её события?')) {
            tr = $(this).closest('tr');
            $.post('/periodic/del/', 
            {
                id: $(tr).attr('id')
                },
            function () {
                $(tr).remove();
            }
            ,'json');
        }
    });
    // Редактировать
    $('li.edit a').live('click',function(){
        $('#op_addtocalendar_but').click();
        $('#periodic').click();
        fillForm($(this).closest('tr').attr('id'));
        $('#cal_href').val('/periodic/edit/');
        
    });
    // Добавить
    $('li.add a').live('click',function(){
        $('#op_addtocalendar_but').click();
        $('#periodic').click();
        fillForm($(this).closest('tr').attr('id'));
        $('#cal_key').val('');
        $('#cal_href').val('/periodic/add/');
    });
    //Показ и скрытие для всплывающей панельки
    $('div.operation_list').mousemove(function(){
        if (!$('ul:hover').length && !$('.act:hover').length) {
            $('tr.item').removeClass('act');
        }
    });
    $('#tab1 tbody tr').live('mouseover', function() {
        $('tr.item').removeClass('act');
        $(this).addClass('act');
    });

    /**
     * При двойном щелчке на таблице с шаблонами
     */
    $('#tab1 tbody tr').live('dblclick', function(){
        $('#op_addtocalendar_but').click();
        $('#periodic').click();
        fillForm($(this).closest('tr').attr('id'));
        $('#cal_href').val('/periodic/edit/');
    });

    // Биндим события для обновления списка 
    $(window).bind("saveSuccess", function(e, data){
        loadPeriodic();
    });


    // FUNCTIONS
    /**
     * Загружает список периодических транзакций (правила)
     */
    function loadPeriodic() {
        $.get('/periodic/getList/', '', function(data){
            pobj = {};
            pobj = data;
            var c = '';
            for(var id in data) {
                cat = $('#ca_'+data[id]['category']).attr('title');
                cat = (!cat)?'нет':cat;
                c += '<tr id="'+id+'" class="item">'
                //                +'<td class="chk"><input type="checkbox"/></td>'
                +'<td>'+data[id]['date']+'</td>'
                +'<td>'+data[id]['title']+'</td>'
                +'<td>'+cat+'</td>'
                +'<td>'+res['accounts'][data[id]['account']]['name'] +'</td>'
                +'<td class="mark no_over" style="display: table-cell;">'+data[id]['amount']+'<div class="cont" style="position:relative; top:-20px">'
                +'<ul style="z-index: 100;">'
                +'<li class="edit"><a title="Редактировать">Редактировать</a></li>'
                +'<li class="del"><a title="Удалить">Удалить</a></li>'
                +'<li class="add"><a title="Добавить">Добавить</a></li></ul></div></td></tr>';
            }
            $('#tab1 tbody').html(c);
            //$('.operation_list').jScrollPane();
        }, 'json');
    }

    /**
     * Заполняет форму
     */
    function fillForm(id) {
        $('#cal_key').val(pobj[id]['id']);
        $('#cal_title').val(pobj[id]['title']);
        $('#cal_amount').val(pobj[id]['amount']);
        $('#cal_date').val(pobj[id]['date']);
        $('#cal_comment').val(pobj[id]['comment']);
        $('#cal_category').val(pobj[id]['category']);
        if (pobj[id]['repeat'] > 0){
            $('.rep_type').removeAttr('checked');
            if (pobj[id]['infinity'] == 1) {
                $('.rep_type[value=1]').attr('checked','checked');
            } else if (pobj[id]['end'] != '00.00.0000') {
                $('.rep_type[value=2]').attr('checked','checked');
            } else {
                $('.rep_type[value=3]').attr('checked','checked');
            }
        }
        $('#cal_account').val(pobj[id]['account']);
        $('#cal_repeat').val(pobj[id]['repeat']).change();
        $('#cal_count').val(pobj[id]['counts']);
    }
});