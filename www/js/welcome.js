var id = 'step1';
$(document).ready(function() {
var bgpix = {step1: 'bg1',step2: 'bg2',step3:  'bg3',step4: 'bg4',step5: 'bg5'};
var text = {step1: "<h2></h2>Home-Money.ru это on-line система управления личными финансами с широкими аналитическими возможностями.",
            step2: "<h2></h2>Home-Money.ru позволит принимать правильные решения по управлению личными финансами.",
            step3: "<h2></h2>Возьмите под контроль свои финансы с помощью Home-Money.ru. Вносите информацию о доходах и расходах из любой точки мира, 24 часа в сутки.",
            step4: "<h2></h2>Получайте удовольствие и пользу – деньги работают, а вы избавляетесь от финансового стресса.",
            step5: "<h2></h2>Воспользуйтесь возможностями системы Home-Money.ru для планирования семейного бюджета. Умело распределяйте ваши финансы."}
var head = {step1: 'txt1',step2: 'txt4',step3:  'txt3',step4: 'txt2',step5: 'txt5'}

function update_text(id)
{
    $('ul.steps li').attr('class','');
    $('li#'+id).attr('class','act');
    $('div.descr').html('<div class="'+head[id]+'" style="display: block;">'+text[id]+'</div>');
    $('#bgpics').attr('class',bgpix[id]);
}


update_text(id);
 $('ul.steps li').click(
    function(){
        id = $(this).attr('id');
        update_text(id);    
    });


});


