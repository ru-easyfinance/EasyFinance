easyFinance.widgets.calendarList = function(){
    var _data;
    var chainId;
    var operationId;
    
    function init(){
        $('#event_with_select_events span#remove_all_cal').click(function() {
            var ch = $('#per_tabl tr td input:checked');
            if ($(ch).length > 0 && confirm('Удалить выбранные события?')) {
                var obj = new Array ();
                $(ch).closest('tr').each(function(){
                    obj.push($(this).attr('id').replace('ev_', ''));
                });
                $.jGrowl('События удаляются!',{theme : 'green'});
                easyFinance.models.accounts.deleteOperationsByIds(obj, [], function() {});
            }
        });


        $('#event_with_select_events span#accept_all_cal').click(function() {
            var ch = $('#per_tabl tr td input:checked');
            if ($(ch).length > 0 && confirm('Подтвердить выбранные события?')) {
                var obj = new Array ();
                $(ch).closest('tr').each(function(){
                    obj.push($(this).attr('id').replace('ev_', ''));
                });
                $.jGrowl('События подтверждаются!',{theme : 'green'});
                easyFinance.models.accounts.acceptOperationsByIds(obj);
            }
        });


        $('#per_tabl_header th input[type=checkbox]').change(function(){
            if ($(this).attr('checked')){
                $(this).closest('div').find('input[type=checkbox]').attr('checked', 'checked');
            }else{
                $(this).closest('div').find('input[type=checkbox]').removeAttr('checked');
            }
        });
        $('#per_tabl tr').live('dblclick',function(){
            $(this).find('li.edit a').click();
        });
        var element;
        $('#per_tabl tr .cont ul li.edit a').live('click',function(){
            element = _data[$(this).closest('tr').attr('id').replace('ev_', '')];
            promptSingleOrChain("edit", function(isChain){
                easyFinance.widgets.operationEdit.fillFormCalendar(element, true, isChain);
            });

            return false;
        });

        $('#per_tabl tr .cont ul li.accept a').live('click',function(){
            $.jGrowl('События подтверждаются!',{theme : 'green'});
            var operationId = $(this).closest('tr').attr('id').replace('ev_', '');
            easyFinance.models.accounts.acceptOperationsByIds([operationId]);
            return false;
        });
        
        $('#per_tabl tr .cont ul li.del a').live('click',function(){
            var element = _data[$(this).closest('tr').attr('id').replace('ev_', '')];
            chainId = element.chain;
            operationId = element.id;

            promptSingleOrChain("delete", function(isChain) {
                if (isChain)
                    easyFinance.models.accounts.deleteOperationsChain(chainId, function() {
                    });
                else
                    easyFinance.models.accounts.deleteOperationsByIds(operationId, [], function() {
                    });
            });
        });
    }

    function load(data){
        var periodicList = '';
        var tempDate = new Date();
        var accept = '';
        var date = $('#calendar').fullCalendar('getDate');
        var month = date.getMonth();
        _data = $.extend({}, data);
        for (var key in data){
            accept  = data[key].accepted == '1' ? 'accept':'reject';
            tempDate.setTime(data[key].timestamp* 1000);
            if (month == tempDate.getMonth()){
                periodicList += '<tr id="ev_' + key + '" class="'+accept+'"><td class="chk"><input type="checkbox" value="" /></td>' +
                    '<td>' + $.datepicker.formatDate('dd.mm.yy',tempDate) + '</td>' +
                    '<td>' + shorter((easyFinance.models.category.getUserCategoryNameById(data[key].cat_id)||'Без категории'),25) + '</td>' +
                    '<td class="money"><span style="background : 0; display : inline; width : auto; height : auto;" class="'+(_data[key].money > 0 ? 'sumGreen' : 'sumRed')+'">' + (formatCurrency(_data[key].money)+ '</span> ' +(easyFinance.models.accounts.getAccountCurrencyText(_data[key].account_id) || easyFinance.models.currency.getDefaultCurrencyText())) +
                    '</td>' +
                    '<td><div class="cont" style="top: -17px"><ul style="right:0">' +
                        (accept == 'reject'? '<li class="accept"><a title="Подтвердить">Подтвердить</a></li>':'') +
                        '<li class="edit"><a title="Редактировать">Редактировать</a></li>' +
                        '<li class="del"><a title="Удалить">Удалить</a></li>' +
                        '</ul></div></td><td class="'+accept+'" style="width:16px">&nbsp;&nbsp;&nbsp;</td>' +
                    '</tr>';
            }
        }
        $('#per_tabl tbody').html(periodicList);        
    }

    return{
        init: init,
        load: load
    }
}();