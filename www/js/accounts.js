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
        deleteAccount($(this).attr('value'));
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
        g_types = [0,0,0,1,2,0,3,3,3,3,4,0];
        g_name = ['Деньги','Долги мне','Мои долги','Инвестиции','Имущество'];
        var arr = ['','','','',''];
        $.post('/accounts/accountslist/',
            {},
            function(data){
                len = data.length;
                div = "<div class='cont'>&nbsp;<ul>\n\
                        <li class='edit'><a></a></li>\n\
                        <li class='del'><a></a></li>\n\
                      </ul></div>";
                //str = '';
                //str= "<tr><th><ob>Название</b></th><th><b>Тип счета</b></th><th><b>Комментарий</b></th><tr>";
                $('#operation_list').empty();
                for (key in data )//пробег по категориям счетов @todo
                {
                    //i = data[key]['type'];
                    //str = '<b>'+ g_name[data[key]['type']] + '</b>';// + data[i]['summ'];
                    str = '<tr id="item">';

                        for( k in data[key]['fields'])//добавляются все поля
                        {
                            str = str + '<td id='+k+'>';

                                str = str +data[key]['fields'][k]+ '</td>';
                  
                        }
                    str = str+'<td id="mark">'+ div +'</td></tr>';
                    //alert(g_types[data[key]['type']]);
                    i = g_types[data[key]['type']];
                    
                    arr[i] = arr[i]+str;
                    //alert(arr[i]);
                    //todo hide show
                }
                
                for(key in arr)
                {
                    s='<b>'+ g_name[key] + '</b><table>'+arr[key]+'</table>';
                    if (arr[key])
                    $('#operation_list').append(s);
                }
                $('#item td').hide();
                $('#item td#name').show();
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
            $.post('/accounts/del/',
                {id :$(this).attr('id') },
                function(data){},
                'text');
            $(this).closest('#item').empty();
        }
    );
    //edit account lick
    $('li.edit').live('click',
        function(){
                id =$(this).attr('id');
                aid = $(this).closest('div').find('li.del').attr('id');
                tid = id;
               changeTypeAccount(id);
               $('#blockCreateAccounts').show();
               
            $.post('/accounts/get_fields/',
            {id :id,
             aid : $(this).closest('div').find('li.del').attr('id')},
            function(data){
                for(key in data)
                {                
                    $('#blockCreateAccounts').find('#'+key).val(data[key]) ;
                }
                new_acc = 0;
            },
            'json');
        }
    );




    function changeTypeAccount(id) {
        $('#loader').html('Подождите, идет загрузка...');
        $('#information_text').hide();
        $.ajax({
            type: "POST",
            url: "/accounts/changeType/",
            data: {
                id: id
            },
            success: function(data) {
                $('#account_fields').html(data);
                $('#loader').html(' ');
                $('#information_text').show();
            }
        });
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
        $('#loader').html('Подождите, идет сохранение...');
        $('#information_text').hide();
        var qString = $("#formAccount").formSerialize();
        $.post(
            "/accounts/correct/",
            {
                qString: qString,
                aid :aid,
                tid :tid
            },
            function(data) {
                $('#loader').html(' ');
                $('#dataAccounts').html(data);
                $('#information_text').show();
                update_list();
                accountAddUnvisible();
            },
            'text'
        );
    }
});