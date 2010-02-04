/**
 * @desc Accounts Panel Widget
 * appears in left column
 * @author Andrey [Jet] Zharikov
 */

easyFinance.widgets.accountsPanel = function(){
    // private constants

    // private variables
    var _model = null;
    var _$node = null;

    // public variables

    // public functions
    /**
     * @desc init widget
     * @usage init(nodeSelector, model)
     */
    function init(nodeSelector, model) {
        if (!model)
            return null;

        _$node = $(nodeSelector);

        _model = model;
        $(document).bind('accountsLoaded', redraw);
        $(document).bind('accountAdded', redraw);
        $(document).bind('accountDeleted', redraw);

       $('.accounts .add').click(function(){
           document.location='/accounts/#add';
           // временный хак до полного перехода на аякс
           // отображает форму создания счёта
          $('#addacc').click();
       })

       $('.accounts li a').live('click',function(){
            var id = $(this).find('div.id').attr('value').replace("edit", "");
            document.location='/operation/#account='+id;
            $('tr.item#'+$(this).find('div.id').attr('value')).dblclick();
            //временный хак до полного перехода на события

            if (easyFinance.widgets.operationEdit && pathName == '/operation/'){
                easyFinance.widgets.operationEdit.showForm();
                easyFinance.widgets.operationEdit.setAccount(id);
            }

            if (easyFinance.widgets.operationsJournal){
                easyFinance.widgets.operationsJournal.setAccount(id);
                easyFinance.widgets.operationsJournal.loadJournal();
            }
       })

        return this;
    }

    function redraw(){
        var g_types = [0,0,0,0,0,0,1,2,2,2,3,3,3,3,4,0,0];
        var g_name = ['Деньги','Долги мне','Мои долги','Инвестиции','Имущество'];//названия групп
        var arr = ['','','','',''];//содержимое каждой группы
        var summ = [0,0,0,0,0];// сумма средств по каждой группе
        var val = {};//сумма средств по каждой используемой валюте

        if (!_model)
            return;

        var data = $.extend({},_model.getAccounts());

        if (!data){
            data = {};
        }
        
        var i = 0;
        var total = 0;
        var str = '';
        var key;
        var s = '';
        for (key in data )
        {
            i = g_types[data[key]['type']];
            str = '<li><a>';
            str = str + '<div style="display:none" class="type" value="'+data[key]['type']+'" />';
            str = str + '<div style="display:none" class="id" value="'+data[key]['id']+'" />';
            str = str + '<span>'+data[key]['name']+'</span><br>';
            str = str + '<span class="noTextDecoration ' + (data[key]['totalBalance']>=0 && data[key]['type']!='8' ? 'sumGreen' : 'sumRed') + '">'
                + ((data[key]['type']!=7 && data[key]['type']!=8) ? formatCurrency(data[key]['totalBalance']) : formatCurrency(-data[key]['totalBalance'])) + '</span>&nbsp;';
            str = str + res.currency[data[key]['currency']]['text'] + '</span></a></li>';
            //if ( i!=2 ){
            // перевод в валюту по умолчанию
                summ[i] = summ[i]+data[key]["totalBalance"] * res.currency[data[key]["currency"]]['cost'] / res.currency[res.currency['default']]['cost'];
            /*}else{
                summ[i] = summ[i]-data[key]['defCur'];
            }*/

            if (!val[data[key]['currency']]) {
                val[data[key]['currency']]=0;
            }

            //if ( i!=2 ){
            val[data[key]['currency']] = parseFloat( val[data[key]['currency']] )
                + parseFloat(data[key]['totalBalance']);
            /*}else{
                 val[data[key]['currency']] = parseFloat( val[data[key]['currency']] )
                - parseFloat(data[key]['totalBalance']);
            }*/

            arr[i] = arr[i]+str;
        }
        total = 0;
        for(key in arr)
        {
            total = total+parseFloat(summ[key]);
            s='<ul>'+arr[key]+'</ul>';
            if (key>=0 && key <=6)
                _$node.find('#accountsPanelAcc'+key).html(s);
            if (s!='<ul></ul>')
                _$node.find('#accountsPanelAcc'+key).show().prev().show();
            else
                _$node.find('#accountsPanelAcc'+key).hide().prev().hide();
        }

        // формирование итогов
        str = '<ul>';

        //for(key in res['currency'])
        //    break;
        //var c_key = res['currency'][key]['text'] || '';
        
        i = 0;
        for(key in val) {
            i++;
            str = str+'<li><div class="' + (val[key]>=0 ? 'sumGreen' : 'sumRed') + '">'+formatCurrency(val[key])+' '+res.currency[key].text+'</div></li>';
        }
        str = str+'<li><div class="' + (total>=0 ? 'sumGreen' : 'sumRed') + '"><strong>Итого:</strong> <br>'+formatCurrency(total)+' '+res.currency[res.currency['default']].text+'</div></li>';
        str = str + '</ul>';
        _$node.find('#accountsPanel_amount').html(str);
        $('div.listing dl.bill_list dt').addClass('open');
        $('div.listing dl.bill_list dt').live('click', function(){
            $(this).toggleClass('open').next().toggle();
            //запоминание состояние в куку
            var accountsPanel = '';
            $('div.listing dl.bill_list dd:visible').each(function(){
                accountsPanel += $(this).attr('id');
            })
            var isSecure = window.location.protocol == 'https'? 1:0
            $.cookie('accountsPanel_stated', accountsPanel, {expire: 100, path : '/', domain: false, secure : isSecure});
            return false;
        });
        //загружает состояние из
        var accountsPanel = $.cookie('accountsPanel_stated');
        if (accountsPanel){
            $('div.listing dl.bill_list dt:visible').each(function(){
                if (accountsPanel.toString().indexOf($(this).next().attr('id')) == -1)
                    $(this).click()
            })
        }
        
        $('div.listing dd.amount').live('click', function(){
            $(this).prev().click();
            return false;
        });
        
        //$('div.listing dl.bill_list dt').click();
        //$('div.listing dl.bill_list dt:last').click().addClass('open');
        //$('div.listing dl.bill_list dt').click().addClass('open');
    }

    // reveal some private things by assigning public pointers
    return {
        init: init,
        redraw: redraw
    };
}(); // execute anonymous function to immediatly return object
