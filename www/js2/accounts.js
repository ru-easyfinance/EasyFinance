// {* $Id$ *}
$(document).ready(function() {
    $('#add_account').click(function(){ 
        accountAddVisible();
    });
    $('#btnCancelAdd').click(function(){ 
        accountAddUnvisible();
    });
    $('#type_account').change(function(){ 
        changeTypeAccount($(this).attr('value'));
    });
    $('#btnAddAccount').click(function(){ 
        createNewAccount();
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
            }
        });
    }

    function deleteAccount(id) {
        if (!confirm("Вы действительно хотите удалить эту категорию?")) {
            return false;
        }

        $('#information_text').hide();
        $('#loader').html('Подождите, идет удаление...');

        $.ajax({
            type: "POST",
            url: "/accounts/del/",
            data: {
                id: id
            },
            success: function(data) {
                $('#loader').html(" ");
                $('#dataAccounts').html(data);
                $('#information_text').show();
            }
        });
    }
});