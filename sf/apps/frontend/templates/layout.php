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
        <!--[if gte IE 7]>
            <link rel="stylesheet" type="text/css" href="/css/ie7.css?r=<?php echo REVISION ?>"/>
        <![endif]-->
        <?php include_javascripts() ?>
        <?php include_partial('global/res', array()) ?>
    </head>

    <body id="p_index" <?php if (!strpos($_SERVER['HTTP_HOST'], 'rambler') === false) { echo 'class="body-rambler"'; }?>>
        <?php include_partial('common/noscript') ?>
        <!-- Jet. Ticket #443. Preload images to prevent wrong resizing -->
        <img alt="" src="/img/i/gauge70.gif" width="70" height="70" class="preload" />
        <img alt="" src="/img/i/gauge107.gif" width="107" height="107" class="preload" />
        <img alt="" src="/img/i/gauge157.gif" width="157" height="157" class="preload" />
        <div id="container1">
        <?php if (strpos($_SERVER['HTTP_HOST'], 'rambler') === false) : ?>
            <?php if ($_SERVER['HTTP_HOST'] === '/'): ?>
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
            <?php endif?>
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
    <?php if ($sf_user->isAuthenticated()) : ?>
    <?php include_partial('global/common/mainmenu'); ?>
    <?php endif; ?>
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

                    <?php include_partial('global/rightColumn', array()) ?>

                </div>
            </div>
        </div>
        <div class="ccb">
            <i class="bl"></i>
            <i class="br"></i>
        </div>
    </div>
</div>
<!--/середина-->
<?php include_partial('global/footer', array()) ?>
    </body>
</html>
