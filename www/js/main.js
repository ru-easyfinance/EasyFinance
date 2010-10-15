function setHeightClass(){
    var cheight = 0;
    if ('CSS1Compat' && !window.opera){
        cheight = document.documentElement.clientHeight;
    }else{
        cheight = document.body.clientHeight;
    }

    var cls = '';
    if (cheight > 600){
        cls = 'heightLarge';
    }else if(cheight > 350){
        cls = 'heightNormal';
    }else{
        cls = 'heightSmall';
    }

    $('div#mainwrap').addClass(cls);
}


$.jGrowl.defaults.live = 1500;
$.jGrowl.defaults.position = "center";
$.jGrowl.defaults.closerTemplate = "<div>[ закрыть все сообщения ]</div>";

function get_array_key($arr, $val){
    var $ret = -1;
    for (var key in $arr) {
        if ($val == $arr[key]) {
            $ret = key;
            break;
        }
    }
    return $ret;
}

var isChrome = /chrome/.test( navigator.userAgent.toLowerCase() );

var aPath = ['//', '/about/', '/accounts/', '/admin/', '/blog/', '/budget/', '/calendar/', '/category/', '/experts/', '/feedback/', '/forum/', '/info/', '/login/', '/logout/', '/mail/', '/operation/', '/periodic/', '/my/profile/', '/registration/', '/report/', '/review/', '/rules/', '/security/', '/start/', '/tags/', '/targets/', '/welcome/', '/template/', '/my/']; //данный контроллер можно использовать как системный))

