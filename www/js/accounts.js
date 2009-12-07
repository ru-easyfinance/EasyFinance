// {* $Id: accounts.js 113 2009-07-29 11:54:49Z ukko $ *}
/**
 * Функция реализует доступ к функциям на странице по стандартизированному запросу.
 * Используется восновном при переадресации
 * #add добавить счёт
 * #edit[num] редактировать счёт
 * @return void
 */
function hash_api(str,flag)
{
    var s = str.toString();
    if (s=='#add')
    {
        easyFinance.widgets.accountEdit._isEditing = false;
        showForm();
        $('#type_account').removeAttr('disabled');
    }
    if(s.substr(0,5)=='#edit')
    {
        $('#blockCreateAccounts').show();
        easyFinance.widgets.accountEdit._isEditing = true;
        var account = easyFinance.models.accounts.getAccounts()[s.substr(5)];
        if (!account)
            return false;
        tid = account.type;
        $.post(
            "/accounts/changeType/",
            {
                id: account.type,
                accid: account.id
            },
            function(data) {
                $('#account_form_fields').html(data);
                var key,val;
                $('select,input,textarea','#blockCreateAccounts tr td').each(function(){
                    key = $(this).attr('id');
                    val = account[key];
                    if (val){
                        $(this).val(val);
                    }

                });
                $(document).scrollTop(300);
                $('#type_account').val(account.type);

                $('#type_account').attr('disabled', 'disabled');

                $('#account_form_fields table').attr('id',account.id);
                $('#account_form_fields table').append('<input type="hidden" name="id" class="id" value="'+account.id+'" />');
                if (flag)
                {
                    easyFinance.widgets.accountEdit._isEditing = false;
                    $('#type_account').removeAttr('disabled');
                    $('#account_form_fields table').attr('id','0');
                    $('#account_form_fields table input.id').val('0');
                    var bk_val = $('#blockCreateAccounts input#name').val();
                    $('#blockCreateAccounts input#name').val('Копия_' + bk_val);
                }

            },
            'text'/* /@todo заменить на ясон; требует изменения модели и контроллера*/
        );
    }
}

$(document).ready(function() {
    easyFinance.widgets.accountEdit.init('#widgetAccountEdit', easyFinance.models.accounts);
    easyFinance.widgets.accountsJournal.init('#widgetAccountEdit', easyFinance.models.accounts);

    // @todo isEditing заменить на пуск события

    /**
     * Переводит произвольную строку в вещественное число
     * Пример: фы1в31ф3в1в.ф3ю.132вы переведёт в 13131.3132
     * @return float
     */
    function tofloat(s)
    {
        var str = s.toString();
        var l = str.length;
        var rgx = /[0-9\-\.]/;
        var newstr ='';
        for(var a=0;a<l;a++)
            {

                rgx.test(str[a])
                newstr +=str[a]
            }
        return parseFloat(newstr);
    }

    /**
     * хак для операций.подгружает счёт при его добавлении сразу(раньше требовалось обновить страницу).
     * @param {}//.id - accoun id; .cur_id - currency id
     * @return void
     */
    /*
    function hack4operation(param)
    {
        var id = param.id;
        var cur_id = param.cur_id;
        var opt =   '<option currency="' + cur_id +
                    '" value="' + id +
                    '" >' + account_list[id]['name'] +
                    '(' + account_list[id]['cur'] +
                    ')</option>';
        $('#op_account').append(opt);
    }
    */
    /**
     * функция - пережиток прошлого;
     * перезагружает account_list и выполняет последующие инструкции;
     * не рекомендуется к использованию
     * @return void
     * @deprecated delete//where rewrite account model, controller
     */
    /*
    function update_list(param)
    {
        $.post('/accounts/accountslist/',
            {},
            function(data){
                if (data == 'n') data=null;
                account_list = data;
                res.accounts = data;
                
                $('li#c2').click();
                
                list();
                var s = location.hash;
                hash_api(s);
                if (param){
                    hack4operation(param);
                }
            },
            'json'
        );
    }
    */
});