// {* $Id: accounts.js 113 2009-07-29 11:54:49Z ukko $ *}
/**
 * Функция реализует доступ к функциям на странице по стандартизированному запросу.
 * Используется восновном при переадресации
 * #add добавить счёт
 * #edit[num] редактировать счёт
 * @return void
 */
function accounts_hash_api(str, clone)
{
    var s = str.toString();

    if (s=='#add') {
        easyFinance.widgets.accountEdit.showForm();
        //easyFinance.widgets.accountEdit.addAccount();
    }

    if(s.substr(0,5)=='#edit') {
        easyFinance.widgets.accountEdit.showForm();

        if (clone)
            easyFinance.widgets.accountEdit.copyAccountById(s.substr(5));
        else
            easyFinance.widgets.accountEdit.editAccountById(s.substr(5));
    } else if(s.substr(0,5)=='#copy') {
        easyFinance.widgets.accountEdit.showForm();
        easyFinance.widgets.accountEdit.copyAccountById(s.substr(5));
    }
}

$(document).ready(function() {
    easyFinance.widgets.accountEdit.init('#widgetAccountEdit', easyFinance.models.accounts, easyFinance.models.currency);
    easyFinance.widgets.accountsJournal.init('#widgetAccountEdit', easyFinance.models.accounts, easyFinance.models.currency);

    accounts_hash_api(document.location.hash)

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
});