
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

$('#master input')
    .live('keyup',function(e){
        FloatFormat($(this),String.fromCharCode(e.which) + $(this).val())
    })
    .live('click',function(e){
        if (this.value == '0.00')
            this.value = '';
    }
);

    if (!model){return {};}

    model.load(res.budget);

    /**
     * @desc {html} список бюджетов сформированный в хтмл
     */
    var _$_list = model.print_list('0','1');
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
    


    $('#list.budget .waste_list form').html(_$_list);
    $('.w2,.w4').css('text-align', 'right')
    /*********************************************************************
     * Заплатка на вёрстку требует последующий модификации модели и т.п.
     */
    $('.line .amount').each(function(){
        if ($(this).text()=='0.00') $(this).closest('.line').remove()
    })
    $('.cont input[value="0.00"]').closest('tr').remove();
    $('.cont').each(function(){
        var str = '<span>'+$(this).find('input').val()+'</span>'
        $(this).html(str);
    })
    //********************************************************************

    $('.budget .f_field3').html(_$_group);

if (parseFloat($('.budget .f_field3 .rest span b').text()) < 0)
                    {
                        $('.budget .f_field3 .rest span').css('color','#EB3C34')
                    }
                    else
                    {
                        $('.budget .f_field3 .rest span').css('color','#309500')
                    }
    /**********************************************************************
     * Появилась дата в мастере.Самое простое решение
     */
    var d = new Date();
    $('.budget #month').val(d.getMonth()+1)
    $('.budget #year').val(d.getFullYear())
    $('.budget #r_month').val($('.budget #month').val())
    $('.budget #r_year').val($('.budget #year').val())
    if($('.budget #r_month').val() == 12)
    {
        $('#sel_date #month').val(1);
        $('#sel_date #year').val(parseInt($('.budget #r_year').val())+1);
    }else{
        $('#sel_date #month').val(parseInt($('.budget #r_month').val())+1);
        $('#sel_date #year').val(parseInt($('.budget #r_year').val()));
    }
    //********************************************************************


    $('#master').dialog({bgiframe: true,autoOpen: false,width: 520,modal:true});
    $('#sel_date').dialog({bgiframe: true,autoOpen: false,width: 520});


    /*********************************************************************
     *Появилась кнопочка для мастера. верстка началась в 8 писалось в очень спехе 
     */
    $('#btnBudgetWizard').click(function(){
        $('#sel_date').dialog('open');
    })

    /*** Всплывающие подсказки */

    if(!$.cookie('help')){
         $('#btnBudgetWizard').qtip({
           content: 'Создать новый или отредактировать уже существующий бюджет',
           show: {delay: 1000},
           position: {target: 'mouse'},
           style: 'mystyle'
        });

        $('.w1').qtip({
           content: 'Категория, по которой будет учитываться бюджет',
           show: {delay: 1000},
           position: {target: 'mouse'},
           style: 'mystyle'
        });

        $('.w2').qtip({
           content: 'Сумма, выделенная в бюджете на эту категорию',
           show: {delay: 1000},
           position: {target: 'mouse'},
           style: 'mystyle'
        });

        $('.w3').qtip({
           content: 'Риска в линии времени отображает позицию текущей даты в выбранном периоде. \n\
Зелёная полоска сигнализирует, что вы укладывайтесь в бюджет, а красная - что с текущим темпом трат, вы можете не уложиться в планируемую сумму.',
           show: {delay: 1000},
           position: {target: 'mouse'},
           style: 'mystyle'
        });

        $('.w4').qtip({
           content: 'Средний расход(доход) за предыдущие три месяца по выбранной категории',
           show: {delay: 1000},
           position: {target: 'mouse'},
           style: 'mystyle'
        });
    }

    //*********************************************************************
    ///////////////////////////////////////////////////////////////////////
    //////////////////////////Мастер///////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////


    /*
     * Атцкий хук
     */
    function load(arr)//переменная Арррррр
    {
        while (arr&&(arr.indexOf('{') != -1)){
            var aggl = arr.toString().match(/\{[A-Za-z0-9\:\ \.\"]+\}/)[0]
            var key = aggl.substring(0,aggl.indexOf(':')).replace(/[^0-9\.]/gi, '')
            arr = arr.replace(aggl,'');
            var val = aggl.substring(aggl.indexOf(':')).replace(/[^0-9\.]/gi, '')
            if (val){
                //alert('#master #c_'+key+' .amount')
                $('#master #'+key+',#master #c_'+key+' .amount').find('input').val(formatCurrency(val));//рекомендуется сразу убиться об стенку
            }
        }
        $('#master .line').each(function(){
            var summ = 0;
            $(this).find('table input').each(function(){
                var str = $(this).val().toString()||'0'
                summ += parseFloat(str.replace(/[^0-9.]/gi,''));
            })
            if(!$(this).find('.amount input').length){
                $(this).find('.amount').text(formatCurrency(summ));
            }
        })
    }

    ///////////////////////////////////////////////////////////////////////////
    var ret =new Array,date;

    $('#master #prev').click(function(){
        $('#master #prev').hide()
        $('#master .button').show()//тк не работает чётко и хорошо по понятным причинам хак дл тех кому хочется кнопки тыкать
        $('#master #b_save').hide()//см на строку выше
        $('#master .line tr').closest('.line').each(function(){
                var summ = 0;
                $(this).find('table input').each(function(){
                    var str = $(this).val().toString()||'0'
                    summ += parseFloat(str.replace(/[^0-9.]/gi,''));
                })
                $(this).find('.amount').text(formatCurrency(summ));
            })
            //Опять динамический заголовок
            $('.ui-dialog-titlebar #ui-dialog-title-master').html('<h4>Шаг 2 из 3. Доходы - Планирование бюджета на '+$('#sel_date select option[value="'+$('#sel_date select').val()+'"]').text() +' '+$('#sel_date input').val() + '</h4>')
            //статистика... не самый удачный вариант(оч много иттераций),но это кастыль.
            ret['1'] = '['
            $('#master .waste_list form tr').each(function(){
                if(parseFloat($(this).find('input').val().toString().replace(/[^0-9.]/,''))!='0'){
                    var tmp = '{"'+$(this).attr('id')+'": "'+parseFloat($(this).find('input').val().toString().replace(/[^0-9.]/,''))+'"},'
                    ret['1'] += tmp
                }

            })
            $('#master .amount').each(function(){
                var str = $(this).find('input').val() || $(this).text();
                if(parseFloat(str.replace(/[^0-9.]/,''))!='0'){
                    var tmp = '{"'+$(this).closest('.line').attr('id').toString().replace(/[^0-9.]/gi,'')+'": "'+str.replace(/[^\-0-9.]/,'')+'"},'
                    ret['1'] += tmp
                }
            })
            ret['1'] +=']';
            /////////////////////////////////////////////////////////////////////////////////
            $('#master .waste_list form').html(model.print_list('1'))
            $('#master .h .w4').text('Сред.Доход');
            $('#master input').removeAttr('readonly');
            $('#master div.amount').each(function(){
                var txt = $(this).text();
                txt = (txt =='null')?0:txt;
                if (!$(this).closest('.line').find('tr').html()){
                    $(this).closest('.amount').html('<input type="text" value="'+txt+'"/>')
                }
            })
            load(ret['0'])
            $('#master .w3').hide();
            $('#master tr input').blur(function(){
                    var summ = 0;
                    $(this).closest('table').find('input').each(function(){
                        var str = $(this).val().toString()||'0'
                        summ += parseFloat(str.replace(/[^0-9.]/gi,''));
                    })

                    $(this).closest('.line').find('.amount').text(formatCurrency(summ));
                    var tmp = summa();
                    $('#master .f_field3 .income span b').text(formatCurrency(tmp));
                    $('#master .f_field3 .rest span b').text(formatCurrency( tmp - $('#master .f_field3 .waste span b').text().toString().replace(/[^0-9.]/gi, '')));
                    if (parseFloat($('#master .f_field3 .rest span b').text()) < 0)
                    {
                        $('#master .f_field3 .rest span b').css('color','#EB3C34')
                    }
                    else
                    {
                        $('#master .f_field3 .rest span b').css('color','#309500')
                    }
                })

                $('#master input').blur(function(){
                    var tmp = summa();
                    $('#master .f_field3 .income span b').text(formatCurrency(tmp));
                    $('#master .f_field3 .rest span b').text(formatCurrency( tmp - $('#master .f_field3 .waste span b').text().toString().replace(/[^0-9.]/gi, '')));
                    if (parseFloat($('#master .f_field3 .rest span b').text()) < 0)
                    {
                        $('#master .f_field3 .rest span b').css('color','#EB3C34')
                    }
                    else
                    {
                        $('#master .f_field3 .rest span b').css('color','#309500')
                    }
                })
    })


    function summa(){
        var ret = 0;
        $('#master div.amount').each(function(){
            var str = $(this).find('input').val() || $(this).text();
            if(!isNaN(parseFloat(str.replace(/[^0-9.]/,'')))){
                ret += parseFloat(str.replace(/[^0-9.]/,''));
            }
        })
        return ret
    }

    var isCAmmount;
    $('.next').click(function(){
        $('#master #prev').hide();
        $('#master #b_save').hide();
        date='01.'+$('#sel_date select').val()+'.'+$('#sel_date input').val();//Формирование удобной серверу даты
        $('#master').dialog('open');//а вот и сам мастер
        $('.ui-dialog-titlebar #ui-dialog-title-master').html('<h4>Шаг 2 из 3. Доходы - Планирование бюджета на '+$('#sel_date select option[value="'+$('#sel_date select').val()+'"]').text() +' '+$('#sel_date input').val() + '</h4>')//динамические титлы наспех
        $('#master .button').show();//тк не работает чётко и хорошо по понятным причинам хак дл тех кому хочется кнопки тыкать
        $('#master #b_save').hide();//см на строку выше
        $('#master #b_save.tab').hide();
        /*
         * Оказалось что мастер грузится с сервера .....
         */
        $.post('/budget/load/', {start: date},function(data){
                model.load(data);             
                _$_list = model.print_list('1');
                $('#master .waste_list form').html(_$_list);
                _$_group = model.print_info().group;
                $('#master .f_field3').html(_$_group);
                $('#master .h .w4').text('Сред.Доход');
                //дальше костыли, причём часть из них не актуальна в свете изменяющихся, в то время постоянно, требований к отображению
                $('#master input').removeAttr('readonly');
                $('#master div.amount').each(function(){
                    var txt = $(this).text();
                    txt = (txt =='null')?0:txt;
                    if (!$(this).closest('.line').find('tr').html()){
                        $(this).closest('.amount').html('<input type="text" value="'+txt+'"/>')
                    }
                })
                $('#master .w3').hide();
                $('#master .cont').css('width','180px');
                $('#sel_date').dialog('close');
                $('#master .f_field3 .rest').css({left:'193px',top: '20px'})
                $('#master .f_field3 .waste').css({right:'0px',left:'350px'})
                /////////////////////////////////////////////////////////////

                /**
                 *по сути тоже костыль но не очень страшный и даже более мение оптимизированный
                 */
                
                $('#master tr input').blur(function(){
                    var summ = 0;
                    $(this).closest('table').find('input').each(function(){
                        var str = $(this).val().toString()||'0'
                        summ += parseFloat(str.replace(/[^0-9.]/gi,''));
                    })
                    
                    $(this).closest('.line').find('.amount').text(formatCurrency(summ));
                    var tmp = summa();
                    $('#master .f_field3 .income span b').text(formatCurrency(tmp));
                    $('#master .f_field3 .rest span b').text(formatCurrency( tmp - $('#master .f_field3 .waste span b').text().toString().replace(/[^0-9.]/gi, '')));
                    if (parseFloat($('#master .f_field3 .rest span b').text()) < 0)
                    {
                        $('#master .f_field3 .rest span b').css('color','#EB3C34')
                    }
                    else
                    {
                        $('#master .f_field3 .rest span b').css('color','#309500')
                    }
                })

                $('#master input').blur(function(){
                    var tmp = summa();
                    $('#master .f_field3 .income span b').text(formatCurrency(tmp));
                    $('#master .f_field3 .rest span b').text(formatCurrency( tmp - $('#master .f_field3 .waste span b').text().toString().replace(/[^0-9.]/gi, '')));
                    if (parseFloat($('#master .f_field3 .rest span b').text()) < 0)
                    {
                        $('#master .f_field3 .rest span b').css('color','#EB3C34')
                    }
                    else
                    {
                        $('#master .f_field3 .rest span b').css('color','#309500')
                    }
                })
        } , 'json');

        
        /*
         * 2 ая страница бюджета
         */                
        $('#master .button').click(function(){
            //статистика пока хук
            $('#master .button').hide();
            $('#master #prev').show();
            $('#master #b_save').show();
            $('#master .line').each(function(){
                if($(this).find('.amount input').lenght){
                var summ = 0;
                $(this).find('table input').each(function(){
                    var str = $(this).val().toString()||'0'
                    summ += parseFloat(str.replace(/[^0-9.]/gi,''));
                })
                $(this).find('.amount').text(formatCurrency(summ));
                }
            })
            $('#master .h .w4').text('Сред.Расход');
            //Опять динамический заголовок
            $('.ui-dialog-titlebar #ui-dialog-title-master').html('<h4> Шаг 3 из 3. Расходы - Планирование бюджета на '+$('#sel_date select option[value="'+$('#sel_date select').val()+'"]').text() +' '+$('#sel_date input').val() + '</h4>')
            //статистика... не самый удачный вариант(оч много иттераций),но это кастыль.
            ret['0'] = '['
            $('#master .waste_list form tr').each(function(){
                if(parseFloat($(this).find('input').val().toString().replace(/[^0-9.]/,''))!='0'){
                    var tmp = '{"'+$(this).attr('id')+'": "'+parseFloat($(this).find('input').val().toString().replace(/[^0-9.]/,''))+'"},'
                    ret['0'] += tmp
                }
                    
            })
            isCAmmount = 0;
            $('#master .amount').each(function(){
                var str = $(this).find('input').val() || $(this).text();
                if(parseFloat(str.replace(/[^0-9.]/,''))!='0'){
                    var tmp = '{"'+$(this).closest('.line').attr('id').toString().replace(/[^0-9.]/gi,'')+'": "'+str.replace(/[^\-0-9.]/,'')+'"},'
                    ret['0'] += tmp
                    isCAmmount += parseFloat(str.replace(/[^0-9.]/,''));
                }
            })
            var tmp = isCAmmount;
            $('#master .f_field3 .income span b').text(formatCurrency(tmp));
            $('#master .f_field3 .rest span b').text(formatCurrency( tmp - $('#master .f_field3 .waste span b').text().toString().replace(/[^0-9.]/gi, '')));
            ret['0'] +=']';
            if (parseFloat($('#master .f_field3 .rest span b').text()) < 0)
            {
                $('#master .f_field3 .rest span b').css('color','#EB3C34')
            }
            else
            {
                $('#master .f_field3 .rest span b').css('color','#309500')
            }
            
            //////////////////////////////////////////////////////////////////////////////////
            $('#master .waste_list form').html(model.print_list('0'))

            $('#master input').removeAttr('readonly');
            $('#master div.amount').each(function(){
                var txt = $(this).text();
                txt = (txt =='null')?0:txt;
                if (!$(this).closest('.line').find('tr').html()){
                    $(this).closest('.amount').html('<input type="text" value="'+txt+'"/>')
                }
            })
            $('#master tr input').blur(function(){
                    var summ = 0;
                    $(this).closest('table').find('input').each(function(){
                        var str = $(this).val().toString()||'0'
                        summ += parseFloat(str.replace(/[^0-9.]/gi,''));
                    })
                    $(this).closest('.line').find('.amount').text(formatCurrency(summ));
                    var tmp = summa();
                    $('#master .f_field3 .waste span b').text(formatCurrency(tmp));
                    $('#master .f_field3 .rest span b').text(formatCurrency($('#master .f_field3 .income span b').text().toString().replace(/[^0-9.]/gi, '')-tmp));
                    if (parseFloat($('#master .f_field3 .rest span b').text()) < 0)
                    {
                        $('#master .f_field3 .rest span b').css('color','#EB3C34')
                    }
                    else
                    {
                        $('#master .f_field3 .rest span b').css('color','#309500')
                    }
            })
            $('#master input').blur(function(){
                    var tmp = summa();
                    $('#master .f_field3 .waste span b').text(formatCurrency(tmp));
                    $('#master .f_field3 .rest span b').text(formatCurrency($('#master .f_field3 .income span b').text().toString().replace(/[^0-9.]/gi, '')-tmp));
                    if (parseFloat($('#master .f_field3 .rest span b').text()) < 0)
                    {
                        $('#master .f_field3 .rest span b').css('color','#EB3C34')
                    }
                    else
                    {
                        $('#master .f_field3 .rest span b').css('color','#309500')
                    }
                })
            $('#master .w3').hide();
            load(ret['1'])
            $('#master .button').css({background:'#FFFFFF',color:'#50C319',borderBottom:'1px dotted #50C319'});
        });
    })
    
    $('#master #b_save').click(function(){
        $('#master .line').each(function(){
            var summ = 0;
            $(this).find('table input').each(function(){
                var str = $(this).val().toString()||'0'
                summ += parseFloat(str.replace(/[^0-9.]/gi,''));
            })
            if(!$(this).find('.amount input').length)$(this).find('.amount').text(formatCurrency(summ));
        })
        ret['1'] = '['
        $('#master .waste_list form tr').each(function(){
            if(parseFloat($(this).find('input').val().toString().replace(/[^0-9.]/,''))!='0'){
                var tmp = '{"'+$(this).attr('id')+'": "'+$(this).find('input').val().toString().replace(/[^0-9\.]/gi, '')+'"},'
                ret['1'] += tmp
            }
        })
        var tmpAmm = 0;
        $('#master .amount').each(function(){
            var str = $(this).find('input').val() || $(this).text();
            if(parseFloat(str.replace(/[^0-9.]/,''))!='0'){
                var tmp = '{"'+$(this).closest('.line').attr('id').toString().replace(/[^0-9.]/gi,'')+'": "'+str.replace(/[^\-0-9.]/, '')+'"},'
                ret['1'] += tmp
                tmpAmm += parseFloat(str.replace(/[^0-9.]/,''));
            }
        })
        var tmp = tmpAmm;
            $('#master .f_field3 .waste span b').text(formatCurrency(tmp));
            $('#master .f_field3 .rest span b').text(formatCurrency( $('#master .f_field3 .income span b').text().toString().replace(/[^0-9.]/gi, '')-tmp));
        ret['1'] +=']';
        var r_str = '{"d":'+ret[0]+', "r":'+ret[1]+'}';
        if (parseFloat($('#master .f_field3 .rest span b').text()) < 0)
        {
            $('#master .f_field3 .rest span b').css('color','#EB3C34')
        }
        else
        {
            $('#master .f_field3 .rest span b').css('color','#309500')
        }
        if(parseFloat($('#master .f_field3 .rest span b').text())<0)
        {
            if(!confirm('Ваш общий расход превышает общий доход. Продолжить сохранение?'))
                return false;
        }
        $.post('/budget/add/',{data:r_str.replace(/,]/gi, ']'),start:date} , function(data){
            ret = ['',''];
            if (!data['errors'] || data.errors == [])
            {
               $.jGrowl("Бюджет сохранён", {theme: 'green'});
            }
            else
            {
                var err = '<ul>';
                for(var key in data.errors)
                {
                    err += '<li>' + data.errors[key] + '</li>';
                }
                $.jGrowl(err+'</ul>', {theme: 'red'});
            }
            if (date =='01.'+$('#r_month').val()+'.'+$('#r_year').val()){
                $('.budget #r_year').val('0');
                $('#reload_bdg').click()
            }
            $('#master .button').show()
            $('#master').dialog('close');
            },
        'json')
    });
    $('div.line a.name').live('click',function(){
        $(this).closest('.line').toggleClass('open').toggleClass('close');
        return false;
    })
    $('#reload_bdg').click(function(){
    if ( ($('.budget #r_month').val()!=$('.budget #month').val())||($('.budget #r_year').val()!=$('.budget #year').val()) ) {
        $.post('/budget/load/',
            {
                start:'01.'+$('.budget #month').val()+'.'+$('.budget #year').val()
            },
            function(data)
            {
                model.load(data);
                _$_list = model.print_list($('.budget #r_type').val(),'1');
                _$_group = model.print_info().group;
                $('#list.budget #r_month').val($('.budget #month').val())
                $('#list.budget #r_year').val($('.budget #year').val())
                $('#list.budget .f_field3').html(_$_group);
                $('#list.budget .waste_list form').html(_$_list);
                $('.cont input[value="0.00"]').closest('tr').remove();
                $('.line .amount').each(function(){if ($(this).text()=='0.00') $(this).closest('.line').remove()})
                
                $('.cont').each(function(){
                    var str = '<span>'+$(this).find('input').val()+'</span>'
                    $(this).html(str);
                })
                $('.w2,.w4').css('text-align', 'right')
                if (parseFloat($('.budget .f_field3 .rest span b').text()) < 0)
                    {
                        $('.budget .f_field3 .rest span').css('color','#EB3C34')
                    }
                    else
                    {
                        $('.budget .f_field3 .rest span').css('color','#309500')
                    }
            },
            'json')
         }
         else
         {
            _$_list = model.print_list($('.budget #r_type').val(),'1');
            $('#list.budget #r_month').val($('.budget #month').val())
            $('#list.budget #r_year').val($('.budget #year').val())

            $('#list.budget .waste_list form').html(_$_list);
            $('.line .amount').each(function(){if ($(this).text()=='0.00') $(this).closest('.line').hide()})
            $('.cont input[value="0.00"]').closest('tr').remove();
            $('.w2,.w4').css('text-align', 'right')
            $('.cont').each(function(){
                var str = '<span>'+$(this).find('input').val()+'</span>'
                $(this).html(str);
            })
         }

         $('#list.budget .h .w4').text('Сред.'+$('.budget #r_type option:[selected]').text().toString().replace('ный','')+', руб');

    })
    /**
     * @desc маска для инпута с годом
     */
    $('#sel_date input#year').keyup(function(){
        var str = $('#sel_date input#year').val();
        $('#sel_date input#year').val(str.match(/[0-9]{0,4}/)[0]);
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