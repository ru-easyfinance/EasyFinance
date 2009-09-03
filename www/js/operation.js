// {* $Id: operation.js 137 2009-08-10 16:00:50Z ukko $ *}
$(function(document) {
    var operationList;
    // Init
    $('#amount').calculator({
        layout: [$.calculator.CLOSE+$.calculator.ERASE+$.calculator.USE,
                'MR_7_8_9_-' + $.calculator.UNDO,
                'MS_4_5_6_*' + $.calculator.PERCENT ,
                'M+_1_2_3_/' + $.calculator.HALF_SPACE,
                'MC_0_.' + $.calculator.PLUS_MINUS +'_+'+ $.calculator.EQUALS],
        showOn: 'focus' //opbutton
        //,buttonImageOnly: true
        //,buttonImage: '/img/calculator.png'
    });
    $("#date, #dateFrom, #dateTo").datepicker({dateFormat: 'dd.mm.yy'});//+

    // Bind
    $('#btn_Save').click(function(){ saveOperation(); })
    $('#btn_Cancel').click(function(){ clearForm() });
    $('#btn_ReloadData').click(function(){ loadOperationList(); });

    $('#amount,#currency').change(function(){
        if ($('#type').val() == 2) {
            //@TODO Дописать округление
            var result = Math.round($('#amount').val() / $('#currency').val());
            if (!isNaN(result) && result != 'Infinity') {
                $("#convertSumCurrency").html("конвертация: "+result);
            }
        }
    });
    $('#account').change(function(){
        changeAccountForTransfer();
        loadOperationList();
    });
    $('#AccountForTransfer').change( function(){ changeAccountForTransfer(); });
    $('#type').change(function(){ changeTypeOperation('add'); });
    $('#target').change(function(){
        $("span.currency").each(function(){
            $(this).text(" "+$("#target :selected").attr("currency"));
        });
        $("#amount_done").text(formatCurrency($("#target :selected").attr("amount_done")));
        $("#amount_target").text(formatCurrency($("#target :selected").attr("amount")));
        $("#percent_done").text(formatCurrency($("#target :selected").attr("percent_done")));
        $("#forecast_done").text(formatCurrency($("#target_sel :selected").attr("forecast_done")));
    });

    // Autoload
    loadOperationList();

    /**
     * Загружает список всех операций (с фильтром)
     * @return void
     */
    function loadOperationList() {
        $.get('/operation/listOperations/',{
            dateFrom: $('#dateFrom').val(),
            dateTo: $('#dateTo').val(),
            category: $('#cat_filtr :selected').val(),
            account: $('#account :selected').val()
        }, function(data) {
            delete operationList;
            operationList = $.extend(data);
            var tr = '';
            if (data != null) {
                // Собираем данные для заполнения в таблицу
                for(v in data) {
                    tg = (data[v].tags!=null) ? data[v].tags : '';
                    if (data[v].tr_id > 0) {
                        tp = 'Перевод';
                    }else if (data[v].virt == 1) {
                        tp = 'Фин.цель';
                    } else {
                        if (data[v].drain == 1) {
                            tp = 'Расход';
                        } else {
                            tp = 'Доход';
                        }
                    }
                    tr += "<tr value='"+data[v].id+"'><td class='check'><input type='checkbox' /></td>"
                        + '<td class="light"><a href="#">' + tp + '</a></td>'
                        + '<td class="summ"><span><b>'+data[v].money+'</b></span></td>'
                        + '<td class="light"><span>'+data[v].date+'</span></td>'
                        + '<td class="big"><span>'+data[v].cat_name+'</span></td>'
                        + '<td class="light"><span>'+data[v].account_name+'</span></td>'
                        + '<td class="light"><span>'+tg+'</span></td>'
                        + '<td class="no_over big">'
                            +'<div class="cont"><span>'+data[v].comment+'</span><ul>'
                            +'<li class="edit"><a title="Редактировать">Редактировать</a></li>'
                            +'<li class="del"><a title="Удалить">Удалить</a></li>'
                            +'<li class="add"><a title="Копировать">Копировать</a></li>'
                            +'</ul></div>'
                        +'</td></tr>';
                }
                // Очищаем таблицу
                $('#operations_list tr:not(:first)').each(function(){
                    $(this).remove();
                });
                // Заполняем таблицу и биндим показ и скрытие тулбокса
                $('#operations_list').append(tr).find('td').live('mouseover',function(){
                    $(this).parent().find('ul').show();
                }).live('mouseout', function(){
                    $(this).parent().find('ul').hide();
                });
                // Биндим щелчки на кнопках тулбокса (править, удалить, копировать)
                $('#operations_list a').unbind('click.panel').bind('click.panel', function(){
                    if ($(this).parent().attr('class') == 'edit') {
                        fillForm(operationList[$(this).closest('tr').attr('value')]);
                        $('form').attr('action','/operation/edit/');
                        $(document).scrollTop(300);
                    } else if($(this).parent().attr('class') == 'del') {
                        deleteOperation($(this).closest('tr').attr('value'), $(this).closest('tr'));
                    } else if($(this).parent().attr('class') == 'add') {
                        fillForm(operationList[$(this).closest('tr').attr('value')]);
                        $(this).closest('form').attr('action','/operation/add/');
                        $('#date').datepicker('setDate', new Date() );
                        $(document).scrollTop(300);
                    }
                })
            }
        },'json');
        // Загружаем теги
        $.get('/tags/getTags/', '', function(data) {
            $('input#tags').tagSuggest({
                tags: data,
                separator: ', '
            });
        }, 'json');
    }

    /**
     * Добавляет новую операцию
     * @return void
     */
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
        }, function(data, textStatus){
            for (var v in data) {
                //@FIXME Дописать обработку ошибок и подсветку полей с ошибками
                alert('Ошибка в ' + v);
            }
            // В случае успешного добавления, закрываем диалог и обновляем календарь
            if (data.length == 0) {
                clearForm();
                loadOperationList();
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

    /**
     * Проверяет валидность введённых данных
     */
    function validateForm() {
        $error = '';
        if (isNaN(parseFloat($('#amount').val()))){
            alert('Вы ввели неверное значение в поле "сумма"!');
            return false;
        }

        if ($('#type') == 4) {
            //@FIXME Написать обновление финцелей
            amount = parseFloat($("#target_sel option:selected").attr("amount")); $("#amount").text(amount);
            amount_done = parseFloat($("#target_sel option:selected").attr("amount_done")); $("#amount_done").text(amount_done);
            if ((amount_done + parseFloat($("#amount").val())) >= amount) {
                if (confirm('Закрыть финансовую цель?')) {
                    $("#close").attr("checked","checked");
                }
            }
        }
        return true;
    }

    /**
     * Очищает форму
     * @return void
     */
    function clearForm() {
        $('#type,#category,#target').val(0);
        $('#amount,#AccountForTransfer,#comment,#tags,#date').val('');
        $('#amount_target,#amount_done,#forecast_done,#percent_done').text('');
        $('#close').removeAttr('checked');
        $('form').attr('action','/operation/add/');
        $('#type').change();
    }

    /**
     * Функция заполняет форму данными c массива
     * @param data данные для заполнения
     */
    function fillForm(data) {
        clearForm();
        //"drain":"1",
        //"comment":"sadf asdfasdf as",
        //"tags":"",
        //"cat_name":"\u0414\u044b\u0440\u043a\u0430 3",
        //"cat_parent":"6",
        //"account_name":"\u041d\u0430\u043b\u0438\u043a\u0438",
        //"account_currency_id":"1",
        //"cat_transfer":"1"},
        $('#id').val(data.id);
        $('#account').val(data.account_id);
//        if (date.drain) {
//            $('#type').val(1);
//        } else {
//            $('#type').val(0);
//        }
        //$('#type').val(data.type);
        $('#amount').val(data.money);
        $('#category').val(data.cat_id);
        //$('#target').val(data.);
        //$('#close').val(data.);
        $('#AccountForTransfer').val(data.transfer);
        $('#date').val(data.date);
        $('#tags').val(data.tags);
        $('#comment').val(data.comment);
    }

    /**
     * При переводе со счёта на счёт, проверяем валюты
     * @return void
     */
    function changeAccountForTransfer() {
        //@TODO можно оптимизировать процедуру, и не отсылать данные на сервер, если у нас одинаковая валюта на счетах
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

    /**
     * При изменении типа операции
     */
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
  
    /**
     * Удаляет операцию
     * @param id int
     * @return bool
     */
    function deleteOperation(id, tr) {
        if (!confirm("Вы действительно хотите удалить эту запись?")) {
            return false;
        }
        $.post('/operation/del/', {
                id : id
            }, function(data) {
                delete operationList[id];
                $(tr).remove();
            }, 'json');
    }

});

function updateOperation() {
    onSumChangeEdit();
    var type = document.getElementById("type_edit").value;
    var objSel = document.getElementById("selectAccount");
    var bill_id = objSel.options[objSel.selectedIndex].value;
    var objSelCat = document.getElementById("cat_id_old_edit");
    var cat_id = objSelCat.options[objSelCat.selectedIndex].value;
    var sum = document.getElementById("pos_mc_edit").value;
    var dateTo = document.getElementById("sel1").value;
    var comment = document.getElementById("comment_edit").value;
    var toAccount = document.getElementById("selectAccountForTransferEdit").value;
    if (document.getElementById("currency_edit"))
    {
        var currency = document.getElementById("currency_edit").value;
    }
    var m_id = document.getElementById("m_id").value;
    var modules = "operation";
    var action = "updateOperation";
    if (type == '2'){
        action = "updateTransfer";
    }else if (type == '4') {
        action = "updateTargetOperation";
        if (!checkTarget('edit')){
            return false;
        }
    }
    target_sel = $("#target_sel_ed option:selected").val();
    if (sum == 0 || sum == 'NaN'){
        alert('Вы ввели неверное значение в поле "сумма"!');return false;
    }
    if (!validateForm()){
        return false;
    }
    close = $("#close_ed").attr('checked')?1:0;
    //alert(type+'-'+action+'-'+m_id+'-'+currency+'-'+toAccount);
    //return false;
    $.get('/index.php',{
        modules:modules,
        action:action,
        id:m_id,
        a:bill_id,
        cat_id:cat_id,
        dateTo:dateTo,
        type:type,
        comment:comment,
        sum:sum,
        toAccount:toAccount,
        currency:currency,
        target_id:target_sel,
        close:close
    },changeOperationList);
    visibleMessage('Операция редактируется, подождите несколько секунд...');
}



function deleteTargetOperation(id, tr_id) {
    if (!confirm("Вы действительно хотите удалить эту запись?")) {
        return false;
    }
    $("#addOperation").hide();$("#editOperation").hide();
    var modules = "operation";
    var action = "deleteTargetOperation";
    $.get('/index.php',{
        modules:modules,
        action : action,
        id: id,
        tr_id:0
    },changeOperationList);
    visibleMessage('Операция удаляется, подождите несколько секунд...');
}

function deleteOperationTransfer(id) {
    if (!confirm("Вы действительно хотите удалить этот перевод?")) {
        return false;
    }
    $("#addOperation").hide();$("#editOperation").hide();
    var modules = "operation";
    var action = "deleteOperation";
    $.get('/index.php',{
        modules: modules,
        action : action,
        id: 0,
        tr_id:id
    },changeOperationList);
    visibleMessage('Перевод удаляется, подождите несколько секунд...');
}

function editOperation(id) {
    $.get('/index.php',{
        id:id
    }, function() {
        $("#addOperation").hide();
        $("#editOperation").html();
        $("#editOperation").show();
        $("#editOperation").show();
        changeTypeOperation('edit');
        scrollTo(0,0);
    });
}

function editTargetOperation(id) {
    var modules = "operation";
    var action = "editTargetOperation";
    $.get('/index.php',{
        modules:modules,
        action:action,
        id:id
    },operationAfterEdit);
}
