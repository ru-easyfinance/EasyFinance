// {* $Id: operation.js 137 2009-08-10 16:00:50Z ukko $ *}
    /*var catlast ;
    var datelast ;*/

/**
 * Функция очищает форму ввода операции
 */
function clearForm() {
    $('#op_type,#op_category,#op_target').val(0);
    $('#op_amount,#op_AccountForTransfer,#op_comment,#op_tags,#op_date').val('');

    $('span#op_amount_target').text();

    $('span#op_amount_done').text();
    $('span#op_forecast_done').text();
    $('span#op_percent_done').text();

    $('#op_close').removeAttr('checked');

    $('form').attr('action','/operation/add/');

    $('#op_type').change();
}

/**
 * Функция заполняет форму данными c массива
 * @param data данные для заполнения
 */
function fillForm(data) {
    clearForm();

    $('#op_id').val(data.id);
    $('#op_account').val(data.account_id);

    if (data.tr_id=='1') {
        // transfer
        $('#op_type').val(2);
    } else {
        if (data.virt=='1') {
            $('#op_type').val(4);
        } else {
            if (data.drain=='1') {
                $('#op_type').val(0);
            } else {
                $('#op_type').val(1);//@todo
            }
        }
    }

    //////////////////////////
    $('#op_amount').val(Math.abs(data.money));
    $('#op_category').val(data.cat_id);
    //$('#target').val(data.);
    //$('#close').val(data.);
    $('#op_AccountForTransfer').val(data.transfer);
    $('#op_date').val(data.date);
    $('#op_tags').val(data.tags);
    $('#op_comment').val(data.comment);
    $('#op_type').change();
    $(document).scrollTop(300);
}

$(document).ready(function() {
    var journalReload = function(){
        easyFinance.widgets.operationsJournal.setAccount($('#op_account :selected').val());
        $('#btn_ReloadData').click();
    }

    $('#op_btn_Save').click(journalReload);
    $('#op_account').change(journalReload);

    // загружаем журнал транзакций
    easyFinance.widgets.operationsJournal.setAccount($('#op_account :selected').val());
    easyFinance.widgets.operationsJournal.loadJournal();
});

// Jet. Рефакторинг от 22 Октября 2009.
// @todo УДАЛИТЬ ВСЮ ЭТУ ХЕРНЮ В НОЯБРЕ
// Ниже закомментирован старый функционал "Журнала Операций"
// Теперь этот функционал реализован в widgets/operations/operationsJournal
// и в widgets/operations/operationEdit, widgets/operations/operationCalendarEdit
/*
 *$('#op_addoperation_but').click();
 *
 *    $('#op_amount').removeAttr('disabled');
    $('#op_comment').removeAttr('disabled');
 *
 *    $('.tags input').keyup(function(){
        $('.tags_could li').show();

    })
    $('.tags_could li').live('click',function(){
        var txt=$('.tags input').val()+$(this).text()+', ';
        $('.tags input').val(txt);
        $('.tags_could').dialog("close");
    });

    $('.tags_could').hide();
 *
 *  //биндим показ, скрытие и действия диалога выбора тегов
    $('a#tags').removeAttr('href');
    $('a#tags').click(function(){
        $('.tags_could').dialog({
            close: function(event, ui){$(this).dialog( "destroy" )}
        }).dialog("open");
        $('.tags_could li').show();
    });
 *
 *    $('#amount').live('keyup',function(e){
            FloatFormat(this,String.fromCharCode(e.which) + $(this).val())
        })
 *
 *
     $('#amount,#currency').change(function(){
        if ($('#type').val() == 2) {
            //@TODO Дописать округление
            var result = Math.round($('#amount').val() / $('#currency').val());
            if (!isNaN(result) && result != 'Infinity') {
                $("#convertSumCurrency").html("конвертация: "+result);
            }
        }
    });

$('#target').change(function(){
    $("span.currency").each(function(){
        $(this).text(" "+$("#target :selected").attr("currency"));
    });
    $("#amount_done").text(formatCurrency($("#target :selected").attr("amount_done")));
    $("#amount_target").text(formatCurrency($("#target :selected").attr("amount")));
    $("#percent_done").text(formatCurrency($("#target :selected").attr("percent_done")));
    $("#forecast_done").text(formatCurrency($("#target_sel :selected").attr("forecast_done")));
});
*/
//var operationList;

