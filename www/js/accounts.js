// {* $Id: accounts.js 113 2009-07-29 11:54:49Z ukko $ *}
$(document).ready(function() {
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
    }

    // upload account
    function update_list()
    {
        g_types = [0,0,0,1,2,0,3,3,3,3,4,0];//Жуткий масив привязки типов к группам
        g_name = ['Деньги','Долги мне','Мои долги','Инвестиции','Имущество'];//названия групп
        spec_th = [ '',
                    '<th>% годовых</th><th>Доходность, % годовых</th>',
                    '<th>% годовых</th>Доходность, % годовых<th></th><th>Изменение с даты открытия</th>',
                    '<th>% годовых</th>',
                    '<th>Доходность, % годовых</th><th>Изменение с даты открытия</th>'];//доп графы для групп
        var arr = ['','','','',''];//содержимое каждой группы
        var summ = [0,0,0,0,0];// сумма средств по каждой группе
        var val = {};//сумма средств по каждой используемой валюте
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
                    str = '<tr id="item">';
                    str = str + '<td id="type" value="'+data[key]['type']+'"></td>';
                    str = str + '<td id="id" value="'+data[key]['id']+'"></td>';
                        for( k in data[key]['fields'])//добавляются все поля
                        {
                            str = str + '<td id='+k+' value='+data[key]['fields'][k]+'>';
                            str = str +data[key]['fields'][k]+ '</td>';
                        }
                    str = str + '<td id="cur" value="'+data[key]['cur']+'">'+data[key]['cur']+'</td>';
                    str = str + '<td id="def_cur" value="'+data[key]['def_cur']+'">'+data[key]['def_cur']+' руб.</td>';
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
                            str = str + '<td id="special" value="'+data[key]['special'][0]+'">'+data[key]['special'][0]+'%</td>';
                            str = str + '<td id="special" value="'+data[key]['special'][1]+'">'+data[key]['special'][1]+'%</td>';
                            break;
                        case 2:
                            str = str + '<td id="special" value="'+data[key]['special'][0]+'">'+data[key]['special'][0]+'%</td>';
                            str = str + '<td id="special" value="'+data[key]['special'][1]+'">'+data[key]['special'][1]+'%</td>';
                            str = str + '<td id="special" value="'+data[key]['special'][2]+'">'+data[key]['special'][2]+'</td>';
                            break;
                        case 3:
                            str = str + '<td id="special" value="'+data[key]['special'][0]+'">'+data[key]['special'][0]+'%</td>';
                            break;
                        case 4:
                            str = str + '<td id="special" value="'+data[key]['special'][1]+'">'+data[key]['special'][1]+'%</td>';
                            str = str + '<td id="special" value="'+data[key]['special'][2]+'">'+data[key]['special'][2]+'</td>';
                            break;
                    }
                    str = str+'<td id="mark">'+ div +'</td></tr>';
                    arr[i] = arr[i]+str;

                    //todo hide show
                }
                total = 0;
                for(key in arr)
                {
                    total = total+(parseInt(summ[key]*100))/100;
                    head_tr = '<tr>\n\
                                    <th> \n\
                                        Имя \n\
                                    </th>\n\
                                    <th> \n\
                                        Описание \n\
                                    </th>\n\
                                    <th> \n\
                                        Остаток \n\
                                    </th>\n\
                                    <th> \n\
                                        Валюта \n\
                                    </th>\n\
                                    <th> \n\
                                        Рублёвый эквивалент \n\
                                    </th>';
                    head_tr = head_tr + spec_th[key];
                    head_tr = head_tr + '<tr>';
                    s='<b>'+ g_name[key] + '</b> : '+(parseInt(summ[key]*100))/100+' руб.<table>'+head_tr+arr[key]+'</table>';
                    if (arr[key])
                    $('#operation_list').append(s);
                }
                /////////////////////формирование итогового поля//////////////////////
                str='<b> Итог </b><table>\n\
                        <tr><th>Сумма</th><th>Валюта</th></tr>';
                for(key in val)
                {
                    str = str+'<tr><td>'+val[key]+'</td><td>'+key+'</td></tr>';
                }
                str = str+'<tr><td><b>Итого:</b>  '+total+'</td><td> руб.</td></tr>';
                str = str + '</table>';
                 $('#operation_list').append(str);
                ////////////////////////////////////////////////////////////////


                $('#item td').hide();
                $('#item td#name').show();
                $('#item td#cur').show();
                $('#item td#def_cur').show();
                $('#item td#special').show();
                $('#item td#description').show();
                $('#item td#total_balance').show();
                $('#item td#mark').show();     
            },
            'json'
        );
    };
    update_list();
    //acount click
    
    $('tr#item').live('mouseover',
        function(){
            $('tr#item').removeAttr('class');
            $(this).attr('class','act');
    });

    $('tr#item').live('mouseout',
        function(){
            $(this).removeAttr('class');
    });
    //del accoun click
    $('li.del').live('click',
        function(){
            if (confirm("Вы уверены что хотите удалить счёт?"))
            {
                $.post('/accounts/del/',
                    {id :$(this).closest('#item').find('#id').attr('value') },
                    function(data){},
                    'text');
                $(this).closest('#item').empty();
                return false;
            }
        }
    );
    //edit account lick
    $('li.edit').live('click',
        function(){
                $('#blockCreateAccounts').show();
                id =$(this).closest('#item').find('#type').attr('value');
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
                        $(th).closest('#item').find('td').each(function(){
                            key = $(this).attr('id');
                            val = $(this).text();
                            $('#blockCreateAccounts').find('#'+key).val(val) ;

                        });
                    },
                    'text'
                );
                
        }
    );




    function changeTypeAccount(id) {
        $('#loader').html('Подождите, идет загрузка...');
        $('#information_text').hide();
        $.post(
            "/accounts/changeType/",
            {
                id: id
            },
             function(data) {
                $('#account_fields').html(data);
                $('#loader').html(' ');
                $('#information_text').show();
            },
            'text'
        );
    }

        function createNewAccount() {
        $('#loader').html('Подождите, идет сохранение...');
        $('#information_text').hide();
        var qString = $("#formAccount").formSerialize();
        $.ajax({
            type: "POST",
            url: "/accounts/add/",
            data: {
                qString: qString,
                ajax: true
            },
            success: function(data) {
                $('#loader').html(' ');
                $('#dataAccounts').html(data);
                $('#information_text').show();
                update_list();
                accountAddUnvisible();
            }
        });
    }

    function correctaccount() {
        $.post('/accounts/del/',
                    {id :$('#blockCreateAccounts').find('input#name').attr('value') },
                    function(data){},
                    'text');
        createNewAccount();
    }
});