href = location.pathname;
href = href.toLowerCase() + '/';
var b = 0;
var nhref = href.match(/\/[a-z]{0,}\//);
var pathName = nhref[0];
var Current_module = get_array_key(aPath, nhref);
var Connected_functional = {
    operation: [2, 5, 6, 7, 8, 11, 15, 16, 19, 25],
    menu: [2, 5, 6, 7, 8, 11, 15, 16, 17, 19, 25, 28]
};
var isIframe = false;
var pathtoid = {
    '/accounts/': 'm2',
    '/budget/': 'm3',
    '/calendar/': 'm5',
    '/category/': 'm2',
    '/experts/': 'm6',
    '/info/': 'm1',
    '/mail/': 'm0',
    '/operation/': 'm2',
    '/periodic/': 'm5',
    '/my/profile/': 'm1',
    '/targets/': 'm3',
    '/report/': 'm4'
};
var page_mid = pathtoid[pathName];
/**
 * @deprecated
 */
function FloatFormat(obj, in_string){
    //'.'
    var l = in_string.length;
    var rgx = /[0-9]/;
    var c = 0;
    var p = 1;
    var newstr = '';
    var i = 0;
    for (var a = 1; a <= l; a++) {
        i = l - a + 1;
        if (rgx.test(in_string.substr(i, 1))) {
            if (c == 3) {
                newstr = ' ' + newstr;
                c = 0;
            }
            newstr = in_string.substr(i, 1) + newstr;
            c++;
        }
        if (in_string.substr(i, 1) == '.' || in_string.substr(i, 1) == ',') {
            if (p) {
                newstr = newstr.substr(0, 2);
                newstr = '.' + newstr;
            }

            c = 0;
            p = 0;
        }
    }

    //ловим положение каретки,
    //ловим символы перед ней,
    $(obj).val(newstr);
    //если они изменились сдвигаем каретку на один вправо
}

function MakeOperation(){
    $.get('/targets/get_closed_list', {}, function(data){
        if (data) {
            for (var v in data) {
                if (confirm('Деньги на финансовую цель ' + data[v]['title'] + ' накоплены. Осуществить перевод денег ?')) {
                    var o = $('.object[name=' + data[v]['title'] + ']');

                    $.post('/targets/close_op', {
                        opid: data[v]['id'],
                        targetcat: data[v]['category_id'],
                        amount: data[v]['amount_done'],
                        account: data[v]['target_account_id']
                    }, function(data){
                        if (typeof data.accepted == 'undefined')
                            data.accepted = 0;
                        easyFinance.widgets.operationEdit.fillFormCalendar(data, true, false);
                        o.remove();
                        $.jGrowl("Финансовая цель закрыта", {
                            theme: 'green'
                        });
                    }, 'json');
                }
            }
        }
    }, 'json');
}

//запланировано
function isLogged(){
    if (res && res.user)
        return true;
    else
        return false;
}

$(document).ready(function(){
    easyFinance.notifier = new $.globalNotifier();

    setHeightClass();
    $('ul.menu2 a').click(function(){
        $.cookie('events_hide', 0, {
            path: '/'
        });
    });
    if (location.hostname.indexOf("iframe.") != -1)
        isIframe = true;
    // # тикет 625
    // инициализируем виджет видео-гида
    if (!isIframe) {
        easyFinance.widgets.help.init('#popupHelp', true);
        //easyFinance.widgets.wizard.init();
        // по умолчанию устанавливаем видео,
        // которое соответствует содержанию страницы
        var tabVideo = {
            "m0": "newOperation",
            "m1": "newOperation",
            "m2": "newAccount",
            "m3": "newBudget",
            "m4": "newTarget",
            "m5": "newOperation",
            "m6": "newOperation"
        };
        $('#linkMainMenuVideoGuide').click(function(){
            $('#popupHelp').dialog('open');
            if (page_mid) {
                easyFinance.widgets.help.showVideo(tabVideo[page_mid]);
            } else {
                easyFinance.widgets.help.showVideo("newAccount");
            }
        });
//        $('#linkMainMenuStartWizard').click(function() {
//            easyFinance.widgets.wizard.show();
//        })
    }

    // #1583. задаем переменные для работы с боковой панелью
    var rightbar = $('.mid .block3'),
        rightbarW = rightbar.width(),
        centralfield = $('.mid .block2 .l-indent'),
        mRight = parseInt(centralfield.css('margin-right')),
        zeroRight = mRight - rightbarW,
        speed = 400,
        debounce = null,
        forceshow = false,
        widthState;

    // #1583. кнопка для показа/скрытия правого сайдбара
    $('.b-sidebar-btn').click(function() {
        $(this).toggleClass('selected');
        forceshow = !forceshow;
        onClick();
        return false;
    });

    // #1583. определяем размер экрана (только не для главной страницы!)
    if (pathName != '//') {
        function rightbarAnimate(value, animate) {
            if (animate) rightbar.stop();
            rightbar.animate({
                left: value
            }, ((animate) ? speed : 0));
        }

        function contentAnimate(value) {
            centralfield.animate({
                marginRight: value
            }, 0, function() {
                if(widthState) {
                    $('.ct.head h2').show();
                    $('.b-sidebar-btn').hide();
                } else {
                    $('.b-sidebar-btn').removeClass('selected').show();
                }
            });
        }

        function onClick(animation) {
            if(forceshow) {
                $('.ct.head h2').hide();
                rightbarAnimate(0, ((animation == false) ? false : true));
            } else {
                rightbarAnimate(rightbarW + 30, ((animation == false) ? false : true));
            }
        }

        $(window).resize(function() {
            if(debounce) clearTimeout(debounce);
            debounce = setTimeout(function() {
                widthState = $('#mainwrap').width() > (1000 + rightbarW);
                if(widthState) {
                    if(forceshow) {
                        forceshow = false;
                    }
                    rightbarAnimate(0);
                    contentAnimate(mRight);
                } else {
                    onClick(false);
                    contentAnimate(zeroRight);
                }
            }, 200);
        }).trigger('resize');
    }

    //#538
    if (!$.cookie('referer_url') &&
    !res.accounts &&
    !/(http(s)?:\/\/[A-z0-9\.]*)?easyfinance\.ru.*/i.test(document.referrer)) {
        $.cookie('referer_url', document.referrer, {
            expire: 100,
            path: '/',
            domain: false,
            secure: false
        });
    }
    $.datepicker.setDefaults($.extend({
        dateFormat: 'dd.mm.yy'
    }, $.datepicker.regional['ru']));
    // *** Функции ***
    if (res['errors'] !== null && res['errors'].length > 0) {
        for (v in res['errors']) {
            $.jGrowl(res['errors'][v], {
                theme: 'red'
            });
        }
    }
    if (res.result && res.result.text)
        $.jGrowl(res.result.text, {
            theme: 'green'
        });
    if (res.error && res.error.text)
        $.jGrowl(res.error.text, {
            theme: 'red'
        });
    //открытие сообщений
    function inarray(key, arr){
        var k;
        for (k in arr) {
            if (key == arr[k]) {
                return true;
            }
        }
        return false;
    }
    // LOAD MODELS
    // modified by Jet 29.10.2009, ticket 337.
    if (isLogged()) {
        easyFinance.models.currency.load(res.currency);
        easyFinance.models.accounts.load(easyFinance.models.currency, res.accounts, function(model){
            easyFinance.widgets.accountsPanel.init('.accounts', model);
        });
        easyFinance.models.category.load(res.category);
    }

    // Если доступна левая панель (пользователь авторизован)
    if ($("#leftPanel").length) {
        // инициализируем виджет добавления, редактирования и планирования операций
        easyFinance.widgets.operationEdit.init('.op_addoperation', easyFinance.models.accounts, easyFinance.models.category);
        easyFinance.widgets.calendarLeft.init("#calendarLeft", easyFinance.models.accounts);
        easyFinance.widgets.accountEdit.init('#widgetAccountEdit', easyFinance.models.accounts, easyFinance.models.currency);
    }
    if (inarray(Current_module, Connected_functional.menu)) {
        $('.navigation a[href*=' + pathName + ']').wrapInner('<b></b>');
        $('.listing').hide();
        $('.navigation li ul').hide();
        $('.navigation li.act ul').show();
        $('.navigation  li span').click(function(){
            $('.navigation  li span').closest('li').removeClass('act');
            $(this).closest('li').addClass('act');
            $('.navigation  li ul').hide();
            $('.navigation li.act ul').show();
        });
        /**
         * Загружаем метки для левой панели
         */
        function reloadTags(){
            $.get('/tags/getTags?responseMode=json', {}, function(data){
                res.tags = $.extend({}, data.tags);
                loadLPTags();
            }, 'json');
        }
        function loadLPTags(){
            var data = res['tags'];
//            var str = '<h2 class="b-leftpanel-title addtaglink"><span>Добавить метку</span><i></i></h2><ul>';
            var str = '<div class="title"><h2><a href="#" class="addtaglink">Добавить метку</a></h2><a title="Добавить" class="add">Добавить</a></div><ul>';
            for (var key in data) {
                str = str + '<li><a>' + data[key] + '</a></li>';
            }
            str += '</ul>';
            $('.tags_list').empty().append(str);
        }
        loadLPTags();
        $(document).bind(
            'operationEdited operationAdded operationsChainAdded operationsChainEdited operationDateEdited',
            function(data){
                reloadTags();
            }
        );
        $('.tags_list li a').live('click', function(){
            $('.edit_tag').dialog('open');
            $('.edit_tag input').val($(this).text());
            $('.edit_tag').dialog({
                width: 260,
                minHeight: 50,
                buttons: {
                    'Сохранить': function(){
                        $.post('/tags/edit/?responseMode=json', {
                            tag: $('.edit_tag').find('#tag').val(),
                            old_tag: $('.edit_tag #old_tag').val()
                        }, function(data){
                            if (data) {
                                $.jGrowl('Метка успешно сохранена', {
                                    theme: 'green'
                                });
                                res.tags = null;
                                var tags = { tags: data.tags };
                                res = $.extend(res, tags);
                                loadLPTags();
                                $('.edit_tag').dialog('close');
                                $('.edit_tag #tag,.edit_tag #old_tag').val('');
                            } else {
                                $.jGrowl('Ошибка при сохранении метки', {
                                    theme: 'red'
                                });
                            }
                        }, 'json');
                    },
                    'Удалить': function(){
                        if (confirm('Метка "' + $('.edit_tag #old_tag').val() + '" будет удалён. Удалить?')) {
                            var tag = $('.edit_tag #old_tag').val();
                            $.post('/tags/del/?responseMode=json', {
                                tag: tag
                            }, function(data){
                                if (!data) {
                                    data = {};
                                }
                                $.jGrowl('Метка удалена', {
                                    theme: 'green'
                                });
                                $('.edit_tag #tag,.edit_tag #old_tag').val(0);
                                delete res.tags;
                                var tags = { tags: data.tags };
                                res = $.extend(res, tags);
                                loadLPTags();
                                $('.edit_tag').dialog('close');
                            }, 'json');
                        }
                    }
                }
            });
        });

        $('.tags_list .add,.tags_list .addtaglink').live('click', function(){
            var add = $('.add_tag');
            $(add).show().dialog('open').dialog({
                width: 260,
                minHeight: 50,
                buttons: {
                    'Сохранить': function(){
                        if ($('input#tag', add).val()) {
                            $.post('/tags/add/?responseMode=json', {
                                tag: $('.add_tag input').val()
                            }, function(data){
                                if (data) {
                                    res['tags'].push($('.add_tag input').val());
                                    loadLPTags();
                                    $('.add_tag').dialog('close');
                                    $('.add_tag input').val('');
                                    $.jGrowl('Новая метка успешно добавлена', {
                                        theme: 'green'
                                    });
                                } else {
                                    $.jGrowl('Ошибка при добавлении метки', {
                                        theme: 'red'
                                    });
                                }
                                $('.add_tag').dialog('close');
                            }, 'json');
                            $('.add_tag').dialog('close');
                        }
                    }
                }
            });
        });

        data = res['user_targets'];
        var s = '<div class="title"><h2><a href="/targets/#add" class="addtargetlink">Добавить цель</a></h2><a href="/targets/#add" title="Добавить" class="add">Добавить</a></div><ul>';
        for (var v in data) {
            if (data[v]['done'] == 0) {
                s += '<li ><a href="/targets/#edit/' + v + '">' + data[v]['title'] + '</a><b>' +
                formatCurrency(data[v]['amount_done']) +
                ' ' +
                easyFinance.models.accounts.getAccountCurrencyText(data[v]['account']) +
                ' </b> <span>(' +
                data[v]['percent_done'] +
                '%)</span><span class="date">' +
                data[v]['end'] +
                '</span></li>';
            }
        }
        s = s + '</ul>';
        data = res['popup_targets'];
        s = s + '<h2>5 самых популярных</h2><ul class="popular">';
        var popular = 0;
        for (v in data) {
            popular++;
            if (popular <= 5)
                s += '<li><a href="#">' +
                data[v]['title'] +
                '</a></li>';
        }
        s = s + '<ul>';
        $('.financobject').append(s);
        $('.financobject div.title a').live('click', function(){
            $("div.financobject_block .add span").click();
        });

        $('.financobject ul a').live('click', function(){
            var id = $(this).attr('href');
            window.location = id;//WTF??
            var str = id.substr(15);
            var f = $('.object[tid="' + str + '"]');
            $('input,textarea', '#tpopup').val('');
            $('#key').val(f.attr('id'));
            $('#type').val(f.attr('type'));
            $('#title').val(f.attr('title'));
            $('#name').val(f.attr('name'));
            $('#targets_category').val(f.attr('category'));
            $('#tg_amount').val(f.attr('amount'));
            $('#start').val(f.attr('start'));
            $('#end').val(f.attr('end'));
            $('#photo').val(f.attr('photo'));
            $('#url').val(f.attr('url'));
            $('#comment').val(f.attr('comment'));
            $('#account').val(f.attr('account'));
            $('#visible').val(f.attr('visible'));
            $('#tpopup').dialog('open');
        });

        //////////////////////////////////////////////////////////////////////
        // правая панель
        // главный тахометр - финансовое состояние
        var flashvars = {
            title: 'Финансовое состояние',
            value: res.informers[0].value,
            bgimage: ""
        };
        var params = {
            wmode: "transparent"
        };
        var attributes = {
            id: "gaugeMain"
        };
        swfobject.embedSWF("/swf/efGauge.swf", "divGaugeMain", "107", "107", "9.0.0", false, flashvars, params, attributes);
        //курсы валют в правой панели
        easyFinance.widgets.currencyRight.load(easyFinance.models.currency);

        //mainmenu
        $('div#mainwrap #' + page_mid).addClass('cur act').children('a').addClass('cur');
        $('.menu3 ul li ul li a[href$=' + pathName + ']').parent().addClass('selected');
    }
    // Кнопка сворачивания / разворачивания
    $('li.over3, li.uparrow').addClass('uparrow').toggleClass('uparrow').click(function(){
        /*
         //@TODO Сохранять значение в куках и потом читать их из куков
         */
        $(this).closest('div.ramka3').find('div.inside').toggle();
        $(this).toggleClass('uparrow').toggleClass('over3');
        var title = $(this).find('a').attr('title') == 'свернуть' ? 'развернуть' : 'свернуть';
        $(this).find('a').attr('title', title);
        //$(this).closest('div.ramka3').className("over2");
    }).find('a').removeAttr('href');
    // Кнопка закрыть
    $('li.over2').remove();
    $('li.over1').remove();

    $(document).keypress(function(e) {
        if(e.keyCode == 27) {
            $(document).trigger('event.esc');
        }
        if(e.keyCode == 9) {
            $(document).trigger('event.tab');
        }
    });
});