//$('#type').change(function(){changeTypeOperation('add');});
//$('#type').change();

/* @deprecated нигде не используется
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
    var target_sel = $("#target_sel_ed option:selected").val();
    if (sum == 0 || sum == 'NaN'){
        alert('Вы ввели неверное значение в поле "сумма"!');return false;
    }
    if (!validateForm()){
        return false;
    }
    var close = $("#close_ed").attr('checked')?1:0;
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
*/

    // @deprecated
    /**
     * Загружает список всех операций (с фильтром)
     * @return void
     */
    /*
    function loadOperationList() {
        $.get('/operation/listOperations/',{
            dateFrom: $('#dateFrom').val(),
            dateTo: $('#dateTo').val(),
            category: $('#op_cat_filtr :selected').val(),
            account: $('#op_account :selected').val()
        }, function(data) {
            delete operationList;
            operationList = $.extend(data);
            var tr = '',tp;
            if (data != null) {
                // Собираем данные для заполнения в таблицу
                for(var v in data) {
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
                    };
                    tr += "<tr value='"+data[v].id+"'><td class='check'><input type='checkbox' /></td>"
                        + '<td class="light"><a href="#">' + tp + '</a></td>'
                        if (data[v].transfer != $('#op_account :selected').val() && data[v].transfer != 0){
                            //alert(data[v].imp_id);
                            tr += '<td class="summ"><span><b>'+formatCurrency(-data[v].money)+'</b></span></td>'
                        }
                        else{
                            if (data[v].imp_id == null)
                                tr += '<td class="summ"><span><b>'+formatCurrency(data[v].money)+'</b></span></td>'
                            else
                                tr += '<td class="summ"><span><b>'+formatCurrency(data[v].imp_id)+'</b></span></td>'
                        }
                        tr += '<td class="light"><span>'+data[v].date+'</span></td>'
                        + '<td class="big"><span>'+ ((data[v].cat_name == null)? '' : data[v].cat_name) +'</span></td>'
                        + '<td class="no_over big">'+data[v].account_name
                            +'<div class="cont" style="top: -10px"><span>'+'</span><ul>'
                            +'<li class="edit"><a title="Редактировать">Редактировать</a></li>'
                            +'<li class="del"><a title="Удалить">Удалить</a></li>'
                            +'<li class="add"><a title="Копировать">Копировать</a></li>'
                            +'</ul></div>'
                        +'</td></tr>';
                }
                // Очищаем таблицу
                //биндим показ и скрытие тулбокса
                $('tr:not(:first)','#operations_list').each(function(){
                    $(this).remove();
                });
                // Заполняем таблицу
                $('#operations_list').append(tr);
                $('#operations_list th input').change(function(){
                    if($('#operations_list th input').attr('checked'))
                        $('#operations_list .check input').attr('checked','checked');
                    else
                        $('#operations_list .check input').removeAttr('checked');
                })
               //$('.operation_list').jScrollPane();

            }
        },'json');

        $('a#tags').removeAttr('href');
        $('a#tags').click(function(){
            $('.tags_could').dialog({
                close: function(event, ui){$(this).dialog( "destroy" )}
            }).dialog("open");
            $('.tags_could li').show();
        });
		$('.tags input').keyup(function(){
                    $('.tags_could li').show();

		})
                $('.tags_could li').live('click',function(){
                    var txt=$('.tags input').val()+$(this).text()+', ';
                    $('.tags input').val(txt);
                    $('.tags_could').dialog("close");
                });

        // create a style switch button
        /*
	var switcher = $('<a href="javascript:void(0)" class="btn">Change appearance</a>').toggle(
		function(){
			$("#tags ul").hide().addClass("alt").fadeIn("fast");
		},
		function(){
			$("#tags ul").hide().removeClass("alt").fadeIn("fast");
		}
	);
 	$('.tags_could').append(switcher);
        */

	// create a sort by alphabet button
        /*
	var sortabc = $('<a href="javascript:void(0)" class="btn">Sort alphabetically</a>').toggle(
		function(){
			$("#tags ul li").tsort({order:"asc"});
		},
		function(){
			$("#tags ul li").tsort({order:"desc"});
		}
		);
 	$('.tags_could').append(sortabc);
        */

	// create a sort by alphabet button
        /*
	var sortstrength = $('<a href="javascript:void(0)" class="btn">Sort by strength</a>').toggle(
		function(){
			$("#tags ul li").tsort({order:"desc",attr:"class"});
		},
		function(){
			$("#tags ul li").tsort({order:"asc",attr:"class"});
		}
		);
 	$('.tags_could').append(sortstrength);
        */
        //$('.tags_could').hide();
    //}

    /**
     * Удаляет операцию
     * @param id int
     * @return bool
     */
    /* @deprecated
    function deleteOperation(id, tr) {
        if (!confirm("Вы действительно хотите удалить эту запись?")) {
            return false;
        }
        $.post('/operation/del/', {
                id : id
            }, function(data) {
                delete operationList[id];
                $(tr).remove();
                $.jGrowl("Операция удалена", {theme: 'green'});
            }, 'json');
    }

    function deleteTarget(id, tr){
        if (!confirm("Вы действительно хотите удалить перевод на фин.цель?")) {
            return false;
        }
        $.post('/operation/deleteTargetOp/', {
                id : id
            }, function(data) {
                delete operationList[id];
                $(tr).remove();
                $.jGrowl("Операция удалена", {theme: 'green'});
            }, 'json');
    }
    */

