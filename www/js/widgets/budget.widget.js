
easyFinance.widgets.budget = function(model){
    if (!model){return {};}

    /**
     * @desc {html} список бюджетов сформированный в хтмл
     */
    var _$_list = model.print_list('0');
    /**
     * @desc {} системный объект хранящий общую информацию о бюджете
     */
    //var _info = model.print_info();
    /**
     * @desc {html} остаток,ср доход и расход по бюджетам.сформирован в хтмл
     */
    var _$_group = model.print_info().group;
    /**
     * @desc {Int} Общий бюджет
     */
    //var _$_total = model.print_info().total;


    $('.budget .waste_list form').html(_$_list);
    $('.budget .f_field3').html(_$_group);
    //$('.budget #total_budget').val(_$_total);

    $('.budget #r_month').val($('.budget #month').val())
    $('.budget #r_year').val($('.budget #year').val())


//    $('#edit_budget').dialog({bgiframe: true,
//                autoOpen: false,
//                width: 647});
    $('#master').dialog({bgiframe: true,autoOpen: false,width: 520});
    $('#sel_date').dialog({bgiframe: true,autoOpen: false,width: 520,minHeight:'auto'});
    $('#addacc').click(function(){$('#sel_date').dialog('open');})
    
    var ret =new Array,date;
    
    $('.next').click(function(){
        date='01.'+$('#sel_date select').val()+'.'+$('#sel_date input').val();
        
        $('#master').dialog('open');
        $('#master input').removeAttr('readonly');
        $('#master div.amount').each(function(){
            var txt = $(this).text();
            $(this).html('<input type="text" value="'+txt+'"/>')
        })
        $('#master .button').click(function(){
            var id =$(this).attr('id');
            $('#master .waste_list form tr').each(function(){
              if(!ret[id]){ret[id] = new Array}
              ret[id][$(this).attr('id')] = $(this).find('input').val();
            })
            $('#master .amount').each(function(){
                if(!ret[id]){ret[id] = new Array}
              ret[id][$(this).attr('id')] = $(this).find('input').val();
            })
            $('#master .waste_list form').html(model.print_list(id))
        });
        $('#master .w3').hide();
        $('#master .button#0').click()
        $('#sel_date').dialog('close');
    })
    $('#master .button').click(function(){
        $('#master .button').css({background:'#FFFFFF',color:'#50C319',borderBottom:'1px dotted #50C319'});
        $(this).css({background:'#50C319',color:'#FFFFFF',borderBottom:'0'});
    });
    $('#master #b_save').click(function(){
        $('#master .button').click();
        var r_str = ret.toString();
        $.get('/budget/add/',{data:r_str,date:date} , function(data){model.load(data)
            }, 'json')
    });
    $('div.line').live('click',function(){
        $(this).toggleClass('open').toggleClass('close');
    })
    /**
     * bind events with budgets
     */
//     $('.budget .waste_list form tr').live('dblclick',function(){
//         var cat_id = $(this).attr('id');
//         var p_id = $(this).closest('div.line').attr('id');
//         var e_budget = model.get_data(p_id, cat_id);
//         $('#edit_budget').show()
//
//         $('#edit_budget').dialog('open');
//
//         $('#edit_budget #b_drain_type').val(e_budget.type);
//         $('#edit_budget .b_category').attr('id',e_budget.id).text(e_budget.name);
//         $('#edit_budget #b_period').val(e_budget.period);
//         $('#edit_budget #b_amount').val(e_budget.total);
//
//         $('#edit_budget #b_month').val($('.budget #r_month').val());
//         $('#edit_budget #b_year').val($('.budget #r_year').val());
//     })
     $('#reload_bdg').click(function(){
         if ( ($('.budget #r_month').val()!=$('.budget #month').val())||($('.budget #r_year').val()!=$('.budget #year').val()) ) {
            $.post('/budget/load/',{
                date:'01.'+$('.budget #month').val()+'.'+$('.budget #year').val()
            },function(data){
                model.load(data);
                _$_list = model.print_list($('.budget #r_type').val());
            },'json')
         }
         else
         {
             _$_list = model.print_list($('.budget #r_type').val());
         }

        $('.budget #r_month').val($('.budget #month').val())
        $('.budget #r_year').val($('.budget #year').val())
        
        $('.budget .waste_list form').html(_$_list);
     })
    /**
     * @desc маска для инпута с годом
     */
    $('input#year').keyup(function(){
        var str = $('input#year').val();
        $('input#year').val(str.match(/[0-9]{0,4}/));
    });
    /**
     * @desc подсветка строки в списке при наведении
     */
    $('.waste_list form tr').live('mouseover',function(){
        $(this).addClass('act');
    }).live('mouseout',function(){
        $(this).removeClass('act');
    });
    return {};
}