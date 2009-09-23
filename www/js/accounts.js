// {* $Id: accounts.js 113 2009-07-29 11:54:49Z ukko $ *}
$(document).ready(function()
{
    /**
     * переводит число типа 12341.34535 в 12 341.35
     * для удобного отображения в виде баланса денег
     * @return string
     */
    function formatCurrency (num) {
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

    $('ul:last li.active').qtip({
        content: 'This is an active list element',
        show: 'mouseover',
        hide: 'mouseout'
    })



    /**
     * Переводит произвольную строку в вещественное число
     * Пример: фы1в31ф3в1в.ф3ю.132вы переведёт в 13131.3132
     * @return float
     */
    function tofloat(s)
    {
        var str = s.toString();
        var l = str.length;
        var rgx = /[0-9.]/;
        var newstr ='';
        for(var a=0;a<l;a++)
            {

                rgx.test(str[a])
                newstr +=str[a]
            }
        return parseFloat(newstr);
    }
    /**
     * Печатает счета на траницу из глобального массива счетов
     * @return void
     */
    function list()
    {
        var g_types = [0,0,0,0,0,0,1,2,2,2,3,3,3,3,4,0];//@todo Жуткий масив привязки типов к группам
        var g_name = ['Деньги','Долги мне','Мои долги','Инвестиции','Имущество'];//названия групп
        var spec_th = [ '',
                    '<th Style="display:none">% годовых</th><th Style="display:none">Доходность, % годовых</th>',
                    '<th Style="display:none">% годовых</th><th Style="display:none">Доходность, % годовых</th><th Style="display:none">Изменение с даты открытия</th>',
                    '<th Style="display:none">% годовых</th>',
                    '<th Style="display:none">Доходность, % годовых</th><th Style="display:none">Изменение с даты открытия</th>'];//доп графы для групп
        var arr = ['','','','',''];//содержимое каждой группы
        var summ = [0,0,0,0,0];// сумма средств по каждой группе
        var val = {};//сумма средств по каждой используемой валюте
        var div = "<div class='cont'>&nbsp;<ul style='z-index: 100'>\n\
                        <li class='edit' title='Редактировать'><a></a></li>\n\
                        <li class='del' title='Удалить'><a></a></li>\n\
                        <li class='add' title='Копировать'><a></a></li>\n\
                    </ul></div>";
        $('#operation_list').empty();
        var type, str='';
        var total = 0,
            head_tr='   <tr>\n\
                            <th> \n\
                                Имя \n\
                            </th>\n\
                            <th COLSPAN=2 Style="padding-left:40px"> \n\
                                Остаток \n\
                            </th>\n\
                            <th> \n\
                                Рублёвый эквивалент \n\
                            </th>\n\
                            <th></th>\n\
                        <tr>',
            s='';
        for (var key in account_list )// формирует массив с таблицей счетов по группам
        {
            type = g_types[account_list[key]['type']];
            if (!isNaN(type)){
                str = '<tr class="item" id="' + account_list[key]['id'] + '">';
                str = str + '<td class="name">' + account_list[key]["name"] + '</td>';
                str = str + '<td class="total_balance">' + formatCurrency(account_list[key]["total_balance"]) + '</td>';
                str = str + '<td class="cur">' + account_list[key]["cur"] + '</td>';
                str = str + '<td class="def_cur">' + formatCurrency(account_list[key]["def_cur"]) + '</td>';
                summ[type] = summ[type]+account_list[key]['def_cur'];
                if (!val[account_list[key]['cur']]) {
                    val[account_list[key]['cur']]=0;
                }
                val[account_list[key]['cur']] = tofloat( val[account_list[key]['cur']] )
                    + tofloat(account_list[key]['total_balance']);
                str = str + '<td class="mark no_over">' + div + '</td></tr>';
                arr[type] = arr[type] + str;
            }
        }
        for(key in arr)//выводит конечный массив
        {
            if (arr[key]){
                total = total+tofloat(summ[key]);
                s='<div><strong class="title">'+ g_name[key]
                    + '</strong> : ' + formatCurrency(tofloat(summ[key]))
                    + ' руб.<table  class="noborder">' + head_tr+arr[key]
                    + '</table></div>';
                $('#operation_list').append(s);
            }
        }
/////////////////////формирование итогового поля//////////////////////
        str='<strong class="title"> Итог </strong><table class="noborder">\n\
            <tr><th>Сумма</th><th>Валюта</th></tr>';
        for(key in val)
        {
            str = str+'<tr><td>'+formatCurrency(val[key])+'</td><td>'+key+'</td></tr>';
        }
        str = str+'<tr><td><b>Итого : </b>&nbsp;' + formatCurrency(total) + '</td><td> руб.</td></tr>';
        str = str + '</table>';
        $('#operation_list').append(str);
////////////////////////////////////////////////////////////////@todo перенести в цсс
        $('.item td.cur').css('width','50px');
        $('.item td.total_balance').css('text-align','right').css('padding-right','0');
//////////////////////////////////////////////////////scrollbar
        $('.operation_list').jScrollPane();
    }
    /**
     * Функция реализует доступ к функциям на странице по стандартизированному запросу.
     * Используется восновном при переадресации
     * #add добавить счёт
     * #edit[num] редактировать счёт
     * @return void
     */
    function hash_api(str)
    {
        var s = str.toString();
        if (s=='#add')
        {
            new_acc = 1;
            accountAddVisible();
        }
        if(s.substr(0,5)=='#edit')
        {
            $('#blockCreateAccounts').show();
            new_acc=0;
            var account = account_list[s.substr(5)];
            if (!account)
                return false;
            tid = account.type;
            $.post(
                "/accounts/changeType/",
                {
                    id: account.type
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
                    $('#account_form_fields table').attr('id',account.id);
                    $('#account_form_fields table').append('<input type="hidden" name="id" class="id" value="'+account.id+'" />');
                },
                'text'//@todo заменить на ясон; требует изменения модели и контроллера
            );
        }
    }
    /**
     * хак для операций.подгружает счёт при его добавлении сразу(раньше требовалось обновить страницу).
     * @param {}//.id - accoun id; .cur_id - currency id
     * @return void
     */
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
    /**
     * скрывает поле с добавлением счёта
     * @return void
     */
    function accountAddUnvisible() {
        $('#blockCreateAccounts').hide();
    }
    /**
     * раскрывает поле с добавлением счёта
     * @return void
     */
    function accountAddVisible() {
        changeTypeAccount($('#type_account').val());
        $('#blockCreateAccounts').show();
        $('#blockCreateAccounts').val('');
    }
    /**
     * функция - пережиток прошлого;
     * перезагружает account_list и выполняет последующие инструкции;
     * не рекомендуется к использованию
     * @return void
     * @deprecated delete//where rewrite account model, controller
     */
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
    /**
     * функция - пережиток прошлого;
     * перезагружает форму ввода счёта;
     * @return void
     * @deprecated rewrite without Ajax//where rewrite account model, controller, admin
     */
    function changeTypeAccount(id)
    {
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
    /**
     * функция добавляет новый счёт
     * @return void
     * @deprecated rewrite without update_list//on freetime
     */
    function createNewAccount()
    {
        var cur_id = $("#formAccount select:[name='currency_id']").val();
        $.ajax({
            type: "POST",
            url: "/accounts/add/",
            data: $("#formAccount input,select,textarea"),
            success: function(data) {
                var id = data;
                $.jGrowl("Добавлен счёт", {theme: 'green'});
                update_list({id: id,cur_id: cur_id});
                accountAddUnvisible();
                
                $('li#c2').click()
            }
        });
    }
    /**
     * функция редактирует счёт
     * @return void
     * @deprecated rewrite all//on freetime where rewrite account model, controller, admin
     */
    function correctaccount()
    {
        $.post('/accounts/del/',
            {
                id :$('#blockCreateAccounts').find('table').attr('id')
            },
            function(data){
                $.jGrowl("Счёт сохранён", {theme: 'green'});
            },
            'text'
        );
        createNewAccount();
    }
    /**
     * Красивый тултип для таблицы счетов
     * @param line JQuery link to elem
     * @param text текст для подсказки
     * @return void
     */
    function print_qtip(line,text)
    {
         $('.qtip').remove();
         $(line).qtip({
                   content: text.toString(), // Set the tooltip content to the current corner
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
                      tip: true // Give them tips with auto corner detection
                   }
                });
    }

///////////////////////////////////////////////////////////views
    var new_acc = 1;
    var tid;
    var account_list;
    // upload account
    update_list();

    $('#addacc').live('click',function(){////button add account click
        new_acc = 1;
        accountAddVisible();
    });
    $('#btnCancelAdd').click(function(){ ////button cancel in form click
        accountAddUnvisible();
    });
    /**
     * select type in form selected change
     * @deprecated delete //where rewrite account model, controller, admin
     */
    $('#type_account').change(function(){
        changeTypeAccount($(this).attr('value'));
    });
    
    $('#btnAddAccount').click(function(){////button save in form click
        var str = $('#blockCreateAccounts #name').val();
        var id =$('#blockCreateAccounts').find('table').attr('id');
        var l = 1;
        $('.item .name').each(function(){
            if (id != $(this).closest('tr').find('.id').attr('value')){
                if($(this).text()==str)
                    l=0;
            }
        });
        if (l){
            if (new_acc)
            {
                createNewAccount();
            }
            else
            {
                correctaccount();
            }
        }
        else
        {
            $.jGrowl("Такой счёт уже существует!", {theme: 'red'});
        }
    });
    $('tr.item').live('mouseover',
        function(){
            var g_types = [0,0,0,0,0,0,1,2,2,2,3,3,3,3,4,0];//@todo Жуткий масив привязки типов к группам
            var spec_th = [ [],
                        ['<th>% годовых</th>',
                            '<th>Доходность, % годовых</th>'],
                        ['<th>% годовых</th>',
                            '<th>Доходность, % годовых</th>',
                            '<th>Изменение с даты открытия</th>'],
                        ['<th>% годовых</th>'],
                        ['<th>Доходность, % годовых</th>',
                            '<th>Изменение с даты открытия</th>']];//доп графы для групп
            var id =$(this).attr('id');
            var account = account_list[id];
            var spec = spec_th[g_types[account.type]];
             
            var str = '<table Stile="padding:3px">';
            str +=  '<tr><th> Название </th><td style="width:5px">&nbsp;</td><td>'+
                        account.name + '</td>';
            str +=  '<tr><th> Описание </th><td style="width:5px">&nbsp;</td><td>'+
                        account.description + '</td>';
            str +=  '<tr><th> Остаток </th><td style="width:5px">&nbsp;</td><td style="width:95px">'+
                formatCurrency(account.total_balance) + ' ' +account.cur + '</td>';
            str +=  '<tr><th style="max-width:150px"> Остаток в валюте по умолчанию</th><td style="width:10px">&nbsp;</td><td>'+
                formatCurrency(account.def_cur) + '</td>';

            for(var key in spec)
            {
                str +='<tr>'+spec[key]+account.special[key]+'</tr>'
            }
///
            str += '<table>';
            print_qtip(this, str);
            $('tr.item').removeClass('act');
            $(this).addClass('act');
            
    });
    $('tr.item').live('dblclick',
        function(){
             $(this).find('li.edit').click();
        });

    $('body').mousemove(function(){
            if (!$('ul:hover').length && !$('.act:hover').length) {
                $('.qtip').remove();
                $('tr.item').removeClass('act');
            }
    });
    //del accoun click
    $('li.del').live('click',
        function(){
            if (confirm("Вы уверены что хотите удалить счёт?")) {
                var id = $(this).closest('.item').attr('id')
                $.post('/accounts/del/',
                    {id :id},
                    function(data){
                        var val;
                        $('#op_account option').each(function(){
                            val = $(this).val();
                            if (val == id) {
                                $(this).remove();
                            }
                        })
                        update_list();
                        //list();
                        $.jGrowl("Счёт удалён", {theme: 'green'});
                    },
                    'json');
                
            }
        }
    );
    //edit account lick
    $('li.edit').live('click',
        function(){
            $('#blockCreateAccounts').show();
            var id = $(this).closest('.item').attr('id');
            hash_api('#edit'+id);
        }
    );

    $('li.add').live('click',
        function(){
            $('#blockCreateAccounts').show();
            var id = $(this).closest('.item').attr('id');
            hash_api('#edit'+id);
            new_acc=1;

            $('#blockCreateAccounts input#name').val('');
        }
    );
});