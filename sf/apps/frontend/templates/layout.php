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
                    <?php include_partial('global/common/calcul') ?>
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
    </div>
    <div class="ccb">
        <i class="bl"></i>
        <i class="br"></i>
    </div>
</div>
<!--/середина-->
<?php include_partial('global/footer', array()) ?>
    </body>
</html>
