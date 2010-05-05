<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
    <head>
        <?php include_http_metas() ?>
        <?php include_metas() ?>
        <?php include_title() ?>
        <link rel="shortcut icon" href="/favicon.ico" />
        <?php include_stylesheets() ?>
        <?php include_javascripts() ?>
        <script type="text/javascript">
            var res = <?php echo json_encode(array('errors' => array())); // тут был $res из шаблона ?>
        </script>
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
                        <!--- <li><a href="/info/">Личный кабинет</a></li> --->
                        <!--- <li><a href="/profile/">Настройки профиля</a></li> --->
                        <li><a href="/profile/"><?php echo 'Узернэйм'; //{$user_info.user_name} ?>&nbsp;</a></li>
                        <li><a id="show_logout" href="/logout/" title="Выход">ВЫХОД</a></li>
                </ul>
            </div>
<!-- верхнее меню -->
<!--шапка-->
            <div id="header">
                <a href="/info" class="logo">EasyFinance.ru</a>
                <div class="slogan">Система управления личными финансами</div>

                    <!--реклама-->
                    <dl id="advertisement">
                        <dt>реклама</dt>
                        <dd>
                            <div class="ramka1">
                                <div class="ct"><div></div></div>
                                <div class="inside" title="Здесь могла бы быть ваша реклама." style="text-align:center;">
                                    <a href="http://{$smarty.const.URL_ROOT_MAIN}registration/"><img src="/img/i/bannerBookRegister.gif" alt="Здесь могла бы быть ваша реклама." title=" Бесплатная книга &quot;Финансовая грамота&quot;" /></a>
                                </div>
                                <div class="cb"><div></div></div>
                            </div>
                        </dd>
                    </dl>
                    <!--/реклама-->
            </div>

        <?php echo $sf_content ?>

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
