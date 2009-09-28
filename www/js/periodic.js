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
        showOn: 'focus' //opbutton
        //,buttonImageOnly: true
        //,buttonImage: '/img/calculator.png'
    });*/
    $('#add_periodic').click(function(){$('#add_form div.form_block').toggle()});
    $('#date').datepicker();
    $('#btnSave').click(function(){
        saveOperation();
    });
    $('#btnCancel').click(function(){
        clearForm();
        $('#add_form div.form_block').hide();
    });
    
    for(i = 1; i < 32; i++){
        $('form #counts').append('<option>'+i+'</option>').val(i);
    }
    $('#counts').val('');

    $('#infinity, #spinfinity').click(function(){
        $('#counts').attr('disabled','disabled');
        $('#infinity').attr('checked', 'checked');
        $('#count').removeAttr('checked');
    });
    $('#count,#spcount').click(function(){
        $('#counts').removeAttr('disabled');
        $('#infinity').removeAttr('checked');
        $('#count').attr('checked', 'checked');
    });
    $('li.edit a').live('click',function(){
        fillForm($(this).closest('tr').attr('id'));
        $('#form').attr('action', '/periodic/edit/');
    });
    $('li.add a').live('click',function(){
        fillForm($(this).closest('tr').attr('id'));
        $('#form').attr('action', '/periodic/add/');
        $('#id,#date').val('');
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
    $('#tab1 tbody tr').live('mouseout', function(){

    });
    $('#tab1 tbody tr').live('mouseover', function() {
        $(this).filter('ul').show();
    });

    // FUNCTIONS

    /**
     * Загружает список периодических транзакций (правила)
     */
    function loadPeriodic() {
        $.get('/periodic/getList/', '', function(data){
            pobj = {};
            pobj = data;
            c = '';
            for(var id in data) {
                cat = $('#ca_'+data[id]['category']).attr('title')
                cat = (!cat)?'нет':cat;
                c += '<tr id="'+id+'"><td class="chk"><input type="checkbox" /></td>'
                    +'<td>'+data[id]['date']+'</td>'
                    +'<td>'+data[id]['title']+'</td>'
                    +'<td>'+cat+'</td>'
                    +'<td>'+$('#account [value='+data[id]['account']+']').attr('title')+'</td>'
                    +'<td>'+$('#repeat [value='+data[id]['repeat']+']').text()+'</td>'
                    +'<td>'+'<div class="cont">'+ data[id]['amount']
                           +'<ul><li class="edit"><a title="Редактировать">Редактировать</a></li>'
                            +'<li class="del"><a title="Удалить">Удалить</a></li>'
                            +'<li class="add"><a title="Добавить">Добавить</a></li></ul></div></td></tr>';
            }
            $('#tab1 tbody').empty().append(c);
            $('.operation_list').jScrollPane();
        }, 'json');
    }

    function saveOperation() {
        if (checkForm()) {
            $.post($('#form').attr('action'), {
                id: $('#id').val(),
                title: $('#title').val(),
                amount: tofloat($('#amount').val()),
                date: $('#date').val(),
                comment: $('#comment').val(),
                category: $('#category').val(),
                type: $('#type').val(),
                account: tofloat($('#account').val()),
                repeat: $('#repeat').val(),
                counts: $('#counts').val(),
                infinity: $('[name=count]:checked').val()
            }, function(data){
                if (data.length != 0) {
                    for (var v in data) {
                        alert(data[v]);
                    }
                } else {
                    $('#btnCancel').click();
                    loadPeriodic();
                }
            }, 'json');
        }
    }

    function clearForm() {
        $('#title,#amount,#date,#comment,#category,#type,#account,#repeat,#counts').val('');
        $('#infinity').click();
    }

    function fillForm(id) {
        $('#id').val(pobj[id]['id']);
        $('#title').val(pobj[id]['title']);
        $('#amount').val(pobj[id]['amount']);
        $('#date').val(pobj[id]['date']);
        $('#comment').val(pobj[id]['comment']);
        $('#category').val(pobj[id]['category']);
        $('#type').val(pobj[id]['type']);
        $('#account').val(pobj[id]['account']);
        $('#repeat').val(pobj[id]['repeat']);
        $('#counts').val(pobj[id]['counts']);
        if (pobj[id]['infinity'] == 0) {
            $('#infinity').click();
        } else {
            $('#count').click();
        }
        $('#add_form div.form_block').show();
        $(document).scrollTop(300);
    }

    function checkForm() {
        return true;
    }
});