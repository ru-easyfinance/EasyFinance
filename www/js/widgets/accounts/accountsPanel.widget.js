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
        $(document).bind('accountDeleted', redraw);

        redraw();

       $('.accounts .add').click(function(){
           document.location='/accounts/#add';
           // временный хак до полного перехода на аякс
           // отображает форму создания счёта
          $('#addacc').click();
       })

       $('.accounts li a').live('click',function(){
           document.location='/accounts/#edit'+$(this).find('div.id').attr('value');
           $('tr.item#'+$(this).find('div.id').attr('value')).dblclick();
           //временный хак до полного перехода на события
           hash_api('#edit'+$(this).find('div.id').attr('value'));
       })

        return this;
    }

    function redraw(){
        var g_types = [0,0,0,0,0,0,1,2,0,2,3,3,3,3,4,0];
        var g_name = ['Деньги','Долги мне','Мои долги','Инвестиции','Имущество'];//названия групп
        var arr = ['','','','',''];//содержимое каждой группы
        var summ = [0,0,0,0,0];// сумма средств по каждой группе
        var val = {};//сумма средств по каждой используемой валюте

        var data=$.extend({},_model.getAccounts());

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
            str = str + '<span>'+data[key]['name']+'</span>';
            str = str + '<b>'+formatCurrency(data[key]['total_balance']);
            str = str + data[key]['cur']+ '</b>'+'</a></li>';
            if ( i!=2 ){
                summ[i] = summ[i]+data[key]['def_cur'];
            }else{
                summ[i] = summ[i]-data[key]['def_cur'];
            }

            if (!val[data[key]['cur']]) {
                val[data[key]['cur']]=0;
            }

            if ( i!=2 ){
            val[data[key]['cur']] = parseFloat( val[data[key]['cur']] )
                + parseFloat(data[key]['total_balance']);
            }else{
                 val[data[key]['cur']] = parseFloat( val[data[key]['cur']] )
                - parseFloat(data[key]['total_balance']);
            }

            arr[i] = arr[i]+str;
        }
        total = 0;
        for(key in arr)
        {
            total = total+(parseInt(summ[key]*100))/100;
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
        for(key in res['currency'])
            break;
        var c_key = res['currency'][key]['abbr']||'';
        i = 0
        for(key in val)
        {
            if(!i)
                c_key = key;
            i++;
            str = str+'<li><div>'+formatCurrency(val[key])+' '+key+'</div></li>';
        }
        
        str = str+'<li><div><strong>Итого:</strong> <br>'+formatCurrency(total)+' '+c_key+'</div></li>';//@todo
        str = str + '</ul>';
        _$node.find('#accountsPanel_amount').html(str);
        //$('div.listing dl.bill_list dd').hide();
        $('div.listing dl.bill_list dt').addClass('open');
        $('div.listing dl.bill_list dt').live('click', function(){
            $(this).toggleClass('open').next().toggle();
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