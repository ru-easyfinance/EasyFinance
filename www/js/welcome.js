

var id = 'step1';
$(document).ready(function() {



var bgpix = {step1: 'bg1',step2: 'bg2',step3:  'bg3',step4: 'bg4',step5: 'bg5'};
var text = {step1: "<h2></h2>EasyFinance.ru это on-line система управления личными финансами с широкими аналитическими возможностями.",
            step2: "<h2></h2>EasyFinance.ru позволит принимать правильные решения по управлению личными финансами.",
            step3: "<h2></h2>Возьмите под контроль свои финансы с помощью EasyFinance.ru. Вносите информацию о доходах и расходах из любой точки мира, 24 часа в сутки.",
            step4: "<h2></h2>Получайте удовольствие и пользу вместе с EasyFinance.ru – деньги работают, а вы избавляетесь от финансового стресса.",
            step5: "<h2></h2>Воспользуйтесь возможностями системы EasyFinance.ru для планирования семейного бюджета. Умело распределяйте ваши финансы."}
var head = {step1: 'txt1',step2: 'txt4',step3:  'txt3',step4: 'txt2',step5: 'txt5'}
var timeoutid;
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
        var id = $(this).attr('id');
        
        setTimeout(function(){
            clearInterval(timeoutid);
            timeoutid = setInterval(function(){
                toggle_main();
            },3000)
        },7000)

        update_text(id);    
    });

////////////////////////////////////////////////////////////контролируй управляй...
	var c_settings = {
	monitors : {top:'20px',
			left:'100px',
			text:'Деньги  тают в руках?!<br> Пора разобраться с  тратами. Нас окружают миллионы полезных вещей, которые хотелось бы купить, и сотни, которые мы обязаны приобрести. Но для того, чтобы не стать шопоголиком и не упустить важное приобретение или вовремя оплатить счет необходимо заранее планировать доходы и расходы, и в этом Вам поможет наш сервис. <br><br>'},
        control : {top:'20px',
			left:'350px',
			text:'Растущий капитал требует внимательного и четкого управления.<br> В этом Вам поможет удобный и функциональный инструментарий на  нашего сайта, кроме того, по интересующим Вас вопросам вы сможете получить полезный совет нашего Эксперта. <br><br>'},
        economy : {top:'20px',
			left:'650px',
			text:'Существует  много различных способов экономить, и первый из них - больше зарабатывать, например,  используя ПИФы или инвестиции в недвижимость, и наш сайт поможет Вам в выборе наиболее доступных и удобных методов инвестирования. Кроме того, на нашем сайте Вы сможете воспользоваться вторым, но не менее эффективным способом экономить - правильно учитывать и планировать расходы. <br><br>'}
	
	}
        //var w_dialog = 0;
        $('body').mousemove(function(){
            if (!$('#aboutproject div:hover').length)
            {
                $('#dialog').hide();
            }
           // return false;
        })  
        $('#dialog').css('position','absolute');
	$('#aboutproject .inside div').mouseover(function(){
		var c = $(this).attr('class');
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
                //w_dialog = 1;
                return false;
	});
        $('#dialog').hide();
        $('.close').click(function(){
            $('#dialog').hide();
        });
        if (timeoutid){
            clearInterval(timeoutid);
        }
        timeoutid = setInterval(function(){
            toggle_main();
        },3000);

        function toggle_main()
        {
            var next_step = {step1: 'step2',step2: 'step3',step3:  'step4',step4: 'step5',step5: 'step1'};
            var id = $('ul.steps li.act').attr('id');
            update_text(next_step[id]);
        }
if (window.location.hash == '#activate')
{
    $.jGrowl('Ваш аккаунт успешно активирован.', {theme: 'green'});
}

//функция выводит окошко предлагающее обновить explorer
function DropThisShirt(){
    //alert("Выкиньте свой браузер на помойку!!!");
    $('.w_dialog').show();
}
function detectIE6(){
  var browser = navigator.appName;
  var b_version = navigator.appVersion;
  var version = parseFloat(b_version);
  if ((browser == "Microsoft Internet Explorer") && (version <= 6)){
    return true;
  }else{
    return false;
  }
}

//var ua = navigator.userAgent.toLowerCase();
//if  (ua.indexOf("msie") != -1)
/*if (detectIE6())
    DropThisShirt();*/

    //alert("Выкиньте свой браузер на помойку!!!");

$('.link.close').click(function(){
            $('.w_dialog').hide();
        });
});


