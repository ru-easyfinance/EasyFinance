// {* $Id: accounts.js 113 2009-07-29 11:54:49Z ukko $ *}
$(document).ready(function() {
    var new_acc = 1;
    var aid;
    var tid;
    $('#add_account').click(function(){
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

    //$('#statement_date').click(function(){ alert('123'); $("#statement_date").datepicker(); })
    $('#statement_date').focus(function(){ 
        alert('123');
    });

    $('.cat_tr').hover(function () {
        $(this).css('backgroundColor','#F8F6EA');
        $('#ico_del_' + $(this).attr('value')).show();
    }, function () {
        $(this).css('backgroundColor','#FFFFFF');
        $('#ico_del_' + $(this).attr('value')).hide();
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
        $.post('/accounts/accountslist/',
            {},
            function(data){
                len = data.length;
                str= '';
                $('table#list tr.item').empty();
                for (i = 0;i < len;i++ )
                {
                    str = str + '<tr class="item"><td>'+
                        data[i]['account_name']+'</td><td>'+
                        data[i]['account_type_name']+'</td><td>'+
                        data[i]['account_description']+'<div class="cont"><ul>'
                                    +'<li class="edit" id="'+data[i]['account_type_id']+'">Редактировать</li>'
                                    +'<li class="del" id="'+data[i]['account_id']+'">Удалить</li>'
                                    +'</ul></div></td></tr>';
                }
                $('table#list').append(str);
                $('div.cont').hide();
            },
            'json'
        );
    };
    update_list();
    //acount click
    $('tr.item').live('click',
        function(){
             $('div.cont').hide();
            $(this).find('div.cont').show();
    });
    //del accoun click
    $('li.del').live('click',
        function(){
            $.post('/accounts/del/',
                {id :$(this).attr('id') },
                function(data){},
                'text');
            $(this).closest('tr').empty();
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
            },
            'text'
        );
    }
});