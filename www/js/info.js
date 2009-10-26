/** $Id: main.js 627 2009-09-30 07:04:37Z rewle $ */
$(document).ready( function(){

// # BIND
$('.add2 span').live('click', function(){
    document.location = '/targets/#add';
});
$('li.del').live('click', function(){
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
$('li.edit').live('click', function(){
    document.location = '/targets/#edit/'+$(this).closest('div.object2').attr('id');
});
$.post(
    '/info/get_data/',
    {},
    function(data){
        var titles,name,value,pointer,xml,chartSample_1;
        for (var i=0;i<5;i++)
        {
            titles = ['Фин.состояние','Деньги','Бюджет','Кредиты','Расходы'];
            if (isNaN(data[1][i]['color']))
                data[1][i]['color'] = 1;
            //alert(data[1][i]['title'])
            name = (!data[1][i]['title'])?titles[i]:data[1][i]['title'];
            value = parseInt(data[0][i]) ;
            pointer = data[1][i]['color']*33 - 20;
            /*
            xml = '<anychart><gauges><gauge><chart_settings><title>'+
                '<text>'+value+'</text>'+
                "</title></chart_settings><circular><axis radius='50' start_angle='85' sweep_angle='190' size='3'><labels enabled='false'></labels><scale_bar enabled='false'></scale_bar> <major_tickmark enabled='false'/><minor_tickmark enabled='false'/><color_ranges>"+
                "<color_range start='0' end='100' align='Inside' start_size='15' end_size='15' padding='6'>"+
                "<fill type='Gradient'><gradient><key color='Red'/><key color='Yellow'/><key color='Green'/></gradient></fill><border enabled='true' color='#FFFFFF' opacity='0.4'/></color_range></color_ranges></axis><frame enabled='false'></frame><pointers>"+
                "<pointer value='"+pointer+"'>"+
                "<needle_pointer_style thickness='7' point_thickness='5' point_radius='3'><fill color='Rgb(230,230,230)'/><border color='Black' opacity='0.7'/><effects enabled='false'></effects><cap enabled='false'></cap></needle_pointer_style><animation enabled='false'/></pointer></pointers></circular></gauge></gauges></anychart>";
            chartSample_1 = new AnyChart('/swf/anychart/gauge.swf');
                    chartSample_1.width = !i==0?'109px':'120px';
                    chartSample_1.height = !i==0?'120px':'139px';
                    chartSample_1.setData(xml);
                    chartSample_1.wMode="opaque";
                    chartSample_1.write('flash_' + i);
                    chartSample_1 = null;
                    $('div#flash_' + i).prepend('<div Style="text-align:center;font-weight:bold;">'+name+'</div>');
            */

            // init gauges
            var title = (!data[1][i]['title'])?titles[i]:data[1][i]['title'];
            $('#flashTitle_'+i).text(title);
            var size = (i==0) ? "107" : "70";
            var flashvars = {title: "", value: data[0][i], bgimage: "/img/i/gauge" + size + ".gif"};
            var params = {wmode: "transparent"};
            var attributes = {id: "gauge"+i};
            swfobject.embedSWF("/swf/efGauge.swf", "flash_"+i, size, size, "9.0.0", false, flashvars, params, attributes);
        }
        print_targets(0);
    },
    'json');

    /**
     * Выводит список финансовых целей пользователя
     */
    function print_targets(count) {
        var data = res['user_targets'];
        var str ='';
        for (var key in data) {
            if(!data[key]['image']) {
                data[key]['image']='/img/i/fintarget.jpg';

            }
            str += "<div class='object2' id='"
                + key + "'><!--<a class='advice'>Получить совет</a>--><div class='descr'><img src='" +
                data[key]['image']+"' alt='' /><a>" +
                data[key]['title']+'</a><div class="indicator_block"><div class="money">' +
                formatCurrency(data[key]['money']) + ' руб<br /><span> ' +
                data[key]['amount_done'] + '</span></div><div class="indicator"><div style="width:' +
                data[key]['percent_done'] + '%;"><span>' +
                data[key]['percent_done'] + '%</span></div></div><div class="date"><span>Целевая дата:' +
                data[key]['date_end'] + '</span>' +
                "</div><ul><li class='edit'>редактировать</li><li class='del'>удалить</li></ul></div></div></div>";
        }
        str += '<div class="add2"><span>Добавить финансовую цель</span></div>';
        $('.financobject_block').html(str);
    }
})