/* @deprecated
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
*/

/* @deprecated
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
*/

/* @deprecated
function editOperation(id) {
    $.get('/index.php',{
        id:id
    }, function() {
        $("#addOperation").hide();
        $("#editOperation").html();
        $("#editOperation").show();
        changeTypeOperation('edit');
        scrollTo(0,0);
    });
}
*/

/* @deprecated
function editTargetOperation(id) {
    var modules = "operation";
    var action = "editTargetOperation";
    $.get('/index.php',{
        modules:modules,
        action:action,
        id:id
    },operationAfterEdit);
}
*/

    // Bind
    //$('#btn_Save').click(function(){
    //    saveOperation();
    //})

    /**
     * Добавляет новую операцию
     * @return void
     */
    /*
    function saveOperation() {
        if (!validateForm()){
            return false;
        }

        $.post(($('form').attr('action')), {
            id        : $('#id').val(),
            type      : $('#type').val(),
            account   : $('#op_account').val(),
            category  : $('#op_category').val(),
            date      : $('#op_date').val(),
            comment   : $('#op_comment').val(),
            amount    : tofloat($('#op_amount').val()),
            toAccount : $('#op_AccountForTransfer').val(),
            currency  : $('#op_currency').val(),
            target    : $('#op_target').val(),
            close     : $('#op_close:checked').length,
            tags      : $('#op_tags').val()
        }, function(data){
            for (var v in data) {
                //@FIXME Дописать обработку ошибок и подсветку полей с ошибками
                alert('Ошибка в ' + v);
            }
            // В случае успешного добавления, закрываем диалог и обновляем календарь
            if (data.length == 0) {
                //alert('123');
                clearForm();
                easyFinance.widgets.operationsJournal.setAccount($('#op_account :selected').val());
                easyFinance.widgets.operationsJournal.loadJournal();
            }
        }, 'json');
        return true;
    }
    */

    /*
    $('#account').change(function(){
        changeAccountForTransfer();
        easyFinance.widgets.operationsJournal.setAccount($('#op_account :selected').val());
        easyFinance.widgets.operationsJournal.loadJournal();
    });
    $('#AccountForTransfer').change( function(){changeAccountForTransfer();});
    */

    /**
     * При переводе со счёта на счёт, проверяем валюты
     * @return void
     */
    /*
    function changeAccountForTransfer() {
        /@TODO можно оптимизировать процедуру, и не отсылать данные на сервер, если у нас одинаковая валюта на счетах
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
    */

    /**
     * При изменении типа операции
     */
    /*
    function changeop_TypeOperation() {
        // Расход или Доход
        if ($('#type').val() == 0 || $('#type').val() == 1) {
            $("#op_category_fields,#op_tags_fields").show();
            $("#op_target_fields,#op_transfer_fields").hide();
        //Перевод со счёта
        } else if ($('#type').val() == 2) {
            $("#op_category_fields,#op_target_fields").hide();
            $("#op_tags_fields,#op_transfer_fields").show();
            changeAccountForTransfer();
        //Перевод на финансовую цель
        } else if ($('#type').val() == 4) {
            $("#op_target_fields").show();
            $("#op_tags_fields,#op_transfer_fields,#op_category_fields").hide();
            $('#op_target').change();
        }
    }
    */