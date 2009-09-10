// {* $Id: accounts.js 113 2009-07-29 11:54:49Z ukko $ *}

$(document).ready(function() {







$('#header').qtip({
               content: 'corners[i]', // Set the tooltip content to the current corner
               position: {
                  corner: {
                     tooltip: 'rightMiddle', // Use the corner...
                     target: 'leftMiddle' // ...and opposite corner
                  }
               },
               show: {
                  when: false, // Don't specify a show event
                  ready: true // Show the tooltip when ready
               },
               hide: false, // Don't specify a hide event
               style: {
                  border: {
                     width: 5,
                     radius: 10
                  },
                  padding: 10,
                  textAlign: 'center',
                  tip: true, // Give it a speech bubble tip with automatic corner detection
                  name: 'cream' // Style it according to the preset 'cream' style
               }
            });










        function formatCurrency(num) {
        if (num=='undefined') num = 0;
        //num = num.toString().replace(/\$|\,/g,'');
        if(isNaN(num)) num = "0";
        sign = (num == (num = Math.abs(num)));
        num = Math.floor(num*100+0.50000000001);
        cents = num%100;
        num = Math.floor(num/100).toString();
        if(cents<10)
            cents = "0" + cents;
        for (var i = 0; i < Math.floor((num.length-(1+i))/3); i++)
            num = num.substring(0,num.length-(4*i+3))+' '+
            num.substring(num.length-(4*i+3));
        return (((sign)?'':'-') + '' + num + '.' + cents);
    }

    var new_acc = 1;
    var aid;
    var tid;
    $('#addacc').click(function(){
        new_acc = 1;
        accountAddVisible();
    });
    $('#btnCancelAdd').click(function(){ 
        accountAddUnvisible();
    });
    $('#type_account').change(function(){ 
        changeTypeAccount($(this).attr('value'));
    });
    $('#btnAddAccount').click(function(){
        if (new_acc)
            createNewAccount();
        else
            correctaccount();
    });
    $('.delAccount').click(function(){
        
    });

    function accountAddUnvisible() {
        $('#blockCreateAccounts').hide();
    }

    function accountAddVisible() {
        changeTypeAccount($('#type_account').val());
        $('#blockCreateAccounts').show();
        $('#blockCreateAccounts').val('');
    }

    function in_array(a,arr)
    {
        for (k in arr)
        {
            if (a==arr[k])
                return true;
        } 
        return false;
    }
    // upload account
    function update_list()
    {
        g_types = [0,0,0,0,0,0,1,2,2,2,3,3,3,3,4,0];//@todo Жуткий масив привязки типов к группам
        g_name = ['Деньги','Долги мне','Мои долги','Инвестиции','Имущество'];//названия групп
        spec_th = [ '',
                    '<th Style="display:none">% годовых</th><th Style="display:none">Доходность, % годовых</th>',
                    '<th Style="display:none">% годовых</th><th Style="display:none">Доходность, % годовых</th><th Style="display:none">Изменение с даты открытия</th>',
                    '<th Style="display:none">% годовых</th>',
                    '<th Style="display:none">Доходность, % годовых</th><th Style="display:none">Изменение с даты открытия</th>'];//доп графы для групп
        var arr = ['','','','',''];//содержимое каждой группы
        var summ = [0,0,0,0,0];// сумма средств по каждой группе
        var val = {};//сумма средств по каждой используемой валюте
        var main_keys = ['name','description','total_balance'];
        $.post('/accounts/accountslist/',
            {},
            function(data){
                len = data.length;
                div = "<div class='cont'>&nbsp;<ul>\n\
                        <li class='edit'><a></a></li>\n\
                        <li class='del'><a></a></li>\n\
                      </ul></div>";
                
                $('#operation_list').empty();
                for (key in data )
                {
                    i = g_types[data[key]['type']];
                    str = '<tr class="item">';
                    str = str + '<td class="type" value="'+data[key]['type']+'"></td>';
                    str = str + '<td class="id" value="'+data[key]['id']+'"></td>';
                    for (l in main_keys)
                    {
                        k = main_keys[l];
                        if (k == 'total_balance')
                            str = str + '<td class='+k+'>'+formatCurrency(data[key]['fields'][k])+ '</td>';
                        else
                            str = str + '<td class='+k+'>'+data[key]['fields'][k]+ '</td>';
                    }
                    for( k in data[key]['fields'])//добавляются все поля
                    {
                        if (!in_array(k,main_keys))
                        {
                            str = str + '<td class='+k+'>';// value='+data[key]['fields'][k]+'
                            str = str +data[key]['fields'][k]+ '</td>';
                        }
                    }
                    str = str + '<td class="cat">'+data[key]['cat']+'</td>';
                    str = str + '<td class="cur">'+data[key]['cur']+'</td>';
                    str = str + '<td class="def_cur">'+formatCurrency(data[key]['def_cur'])+' руб.</td>';
                    summ[i] = summ[i]+data[key]['def_cur'];
                    //alert(data[key]['def_cur']);
                    if (!val[data[key]['cur']]) {
                        val[data[key]['cur']]=0;
                    }
                    val[data[key]['cur']] = parseFloat( val[data[key]['cur']] )
                        + parseFloat(data[key]['fields']['total_balance']);
                    switch (i)
                    {
                        case 0:
                            break;
                        case 1:
                            str = str + '<td class="special">'+formatCurrency(data[key]['special'][0])+'%</td>';
                            str = str + '<td class="special">'+formatCurrency(data[key]['special'][1])+'%</td>';
                            break;
                        case 2:
                            str = str + '<td class="special">'+formatCurrency(data[key]['special'][0])+'%</td>';
                            str = str + '<td class="special">'+formatCurrency(data[key]['special'][1])+'%</td>';
                            str = str + '<td class="special">'+formatCurrency(data[key]['special'][2])+'</td>';
                            break;
                        case 3:
                            str = str + '<td class="special">'+formatCurrency(data[key]['special'][0])+'%</td>';
                            break;
                        case 4:
                            str = str + '<td class="special">'+formatCurrency(data[key]['special'][1])+'%</td>';
                            str = str + '<td class="special">'+formatCurrency(data[key]['special'][2])+'</td>';
                            break;
                    }
                    str = str+'<td class="mark no_over">'+ div +'</td></tr>';
                    arr[i] = arr[i]+str;

                    //todo hide show
                }
                total = 0;
                for(key in arr)
                {
                    total = total+(parseFloat(summ[key]*100))/100;
                    head_tr = '<tr>\n\
                                    <th> \n\
                                        Имя \n\
                                    </th>\n\
                                    <th Style="display:none"> \n\
                                        Описание \n\
                                    </th>\n\
                                    <th Style="position:absolute;left:200px"> \n\
                                        Остаток \n\
                                    </th>\n\
                                    <th Style="display:none"> \n\
                                        Тип \n\
                                    </th>\n\
                                    <th Style="position:absolute;right:150px"> \n\
                                        Рублёвый эквивалент \n\
                                    </th>';
                    head_tr = head_tr + spec_th[key];
                    head_tr = head_tr + '<tr>';
                    
                    s='<div><strong class="title">'+ g_name[key] + '</strong> : '+(parseFloat(summ[key]*100))/100+' руб.<table>'+head_tr+arr[key]+'</table></div>';
                    if (arr[key])
                    $('#operation_list').append(s);
                }
                /////////////////////формирование итогового поля//////////////////////
                str='<strong class="title"> Итог </strong><table>\n\
                        <tr><th>Сумма</th><th>Валюта</th></tr>';
                for(key in val)
                {
                    str = str+'<tr><td>'+formatCurrency(val[key])+'</td><td>'+key+'</td></tr>';
                }
                str = str+'<tr><td><b>Итого:</b>  '+formatCurrency(total)+'</td><td> руб.</td></tr>';
                str = str + '</table>';
                 $('#operation_list').append(str);
                ////////////////////////////////////////////////////////////////


                $('.item td').hide();
                $('.item td.name').show();
                $('.item td.cur').show().css('width','50px');
                //$('.item td.cat').show();
                $('.item td.def_cur').show();
                //$('.item td.special').show();
                //$('.item td.description').show();
                $('.item td.total_balance').show().css('text-align','right').css('padding-right','0');
                $('.item td.mark').show();
            },
            'json'
        );
    };
    update_list();
    //acount click
    
    $('tr.item').live('mouseover',
        function(){
            var texts=[];
            var i=0;
            var cur=$(this).find('.cur').text();
            $(this).find('td').each(function(){
                texts[i]=$(this).text();
                cls = $(this).attr('class');
                if(cls == 'total_balance')
                    texts[i] = texts[i] +' '+cur;
                //alert(texts[i]);
                if (texts[i]=='undefined')
                    i = i -1;
                if ((cls =='type')||(cls == 'id')||(cls == 'cur'))
                    i = i -1;
                i++;
            });
            var headers=[];
            i=0;
            $(this).closest('table').find('th').each(function(){
                headers[i]=$(this).text();
                i++;
            });
            str = '<table Stile="padding:3px">';
            for(key in headers)
            {
                str = str + '<tr><th>' +
                        headers[key] + '</th><td style="width:10px">&nbsp;</td><td>'+
                        texts[key] + '</td>';
            }
            str = str + '<table>';
            //alert(texts.toString());
            $(this).qtip({
               content: str, // Set the tooltip content to the current corner
               position: {
                  corner: {
                     tooltip: 'rightMiddle', // Use the corner...
                     target: 'leftMiddle' // ...and opposite corner
                  }
               },
               show: {
                  when: false, // Don't specify a show event
                  ready: true // Show the tooltip when ready
               },
               hide: false, // Don't specify a hide event

               style: {
                  name: 'light',
                  //width: { max: 700 }, // Set a high max width so the text doesn't wrap
                  tip: true // Give them tips with auto corner detection
               }
            });
            $('tr.item').attr('class','item');
            $(this).attr('class','item act');
    });

    $('tr.item').live('mouseout',
        function(){
            if($(this).data("qtip"))
                    $(this).qtip("destroy");
            $(this).attr('class','item');
    });
    //del accoun click
    $('li.del').live('click',
        function(){
            if (confirm("Вы уверены что хотите удалить счёт?"))
            {
                $.post('/accounts/del/',
                    {id :$(this).closest('.item').find('.id').attr('value') },
                    function(data){},
                    'text');
                $(this).closest('.item').empty();
                return false;
            }
        }
    );
    //edit account lick
    $('li.edit').live('click',
        function(){
                $('#blockCreateAccounts').show();
                id =$(this).closest('.item').find('.type').attr('value');
                new_acc=0;
                tid = id;
                var th = $(this);
                $.post(
                    "/accounts/changeType/",
                    {
                        id: id
                    },
                     function(data) {
                        $('#account_fields').html(data);
                        $(th).closest('.item').find('td').each(function(){
                            key = $(this).attr('class');
                            val = $(this).text();
                            $('#blockCreateAccounts').find('#'+key).val(val) ;
                            $(document).scrollTop(300);
                        });
                        val = $(th).closest('.item').find('.total_balance').text();

                        
                        $('#blockCreateAccounts').find('#starter_balance').val(val);
                        //$('#blockCreateAccounts').find('#starter_balance').attr('readonly','readonly');
                        //alert($(th).closest('#item').find('#id').attr('value'));
                        $('#account_fields table').attr('id',$(th).closest('.item').find('.id').attr('value'));
                        $('#account_fields table').append('<input type="hidden" name="id" class="id" value="'+$(th).closest('.item').find('.id').attr('value')+'" />');
                    },
                    'text'
                );
                
        }
    );




    function changeTypeAccount(id) {
        $.post(
            "/accounts/changeType/",
            {
                id: id
            },
             function(data) {
                $('#account_form_fields').html(data);
            },
            'text'
        );
    }

        function createNewAccount() {
        $.ajax({
            type: "POST",
            url: "/accounts/add/",
            data: $("#formAccount input,select,textarea"),
            success: function(data) {
                $('#dataAccounts').html(data);
                update_list();
                accountAddUnvisible();
            }
        });
    }

    function correctaccount() {
        $.post('/accounts/del/',
                    {id :$('#blockCreateAccounts').find('table').attr('id') },
                    function(data){},
                    'text');
        createNewAccount();
    }
});