/**
 * @desc Welcome
 * @author Alexandr [Rewle] Ilichov
 */
$(document).ready(function() {
    /**
     * Переменные необходимые для текстовых преобразований
     */
    var bgpix = {step1: 'bg1',step2: 'bg2',step3:  'bg3',step4: 'bg4',step5: 'bg5'};
    var text = {step1: "<h2></h2>EasyFinance.ru это on-line система управления личными финансами с широкими аналитическими возможностями.",
                step2: "<h2></h2>EasyFinance.ru позволит принимать правильные решения по управлению личными финансами.",
                step3: "<h2></h2>Возьмите под контроль свои финансы с помощью EasyFinance.ru. Вносите информацию о доходах и расходах из любой точки мира, 24 часа в сутки.",
                step4: "<h2></h2>Получайте удовольствие и пользу вместе с EasyFinance.ru – деньги работают, а вы избавляетесь от финансового стресса.",
                step5: "<h2></h2>Воспользуйтесь возможностями системы EasyFinance.ru для планирования семейного бюджета. Умело распределяйте ваши финансы."}
    var head = {step1: 'txt1',step2: 'txt4',step3:  'txt3',step4: 'txt2',step5: 'txt5'}
    var timeoutid;
    /**
     * Производит обновление текста в центральной
     * @return void
     */
    function updateText(id)
    {
        $('ul.steps li').removeClass('act');
        $('ul.steps li#'+id).addClass('act');
        $('div.descr').html('<div class="'+head[id]+'" style="display: block;">'+text[id]+'</div>');
        $('#bgpics').attr('class',bgpix[id]);
    }
    /**
     * Переключает картинку на следующий шаг
     * @return void
     */
    function toggleMain()
    {
        var nextStep = {step1: 'step2',step2: 'step3',step3:  'step4',step4: 'step5',step5: 'step1'};
        var id = $('ul.steps li.act').attr('id');
        updateText(nextStep[id]);
    }
    /**
     * Нажатие на кнопку "шаг"
     */
    $('ul.steps li').click(function()
    {
        var id = $(this).attr('id');
        updateText(id);
        setTimeout(function()
        {
            clearInterval(timeoutid);
            timeoutid = setInterval(function()
            {
                toggleMain();
            },
            3000)
        },
        7000)
    });
    /**
     * Задача регулярных действий
     */
    timeoutid = setInterval(function()
    {
        toggleMain();
    },
    3000);
////////////////////////////////////////////////////////////контролируй управляй...
    /**
     * настройки для всплывающего окошка
     */
    var c_settings = {
        monitors : {
            left:'100px',
            text:'Деньги  тают в руках?!<br> Пора разобраться с  тратами. Нас окружают миллионы полезных вещей, которые хотелось бы купить, и сотни, которые мы обязаны приобрести. Но для того, чтобы не стать шопоголиком и не упустить важное приобретение или вовремя оплатить счет необходимо заранее планировать доходы и расходы, и в этом Вам поможет наш сервис. <br><br>'},
        control : {
            left:'350px',
            text:'Растущий капитал требует внимательного и четкого управления.<br> В этом Вам поможет удобный и функциональный инструментарий на  нашего сайта, кроме того, по интересующим Вас вопросам вы сможете получить полезный совет нашего Эксперта. <br><br>'},
        economy : {
            left:'650px',
            text:'Существует  много различных способов экономить, и первый из них - больше зарабатывать, например,  используя ПИФы или инвестиции в недвижимость, и наш сайт поможет Вам в выборе наиболее доступных и удобных методов инвестирования. Кроме того, на нашем сайте Вы сможете воспользоваться вторым, но не менее эффективным способом экономить - правильно учитывать и планировать расходы. <br><br>'}
    }
    /**
     * Позиционирование по умолчанию
     * @deprecated перенести в css
     */
    $('#dialog').css({top:'320px',position:'absolute'}).hide()
    /**
     * скрытие подсказки при необходимости
     */
    $('body').mousemove(function()
    {
        if (!$('#aboutproject div:hover,#dialog:hover').length)
        {
            $('#dialog').hide();
        }
    })
    /**
     * открытие и позиционирование подсказки
     */
    $('#aboutproject .inside div').mouseover(function()
    {
        var c = $(this).attr('class');
        switch (c)
        {
            case 'monitors':
            case 'control':
            case 'economy':
                $('#dialog').show().css('left',c_settings[c]['left']).find('.text').html(c_settings[c]['text']);
                break;
        }
        return false;
    });
    /**
     * Закрытие подсказки
     */
    $('.link.close').click(function(){
        $('#dialog').hide();
    });
});