// {* $Id: periodic.js 108 2009-07-23 10:21:50Z ukko $ *}
$(document).ready(function() {
    var pobj;
    
    loadPeriodic();

    // INIT
    /*$('#amount').calculator({
            layout: [$.calculator.CLOSE+$.calculator.ERASE+$.calculator.USE,
                    'MR_7_8_9_-' + $.calculator.UNDO,
                    'MS_4_5_6_*' + $.calculator.PERCENT ,
                    'M+_1_2_3_/' + $.calculator.HALF_SPACE,
                    'MC_0_.' + $.calculator.PLUS_MINUS +'_+'+ $.calculator.EQUALS],
            showOn: 'button',
            buttonImageOnly: true,
            buttonImage: '/img/i/unordered.gif' //opbutton
        });*/
    $('#add_periodic').click(function(){
        $('#op_addtocalendar_but').click();
        $('#periodic').click();
    });

    $('li.del a').live('click',function(){
        if (confirm('Удалить регулярную транзакцию и все её события?')) {
            tr = $(this).closest('tr');
            $.post('/periodic/del/', 
                {id: $(tr).attr('id')},
                function () {
                    $(tr).remove();
                }
            ,'json');
        }
    });
    
    $('li.edit a').live('click',function(){
        clearForm();
        $('#op_addtocalendar_but').click();
        $('#periodic').click();
        fillForm($(this).closest('tr').attr('id'));
    });

    $('#tab1 tbody tr').live('dblclick', function(){
        clearForm();
        $('#op_addtocalendar_but').click();
        $('#periodic').click();
        fillForm($(this).closest('tr').attr('id'));
    });

    $('li.add a').live('click',function(){
        alert('add');
    });
    
    $('#tab1 tbody tr').live('mouseout', function(){
        $(this).removeClass('act');
    });
    $('#tab1 tbody tr').live('mouseover', function() {
        $(this).addClass('act');
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
                c += '<tr id="'+id+'" class="item"><td class="chk"><input type="checkbox"/></td>'
                    +'<td>'+data[id]['date']+'</td>'
                    +'<td>'+data[id]['title']+'</td>'
                    +'<td>'+cat+'</td>'
                    +'<td>'+$('#op_account [value='+data[id]['account']+']').attr('title')+'</td>'
                    +'<td>'+$('#repeat [value='+data[id]['repeat']+']').text()+'</td>'
                    +'<td class="mark no_over" style="display: table-cell;">'+'<div class="cont">'+ data[id]['amount']
                           +'<ul style="z-index: 100;">'
                            +'<li class="edit"><a title="Редактировать">Редактировать</a></li>'
                            +'<li class="del"><a title="Удалить">Удалить</a></li>'
                            +'<li class="add"><a title="Добавить">Добавить</a></li></ul></div></td></tr>';
            }
            $('#tab1 tbody').html(c);
            $('.operation_list').jScrollPane();
        }, 'json');
    }

    function saveOperation() {
        if (checkForm()) {
            $.post($('#form').attr('action'), {
                id: $('#cal_key').val(),
                title: $('#cal_title').val(),
                amount: tofloat($('#cal_amount').val()),
                date: $('#cal_date').val(),
                comment: $('#cal_comment').val(),
                category: $('#cal_category').val(),
                type: $('#type').val(),
                account: tofloat($('#cal_account').val()),
                repeat: $('#cal_repeat').val(),
                counts: $('#cal_counts').val(),
                infinity: $('[name=count]:checked').val()
            }, function(data){

                if (data.length != 0) {
                    m = 'Не заполнены:\n';
                    for (var v in data) {
                        m += data[v]+"\n";
                    }
                    $.jGrowl(m, {theme: 'red',sticky: true});
                } else {
                    $('#btnCancel').click();
                    loadPeriodic();
                }
            }, 'json');
        }
    }

    function clearForm() {

    }

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

    function checkForm() {
        return true;
    }
});