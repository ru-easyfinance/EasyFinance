var id = 'step1';
$(document).ready(function() {
var bgpix = {step1: 'bg1',step2: 'bg2',step3:  'bg3',step4: 'bg4',step5: 'bg5'};
var text = {step1: "<h2></h2>Home-Money.ru это on-line система управления личными финансами с широкими аналитическими возможностями.",
            step2: "<h2></h2>Home-Money.ru позволит принимать правильные решения по управлению личными финансами.",
            step3: "<h2></h2>Возьмите под контроль свои финансы с помощью Home-Money.ru. Вносите информацию о доходах и расходах из любой точки мира, 24 часа в сутки.",
            step4: "<h2></h2>Получайте удовольствие и пользу вместе с Home-Money.ru – деньги работают, а вы избавляетесь от финансового стресса.",
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

////////////////////////////////////////////////////////////контролируй управляй...
	c_settings = {
	monitors : {top:20,
			left:100,
			text:'Деньги  тают в руках?! Пора разобраться с  тратами. Нас окружают миллионы полезных вещей, которые хотелось бы купить, и сотни, которые мы обязаны приобрести. Но для того, чтобы не стать шопоголиком и не упустить важное приобретение или вовремя оплатить счет необходимо заранее планировать доходы и расходы, и в этом Вам поможет наш сервис. '},
        control : {top:20,
			left:350,
			text:'Растущий капитал требует внимательного и четкого управления. В этом Вам поможет удобный и функциональный инструментарий на  нашего сайта, кроме того, по интересующим Вас вопросам вы сможете получить полезный совет нашего Эксперта. '},
        economy : {top:20,
			left:650,
			text:'Существует  много различных способов экономить, и первый из них - больше зарабатывать, например,  используя ПИФы или инвестиции в недвижимость, и наш сайт поможет Вам в выборе наиболее доступных и удобных методов инвестирования. Кроме того, на нашем сайте Вы сможете воспользоваться вторым, но не менее эффективным способом экономить - правильно учитывать и планировать расходы. '}
	
	}
        var w_dialog = 0;
        $('body').click(function(){
            if (w_dialog)
            {
                $('#dialog').hide();
                w_dialog = 0;
            }
           // return false;
        })
        
	$('.inside h2').click(function(){
		c = $(this).closest('div').attr('class');
		switch (c)
		{
			case 'monitors':
			$('#dialog').show().css('left',c_settings[c]['left']).css('top',c_settings[c]['top']).find('.text').html(c_settings[c]['text']);
				break;
			case 'control':
                            $('#dialog').show().css('left',c_settings[c]['left']).css('top',c_settings[c]['top']).find('.text').html(c_settings[c]['text']);
				break;
			case 'economy':
                            $('#dialog').show().css('left',c_settings[c]['left']).css('top',c_settings[c]['top']).find('.text').html(c_settings[c]['text']);
				break;
		}
                w_dialog = 1;
                return false;
	});
        $('#dialog').hide();
        $('.close').click(function(){
            $('#dialog').hide();
        });

});


