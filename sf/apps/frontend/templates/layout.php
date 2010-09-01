<?php
#Max: давай резать шаблон на куски, чтобы удалять их из старого кода
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
    <head>
        <?php include_http_metas() ?>
        <?php include_metas() ?>
        <?php include_title() ?>
        <link rel="shortcut icon" href="/favicon.ico" />
        <?php include_stylesheets() ?>
        <?php include_javascripts() ?>
        <?php include_partial('global/res', array()) ?>
    </head>

    <body id="p_index">
        <noscript>
            <div style="display:block;position:fixed;top:0px;left:0px;width:100%;height:35px;background-color:#FF2222;z-index:9999;text-align:center;padding-top:10px;">
                <span Style="font-weight:bold;font-size:13px;">Этот сайт требует поддержку javascript</span>
            </div>
        </noscript>
        <!-- Jet. Ticket #443. Preload images to prevent wrong resizing -->
        <img src="/img/i/gauge70.gif" width="70" height="70" class="preload" />
        <img src="/img/i/gauge107.gif" width="107" height="107" class="preload" />
        <img src="/img/i/gauge157.gif" width="157" height="157" class="preload" />
        <div id="container1">
        <?php if (strpos($_SERVER['HTTP_HOST'], 'rambler') === false) : ?>
            <div id="menumain">
                <ul class="menu1">
                    <!-- Jet. ticket #273 -->
                    <li class="first"><a href="/review" id="review">Обзор</a></li>
                    <li><a href="/feedback" id="feed">Отзывы</a></li>
                    <li><a href="https://easyfinance-ru.livejournal.com/" id="blog" target="_blank">Блог</a></li>
                    <li><a href="/articles" id="articles">Статьи</a></li>
                    <li><a href="https://m.easyfinance.ru" id="pda">Мобильная версия</a></li>
                    <li><a href="/articles/12" id="help" style="font-weight:bold; color: yellow;">Помощь</a></li>
                </ul>
                <ul class="menu2">
                    <?php
                    /*
                    <!--- <li><a href="/info/">Личный кабинет</a></li> --->
                    <!--- <li><a href="/profile/">Настройки профиля</a></li> --->
                    */
                    ?>
                    <?php if ($sf_user->isAuthenticated()): ?>
                    <li><a href="/profile/"><?php echo $sf_user->getName(); ?></a></li>
                    <li><a id="show_logout" href="/logout/" title="Выход">ВЫХОД</a></li>
                    <?php else: ?>
                    <li id="show_login"><a id="linkLogin" href="https://<?php echo URL_ROOT ?>login/">ВХОД</a></li>
                    <?php endif; ?>
                </ul>
            </div>
<!-- верхнее меню -->
<!--шапка-->
            <div id="header">
                <a href="/info" class="logo">EasyFinance.ru</a>
                <div class="slogan">Система управления личными финансами</div>

                <!--реклама-->
                <?php include_partial('global/advertisement') ?>
                <!--/реклама-->
            </div>
        <?php endif; ?>

