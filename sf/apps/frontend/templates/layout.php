<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
    <head>
        <?php include_http_metas() ?>
        <?php include_metas() ?>
        <?php include_title() ?>
        <meta name="verify-reformal" content="0df0cf61e83e18bd7bfe09a5" />
        <link rel="shortcut icon" href="/favicon.ico" />
        <?php include_stylesheets() ?>
        <!--[if IE 7]>
            <link rel="stylesheet" type="text/css" href="/css/ie7.css?r=<?php echo REVISION ?>"/>
        <![endif]-->
        <!--[if IE 8]>
            <link rel="stylesheet" type="text/css" href="/css/ie8.css?r=<?php echo REVISION ?>"/>
        <![endif]-->
        <?php include_javascripts() ?>
        <?php include_partial('global/res', array()) ?>
    </head>

    <body id="p_index" <?php if (!strpos($_SERVER['HTTP_HOST'], 'rambler') === false) { echo 'class="body-rambler"'; }?>>
        <?php include_partial('global/common/noscript') ?>
        <!-- Jet. Ticket #443. Preload images to prevent wrong resizing -->
        <img alt="" src="/img/i/gauge70.gif" width="70" height="70" class="preload" />
        <img alt="" src="/img/i/gauge107.gif" width="107" height="107" class="preload" />
        <img alt="" src="/img/i/gauge157.gif" width="157" height="157" class="preload" />
        <div id="container1">
        <?php if (strpos($_SERVER['HTTP_HOST'], 'rambler') === false) : ?>
            <?php if (!$sf_user->isAuthenticated()) : ?>
                <div id="menumain">
                    <?php include_partial('global/common/uppermenu', array()) ?>

                    <?php if ($sf_user->isAuthenticated()) : ?>
                        <ul class="menu2">
                            <li><a href="/my/profile/"><?php echo $sf_user->getName(); ?></a><a id="show_logout" href="/logout/" title="Выход">ВЫХОД</a></li>
                        </ul>
                    <?php else: ?>
                        <?php include_partial('global/common/loginform', array()) ?>
                    <?php endif; ?>

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
                <div class="mid general_cont">
                    <?php if (isset($mainBlockClear)) : ?>
                        <?php include '_mainBlockClear.php'; ?>
                    <?php else : ?>
                        <?php include '_mainBlock.php'; ?>
                    <?php endif; ?>
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
