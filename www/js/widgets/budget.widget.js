
easyFinance.widgets.budget = function(model){
    /**
     * @deprecated
     */
    function FloatFormat(obj, in_string )
{
    //'.'
    var l = in_string.length;
    var rgx = /[0-9]/;
    var c=0;
    var p =1;
    var newstr ='';
    var i = 0;

    for(var a=1;a<=l;a++)
    {
        i=l-a+1;
        if (rgx.test(in_string.substr(i,1)))
        {
            if (c == 3)
            {
                newstr = ' ' + newstr;
                c = 0
            }
            newstr =in_string.substr(i,1)+newstr
            c++;
        }
        if (in_string.substr(i,1)=='.' || in_string.substr(i,1)==',')
        {
            if (p){
                newstr = newstr.substr(0,2)
                newstr ='.'+newstr;
            }
            c=0;
            p = 0;
        }
    }
    $(obj).val(newstr)
}

$('#master input').live('keyup',function(e){
    FloatFormat($(this),String.fromCharCode(e.which) + $(this).val())

})

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
    $('.line .amount').each(function(){
        if ($(this).text()=='0.00') $(this).closest('.line').remove()
    })
    $('.cont input[value="0.00"]').closest('tr').remove();
    $('.cont').each(function(){
        var str = '<span>'+$(this).find('input').val()+'</span>'
        $(this).html(str);
    })
    $('.budget .f_field3').html(_$_group);
    //$('.budget #total_budget').val(_$_total);
    var d = new Date();
    $('.budget #month').val(d.getMonth()+1)
    $('.budget #year').val(d.getFullYear())
    $('.budget #r_month').val($('.budget #month').val())
    $('.budget #r_year').val($('.budget #year').val())


//    $('#edit_budget').dialog({bgiframe: true,
//                autoOpen: false,
//                width: 647});
    $('#master').dialog({bgiframe: true,autoOpen: false,width: 520});
    $('#sel_date').dialog({bgiframe: true,autoOpen: false,width: 520,minHeight:'auto'});
    $('#addacc').click(function(){
        $('#sel_date').dialog('open');
        if($('.budget #r_month').val() == 12)
        {
            $('#sel_date #month').val(1);
            $('#sel_date #year').val(parseInt($('.budget #r_year').val())+1);
        }else{
            $('#sel_date #month').val(parseInt($('.budget #r_month').val())+1);
            $('#sel_date #year').val($('.budget #r_year').val());
        }
    })
    
    var ret =new Array,date;
    
    $('.next').click(function(){
        date='01.'+$('#sel_date select').val()+'.'+$('#sel_date input').val();
        $('#master').dialog('open');
        $('#master #b_save').hide()
        $('#master .button').click(function(){
            $(this).hide();
            $('#master #b_save').show();
            
            var id =-parseInt($(this).attr('id'))+1;
            ret[id] = '['
            $('#master .waste_list form tr').each(function(){
                if(parseFloat($(this).find('input').val().toString().replace(/[^0-9.]/,''))!='0'){
                    var tmp = '{"'+$(this).attr('id')+'": "'+parseFloat($(this).find('input').val())+'"},'
                    ret[id] += tmp
                }
                    
            })

            $('#master .amount').each(function(){
                if(parseFloat($(this).find('input').val().toString().replace(/[^0-9.]/,''))!='0'){
                    var tmp = '{"'+$(this).closest('.line').attr('id').toString().replace(/[^0-9]/gi,'')+'": "'+parseFloat($(this).find('input').val())+'"},'
                    ret[id] += tmp
                }
            })
            ret[id] +=']';
            $('#master .waste_list form').html(model.print_list('1'))

            $('#master input').removeAttr('readonly');
            $('#master div.amount').each(function(){
                var txt = $(this).text();
                txt = (txt =='null')?0:txt;
                $(this).html('<input type="text" value="'+txt+'"/>')
            })
            $('#master .w3').hide();
            //$('.cont input[value="null"]').closest('tr').remove();
            
        });
        $('#master .waste_list form').html(model.print_list('0'))
        $('#master input').removeAttr('readonly');
        $('#master div.amount').each(function(){
            var txt = $(this).text();
            txt = (txt =='null')?0:txt;
            $(this).html('<input type="text" value="'+txt+'"/>')
        })
        $('#master .w3').hide();
        //$('#master .button').click()
        $('#sel_date').dialog('close');
    })
    $('#master .button').click(function(){
        $('#master .button').css({background:'#FFFFFF',color:'#50C319',borderBottom:'1px dotted #50C319'});
        $(this).css({background:'#50C319',color:'#FFFFFF',borderBottom:'0'});
    });
    $('#master #b_save').click(function(){
        //$('#master .button#1').click();
        var id =1
            ret[id] = '['
            $('#master .waste_list form tr').each(function(){
                if(parseFloat($(this).find('input').val().toString().replace(/[^0-9.]/,''))!='0'){
                    var tmp = '{"'+$(this).attr('id')+'": "'+$(this).find('input').val().toString().replace(/[^0-9\.]/gi, '')+'"},'
                    ret[id] += tmp
                }

            })

            $('#master .amount').each(function(){
                if(parseFloat($(this).find('input').val().toString().replace(/[^0-9.]/,''))!='0'){
                    var tmp = '{"'+$(this).closest('.line').attr('id')+'": "'+parseFloat($(this).find('input').val())+'"},'
                    ret[id] += tmp
                }
            })
            ret[id] +=']';//@deprecate переписать
        var r_str = '{"1":'+ret[1]+'},"0":{'+ret[0]+'}';
        $.post('/budget/add/',{data:r_str.replace(/,]/gi, ']'),start:date} , function(data){
            model.load(data);
            $('#master .button').show()
            $('#master').dialog('close');
            }, 'json')
    });
    $('div.line a.name').live('click',function(){
        $(this).closest('.line').toggleClass('open').toggleClass('close');
        return false;
    })
    $('#reload_bdg').click(function(){
    if ( ($('.budget #r_month').val()!=$('.budget #month').val())||($('.budget #r_year').val()!=$('.budget #year').val()) ) {
        $.post('/budget/load/',{
                start:'01.'+$('.budget #month').val()+'.'+$('.budget #year').val()
            },function(data){
                model.load(data);
                $('.cont input[value="0.00"]').closest('tr').remove();
                $('.line .amount').each(function(){if ($(this).text()=='0.00') $(this).closest('.line').hide()})
                _$_list = model.print_list($('.budget #r_type').val());
                $('.cont').each(function(){
                    var str = '<span>'+$(this).find('input').val()+'</span>'
                    $(this).html(str);
                })
            },'json')
         }
         else
         {
             _$_list = model.print_list($('.budget #r_type').val());
         }
        $('.cont').each(function(){
            var str = '<span>'+$(this).find('input').val()+'</span>'
            $(this).html(str);
        })
        $('.budget #r_month').val($('.budget #month').val())
        $('.budget #r_year').val($('.budget #year').val())
        
        $('.budget .waste_list form').html(_$_list);
        $('.line .amount').each(function(){if ($(this).text()=='0.00') $(this).closest('.line').hide()})
        $('.cont input[value="0.00"]').closest('tr').remove();
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