<!--середина-->
<div id="mainwrap"  style="z-index: 5">
    <!-- mainmenu -->
    <?php include_partial('global/common/mainmenu'); ?>


    <div class="ramka2">
        <div class="cct">
            <i class="tl"></i>
            <i class="tr"></i>
        </div>
        <div class="ccm">
            <div class="ccm-container">
            <!--меню4-->
            <!--/меню4-->
            <div class="mid general_cont">

                <!--левая колонка-->
                <?php include_partial('global/common/left.quick'); ?>

                <div class="block2">
                    <div class="l-indent">
                <?php include_partial('global/common') ?>

                <!--наполнение-->
                    <?php echo $sf_content ?>
                <!--/наполнение-->
                    </div>
                </div>

                <!--правая колонка-->
                <div class="block3 ramka3">
                    <div class="ct head">
                        <h2>Информер</h2>
                        <ul class="action">
                            <li class="over1" style="display: none;"><a title="настройки">настройки</a></li>
                            <li class="over2" style="display: none;"><a title="закрыть">закрыть</a></li>
                            <!--<li class="over3"><a href="#" title="свернуть">свернуть</a></li>-->
                        </ul>
                    </div>
                    <!--Финсостояние-->
                    <div class="calendar_block">
                        <h2>Фин. состояние</h2>
                        <div class="flash informerGauge" id="divInformer0">
                            <div id="divGaugeMain"></div>
                        </div>
                    </div>
                    <!--/Финсостояние-->
                    <!--Курсы валют-->
                    <dl id="divExchangeRates" class="info hidden">
                        <dt>Курсы валют</dt>
                        <dd><div class="line"><span class="valuta">RUB</span><span class="">1</span></div><div class="line"><span class="valuta">USD</span><span class="">30.1240</span></div></dd>
                    </dl>
                    <!--/Курсы валют-->
                    <!--калькулятор-->
                    <div class="calculator_block">
                        <h2>Калькулятор</h2>
                        <div class="calculatorRW">
                            <div class="input">
                                <input type="text" value="0" id="calculatorRW"/>
                            </div>
                            <div class="panel">
                                <table>
                                    <tr>
                                        <td class="printed"><div>1</div></td>
                                        <td class="printed"><div>2</div></td>
                                        <td class="printed"><div>3</div></td>
                                        <td class="printed"><div>/</div></td>
                                        <td class="special" event="clear"><div>C</div></td>
                                    </tr>
                                    <tr>
                                        <td class="printed"><div>4</div></td>
                                        <td class="printed"><div>5</div></td>
                                        <td class="printed"><div>6</div></td>
                                        <td class="printed"><div>*</div></td>
                                        <td class="special" event="back"><div>←</div></td>
                                    </tr>
                                    <tr>
                                        <td class="printed"><div>7</div></td>
                                        <td class="printed"><div>8</div></td>
                                        <td class="printed"><div>9</div></td>
                                        <td class="printed"><div>-</div></td>
                                        <td rowspan="2" class="special double" event="calc"><div> = </div></td>
                                    </tr>
                                    <tr>
                                        <td class="printed"><div>0</div></td>
                                        <td class="printed"><div>000</div></td>
                                        <td class="printed"><div>.</div></td>
                                        <td class="printed"><div>+</div></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!--/калькулятор-->
                    <!--календарь-->
                    <div class="calendar_block">
                        <h2>Календарь</h2>
                        <div class="calendar"></div>
                    </div>
                    <!--/календарь-->
                </div>
            <!--/правая колонка-->

            </div>
        </div>
    </div>
    <div class="ccb">
        <i class="bl"></i>
        <i class="br"></i>
    </div>
