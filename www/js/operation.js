// {* $Id: operation.js 137 2009-08-10 16:00:50Z ukko $ *}
    /*var catlast ;
    var datelast ;*/
$(document).ready(function() {

    $('#op_amount').removeAttr('disabled');
    $('#op_comment').removeAttr('disabled');

    $('#op_addoperation_but').click();
    $('#op_btn_Save').click(function(){
        //loadOperationList();
        $('#btn_ReloadData').click();
        })
    var operationList;
    // Init
    $('#amount').live('keyup',function(e){
            FloatFormat(this,String.fromCharCode(e.which) + $(this).val())
        })
    $("#date, #dateFrom, #dateTo").datepicker({dateFormat: 'dd.mm.yy'});//+

    // Bind
    $('#btn_Save').click(function(){
        saveOperation();
    })

    $('#op_account').change(function(){
        $('#btn_ReloadData').click();
    })

    $('#btn_ReloadData').click(function(){loadOperationList();});
    //$('#category').autocomplete();
    $('#amount,#currency').change(function(){
        if ($('#type').val() == 2) {
            /*//@TODO Дописать округление*/
            var result = Math.round($('#amount').val() / $('#currency').val());
            if (!isNaN(result) && result != 'Infinity') {
                $("#convertSumCurrency").html("конвертация: "+result);
            }
        }
    });
    $('#operations_list tr').live('dblclick',function(){
        $(this).find('li.edit a').click();
    })
    $('.light a').live('click', function(){
        $(this).closest('tr').find('li.edit a').click();
        return false;
    });
    $('#account').change(function(){
        changeAccountForTransfer();
        loadOperationList();
    });
    $('#AccountForTransfer').change( function(){changeAccountForTransfer();});
    $('#type').change(function(){changeTypeOperation('add');});
    $('#target').change(function(){
        $("span.currency").each(function(){
            $(this).text(" "+$("#target :selected").attr("currency"));
        });
        $("#amount_done").text(formatCurrency($("#target :selected").attr("amount_done")));
        $("#amount_target").text(formatCurrency($("#target :selected").attr("amount")));
        $("#percent_done").text(formatCurrency($("#target :selected").attr("percent_done")));
        $("#forecast_done").text(formatCurrency($("#target_sel :selected").attr("forecast_done")));
    });

    // Биндим щелчки на кнопках тулбокса (править, удалить, копировать)
    $('#operations_list a').live('click', function(){
        if ($(this).parent().attr('class') == 'edit') {
            fillForm(operationList[$(this).closest('tr').attr('value')]);
            if ($('#op_comment').val() == "Начальный остаток"){
                $('#op_amount').attr('disabled', 'disabled');
                $('#op_comment').attr('disabled', 'disabled');
            }else{
                $('#op_amount').removeAttr('disabled');
                $('#op_comment').removeAttr('disabled');
            }
            $('form').attr('action','/operation/edit/');
        }
        else if($(this).parent().attr('class') == 'del') {
            if (operationList[$(this).closest('tr').attr('value')].virt == "1"){
                deleteTarget($(this).closest('tr').attr('value'), $(this).closest('tr'));
            }
            else{
                deleteOperation($(this).closest('tr').attr('value'), $(this).closest('tr'));
            }
            //deleteOperation($(this).closest('tr').attr('value'), $(this).closest('tr'));
        }
        else if($(this).parent().attr('class') == 'add') {
            fillForm(operationList[$(this).closest('tr').attr('value')]);
            $(this).closest('form').attr('action','/operation/add/');
            $('#date').datepicker('setDate', new Date() );
        }
    });
    $('#operations_list .cont').css({position: 'relative'});
    $('tr:not(:first)','#operations_list').live('mouseover',function(){
        $('#operations_list tr').removeClass('act').find('.cont ul').hide();
        $(this).closest('tr').addClass('act').find('.cont ul').show();
    });
    $('.mid').mousemove(function(){
            if (!$('ul:hover').length && !$('.act:hover').length)
            {
                $('#operations_list tr').removeClass('act').find('.cont ul').hide();
            }
    });
    $('#remove_all_op').click(function(){

        if (!confirm("Вы действительно хотите удалить эти записи?")) {
            return false;
        }
        var ids = [];
        var key = 0;
        var trs = $('#operations_list tr .check input:checked').closest('tr');
        $(trs).each(function(){
            ids[key] =$(this).attr('value');
            key++;
        });

        $.post('/operation/del_all/', {
                id : ids.toString()
            }, function(data) {
                for (var id in ids){
                    if (ids[id])
                    delete operationList[id];
                }
                $(trs).remove();
                $.jGrowl("Операции удалены", {theme: 'green'});
            }, 'json');
    })
    // Autoload
    loadOperationList();


    function formatCurrency(num) {
        if (num=='undefined') num = 0;
        //num = num.toString().replace(/\$|\,/g,'');
        if(isNaN(num)) num = "0";
        var sign = (num == (num = Math.abs(num)));
        num = Math.floor(num*100+0.50000000001);
        var cents = num%100;
        num = Math.floor(num/100).toString();
        if(cents<10)
            cents = "0" + cents;
        for (var i = 0; i < Math.floor((num.length-(1+i))/3); i++)
            num = num.substring(0,num.length-(4*i+3))+' '+
            num.substring(num.length-(4*i+3));
        return (((sign)?'':'-') + '' + num + '.' + cents);
    }

    /**
     * Загружает список всех операций (с фильтром)
     * @return void
     */
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
                        if (data[v].transfer != $('#op_account :selected').val())
                            tr += '<td class="summ"><span><b>'+formatCurrency(-data[v].money)+'</b></span></td>'
                        else
                            tr += '<td class="summ"><span><b>'+formatCurrency(data[v].money)+'</b></span></td>'
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
	var switcher = $('<a href="javascript:void(0)" class="btn">Change appearance</a>').toggle(
		function(){
			$("#tags ul").hide().addClass("alt").fadeIn("fast");
		},
		function(){
			$("#tags ul").hide().removeClass("alt").fadeIn("fast");
		}
	);
 	$('.tags_could').append(switcher);

	// create a sort by alphabet button
	var sortabc = $('<a href="javascript:void(0)" class="btn">Sort alphabetically</a>').toggle(
		function(){
			$("#tags ul li").tsort({order:"asc"});
		},
		function(){
			$("#tags ul li").tsort({order:"desc"});
		}
		);
 	$('.tags_could').append(sortabc);

	// create a sort by alphabet button
	var sortstrength = $('<a href="javascript:void(0)" class="btn">Sort by strength</a>').toggle(
		function(){
			$("#tags ul li").tsort({order:"desc",attr:"class"});
		},
		function(){
			$("#tags ul li").tsort({order:"asc",attr:"class"});
		}
		);
 	$('.tags_could').append(sortstrength);
        $('.tags_could').hide();
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
                /*//@FIXME Дописать обработку ошибок и подсветку полей с ошибками*/
                alert('Ошибка в ' + v);
            }
            // В случае успешного добавления, закрываем диалог и обновляем календарь
            if (data.length == 0) {
                //alert('123');
                clearForm();
                loadOperationList();
            }
        }, 'json');
        return true;
    }

 $('#type').change();

    function clearForm() {
        $('#op_type,#op_category,#op_target').val(0);
        $('#op_amount,#op_AccountForTransfer,#op_comment,#op_tags,#op_date').val('');

        $('span#op_amount_target').text();

        $('span#op_amount_done').text();
        $('span#op_forecast_done').text();
        $('span#op_percent_done').text();

        $('#op_close').removeAttr('checked');

        $('form').attr('action','/operation/add/');

        $('#op_type').change();//*/
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
        $('#op_id').val(data.id);
        $('#op_account').val(data.account_id);
        
        if (data.tr_id=='1')//transfer
        {
            $('#op_type').val(2);
        }
        else
        {

            if (data.virt=='1')
            {
                $('#op_type').val(4);
            }
            else
            {
                if (data.drain=='1') {
                    $('#op_type').val(0);
                } else {
                    $('#op_type').val(1);//@todo
                }
            }
        }
        //$('#type').val(data.type);
        //alert(data.toString());
        //$('#op_type').val(data.drain);
        //////////////////////////
        $('#op_amount').val(data.money);
        $('#op_category').val(data.cat_id);
        //$('#target').val(data.);
        //$('#close').val(data.);
        $('#op_AccountForTransfer').val(data.transfer);
        $('#op_date').val(data.date);
        $('#op_tags').val(data.tags);
        $('#op_comment').val(data.comment);
        $(document).scrollTop(300);
    }

    /**
     * При переводе со счёта на счёт, проверяем валюты
     * @return void
     */
    function changeAccountForTransfer() {
        /*//@TODO можно оптимизировать процедуру, и не отсылать данные на сервер, если у нас одинаковая валюта на счетах */
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
