/** $Id: main.js 627 2009-09-30 07:04:37Z rewle $ */
$(document).ready( function(){

// # BIND
$('.add2 span').live('click', function(){
    document.location = '/targets/#add';
});
$('.financobject_block li.del').live('click', function(){
    target = $(this).closest('div.object2');
    if (confirm('Вы уверены что хотите удалить данную Финансовую цель?')) {
        $.post( '/targets/del/', {
                id: target.attr('id')
            }, function() {
                target.remove();
            }, 'json'
         );
    }
});
$('.financobject_block li.edit').live('click', function(){
    document.location = '/targets/#edit/'+$(this).closest('div.object2').attr('id');
});
/*$.post(
    '/info/get_data/',
    {},
    function(data){*/
    function InitInformers(){
        var data = res.informers;
        var name,value,pointer,xml,chartSample_1;
        var titles = ['Фин.состояние','Деньги','Бюджет','Долги','Доходы'];
        var gauges = ['gaugeMain.gif','gaugeMoney.gif','gaugeBudget.gif','gaugeCredits.gif','gaugeExpenses.gif'];

        for (var i=0;i<5;i++)
        {
            if (isNaN(data[i]['color']))
                data[i]['color'] = 1;

            // init gauges
            var title = titles[i];
            $('#flashTitle_'+i).text(title);
            var size = (i==0) ? "107" : "70";
            var flashvars = {title: data[i]['title'], value: data[i]['value'], bgimage: ""};
            var params = {wmode: "transparent"};
            var attributes = {id: "gauge"+i};
            swfobject.embedSWF("/swf/efGauge.swf", "flash_"+i, size, size, "9.0.0", false, flashvars, params, attributes);
        }
        print_targets(0);
    }

    InitInformers();

    /**
     * Выводит список финансовых целей пользователя
     */
    function print_targets(count) {
        var data = res['user_targets'];
        var str ='';

        for (var key in data) if (data[key]['close']==0) {
            /*if(!data[key]['image']) {
                data[key]['image']='/img/i/fintarget.jpg';

            }*/
            //alert(data[key]['category']);
            data[key]['image']='/img/i/fintarget.jpg';
            if (data[key]['category']==1)
                data[key]['image']='/img/i/home.png';
            if (data[key]['category']==2)
                data[key]['image']='/img/i/avto.png';
            if (data[key]['category']==3)
                data[key]['image']='/img/i/rest.png';
            if (data[key]['category']==6)
                data[key]['image']='/img/i/wedd.png';
            if (data[key]['category']==7)
                data[key]['image']='/img/i/bitv.png';
            if (data[key]['category']==8)
                data[key]['image']='/img/i/comp.png';

            str += "<div class='object2' id='"
                + key + "'><!--<a class='advice'>Получить совет</a>--><div class='descr'><img src='" +
                data[key]['image']+"' alt='' /><a>" +
                data[key]['title']+'</a><div class="indicator_block"><div class="money">' +
                formatCurrency(data[key]['amount']) + ' ' + easyFinance.models.accounts.getAccountCurrencyText(data[key]['account']) + '<br /><span> ' +
                data[key]['amount_done'] + '</span></div><div class="indicator"><div style="width:' +
                data[key]['percent_done'] + '%;"><span>' +
                data[key]['percent_done'] + '%</span></div></div><div class="date"><span>Целевая дата:' +
                data[key]['end'] + '</span>' +
                "</div><ul><li class='edit'>редактировать</li><li class='del'>удалить</li></ul></div></div></div>";
        }
        str += '<div class="add2"><span>Добавить финансовую цель</span></div>';
        $('.financobject_block').html(str);
    }
});