</div>
<!--/середина-->
<?php if (strpos($_SERVER['HTTP_HOST'], 'rambler') === false) : ?>
        <!--подвал-->
        <div id="footer" style="z-index: 10; height: 0; border: 0;">
            <div id="popupreport">
                <div class="inside">
                    <div class="w_d_head">&nbsp;</div>
                    <div class="w_d_body">
                        <table class="header">
                            <tr>
                                <td class="header">
                                    <h2>Оставить отзыв</h2>
                                 </td>

                                 <td class="link close">

                                </td>
                            </tr>
                        </table>
                        <div class="f_field">
                            <p><label for="ftheme">Тема</label></p>
                            <input type="text" value="" id="ftheme" />
                        </div>
                        <div class="f_field ffmes">
                            <label for="ffmes">Ваш отзыв</label>
                            <textarea cols="1" rows="1" id="ffmes"></textarea>
                        </div>
                        <center>
                            <button id="sendFeedback" style="background: none;border: 0;width: 160px;margin-left: -70px;">
                                <img src="/img/i/pix.gif" class="but" alt="Отправить отзыв"/>
                            </button>
                        </center>
                    </div>
                    <div class="w_d_foot">&nbsp;</div>
                </div>
            </div>
            <a  class="addmessage">оставить отзыв</a>

            <div id="popupHelp" class="hidden">
                <div class="title"></div><br>
                <div class="links"></div><br>
                <a
                    id = "playerHelp"
                    class = "video"
                    href="/flv/test.mp4"
                    style="display:block;width:640px;height:480px"
                ></a><br>
            </div>
            <a class="btnHelp">помощь</a>
        </div>

        <div id="footer" style="z-index: 1">
            <div id='dialog_rating'></div>
            <!--/popup оставить отзыв-->

            <ul class="footerTextsArea"><?php // {$seoHTML} ?></ul>

            <div style="position: relative; clear: both; border-top: 1px solid rgb(224, 227, 222);">
                <div class="certificate"><img src="/img/i/ssl_certificate.gif" /></div>

                <dfn>© 2009-2010 EasyFinance</dfn>
                <ul class="contacts">
                    <li><a href="/rules/">Правила использования</a></li>
                    <li><a href="/about/">О компании</a></li>
                    <li><a href="/security/">Безопасность</a></li>
                    <li class="tel tel1">Телефон:  +7 (495) 971-00-52</li>
                    <li class="tel tel2">Поддержка пользователей:  <a id="footerAddMessage" href="#">оставить заявку</a></li>
                    <li class="tel tel3">или отправить на почту <a href="mailto:helpdesk@easyfinance.ru">helpdesk@easyfinance.ru</a></li>
                    <li class="twitter">Twitter:  <a href="http://twitter.com/easyfinanceru">easyfinanceru</a></li>
                    <li class="partners">Работа с партнёрами: <br><a href="mailto:partners@easyfinance.ru">partners@easyfinance.ru</a></li>
                </ul>
                <a href="http://notamedia.ru/" target="_blank"><div class="creator" tooltip="linkalert-tip">дизайн сайта<br/>Нотамедиа 2009</div></a>
                <!--popup оставить отзыв-->
            </div>

            <div id='dialog_rating'></div>
            <!--/popup оставить отзыв-->

            <script type="text/javascript">
                $(document).ready(function() {
                    //toggle long footer text
                    $("#footer a.footerTextToggler").click(function() {
                        $(this).html($(this).html() == '&gt;&gt;' ? '&lt;&lt;' : '&gt;&gt;');
                        $(this).parent().next('div').slideToggle("fast");
                        return false;
                    });
                });
            </script>
        </div>
        <!--/подвал-->
<?php endif; ?>
        <script type="text/javascript">
            var _gaq = _gaq || [];
            _gaq.push(
                ['_setAccount', 'UA-10398211-2'],
                ['_setDomainName', '.easyfinance.ru'],
                ["_addOrganic", "mail.ru", "q"],
                ["_addOrganic","rambler.ru", "words"],
                ["_addOrganic","nigma.ru", "s"],
                ["_addOrganic","blogs.yandex.ru", "text"],
                ["_addOrganic","webalta.ru", "q"],
                ["_addOrganic","aport.ru", "r"],
                ["_addOrganic","akavita.by", "z"],
                ["_addOrganic","meta.ua", "q"],
                ["_addOrganic","bigmir.net", "q"],
                ["_addOrganic","tut.by", "query"],
                ["_addOrganic","all.by", "query"],
                ["_addOrganic","i.ua", "q"],
                ["_addOrganic","online.ua", "q"],
                ["_addOrganic","a.ua", "s"],
                ["_addOrganic","ukr.net", "search_query"],
                ["_addOrganic","search.com.ua", "q"],
                ["_addOrganic","search.ua", "query"],
                ["_addOrganic","poisk.ru", "text"],
                ["_addOrganic","km.ru", "sq"],
                ["_addOrganic","liveinternet.ru", "ask"],
                ["_addOrganic","gogo.ru", "q"],
                ["_addOrganic","gde.ru", "keywords"],
                ["_addOrganic","quintura.ru", "request"],
                ['_trackPageview']
            );
            (function() {
             var ga = document.createElement('script');
             ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
             ga.setAttribute('async', 'true');
             document.documentElement.firstChild.appendChild(ga);
            })();
        </script>
        <!--[if lte IE 6]><script src="/js/warning.js"></script><![endif]-->
    </body>
</html>
