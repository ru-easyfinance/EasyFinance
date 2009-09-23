$(document).ready(function(){
    $.post(
    '/info/get_data/',
    {},
    function(data){
        var titles,name,value,pointer,xml,chartSample_1;
        for (var i=0;i<5;i++)
        {
            titles = ['Деньги','Бюджет','Кредиты','Расходы','Фин.состояние'];
            if (isNaN(data[1][i]['color']))
                data[1][i]['color'] = 1;
            //alert(data[1][i]['title'])
            name = (!data[1][i]['title'])?titles[i]:data[1][i]['title'];
            value = parseInt(data[0][i]) ;
            pointer = data[1][i]['color']*33 - 20;
            xml = '<anychart><gauges><gauge><chart_settings><title>'+
                '<text>'+value+'</text>'+
		"</title></chart_settings><circular><axis radius='50' start_angle='85' sweep_angle='190' size='3'><labels enabled='false'></labels><scale_bar enabled='false'></scale_bar> <major_tickmark enabled='false'/><minor_tickmark enabled='false'/><color_ranges>"+
                "<color_range start='0' end='100' align='Inside' start_size='15' end_size='15' padding='6'>"+
                "<fill type='Gradient'><gradient><key color='Red'/><key color='Yellow'/><key color='Green'/></gradient></fill><border enabled='true' color='#FFFFFF' opacity='0.4'/></color_range></color_ranges></axis><frame enabled='false'></frame><pointers>"+
                "<pointer value='"+pointer+"'>"+
                "<needle_pointer_style thickness='7' point_thickness='5' point_radius='3'><fill color='Rgb(230,230,230)'/><border color='Black' opacity='0.7'/><effects enabled='false'></effects><cap enabled='false'></cap></needle_pointer_style><animation enabled='false'/></pointer></pointers></circular></gauge></gauges></anychart>";
            chartSample_1 = new AnyChart('/swf/anychart/gauge.swf');
                    chartSample_1.width = '109px';
                    chartSample_1.height = '120px';
                    chartSample_1.setData(xml);
                    chartSample_1.wMode="opaque";
                    chartSample_1.write('flash_' + i);
                    chartSample_1 = null;
                    $('div#flash_' + i).prepend('<div Style="text-align:center;font-weight:bold;">'+name+'</div>');
        }
        print_targets(0);
    },
    'json');
})

///////////////////////////////targets
function print_targets(count)
{
    $.post(
        '/targets/user_list/',
        {count : count},
        function(data){
            str='';
            for (key in data)
            {
                if(!data[key]['image'])
                    data[key]['image']='/img/i/fintarget.jpg';
                
                str = str + "<div class='object2' id='"+
                    data[key]['id'] + "'><!--<a class='advice'>Получить совет</a>--><div class='descr'><img src='" +
                    data[key]['image']+"' alt='' /><a>" +
                    data[key]['title']+'</a><div class="indicator_block"><div class="money">' +
                    formatCurrency(data[key]['amount']) + ' руб<br /><span> ' +
                    data[key]['amount_done'] + '</span></div><div class="indicator"><div style="width:' +
                    data[key]['percent_done'] + '%;"><span>' +
                    data[key]['percent_done'] + '</span></div></div><div class="date"><span>Целевая дата:' +
                    data[key]['end'] + '</span>' +
                    "</div><ul><li class='edit'>редактировать</li><li class='del'>удалить</li></ul></div></div></div>";
            }
            str = str + '<div class="add2"><span>Добавить финансовую цель</span></div>';
            $('.financobject_block').html(str);
        },
        'json'
    );
};
function add_target()
{
    document.location = '/targets/#add' ;//@todo :(( неполучается наладить((
};
function edit_target(target)
{
    document.location = '/targets/#edit/'+target ;//@todo :(( неполучается наладить((
};

function del_target(target)
{
    if (!confirm('Вы уверены что хотите удалить данную Финансовую цель?'))
        return false;
    $.post(
        '/targets/del/',
        {id: target.attr('id')},
        function(){
            target.remove();
        },
        'json'
     );
};