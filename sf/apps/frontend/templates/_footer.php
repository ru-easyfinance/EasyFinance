<?php if (strpos($_SERVER['HTTP_HOST'], 'rambler') === false) : ?>
<!--подвал-->
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

<div id="jsRelated" style="z-index: 10; height: 0; border: 0;">

    <?php include_partial('global/common/feedback', array())?>

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

    <div id="popupWizard" class="hidden">
        <dl class="dl-tabs">
            <dt class="selected" ondblclick="return {file: '1.mp4'}"><img src="/img/i/wizard/1.png" title="Используем личный кабинет"/></dt>
            <dd class="selected">Текст. Длинный или не очень.</dd>
            <dt ondblclick="return {file: '2.mp4'}"><img src="/img/i/wizard/2.png" title="Анализируем"/></dt>
            <dd><!-- А можно вообще без текста --></dd>
            <dt ondblclick="return {file: '3.mp4'}"><img src="/img/i/wizard/3.png" title="Учитываем"/></dt>
            <dd>Палимпсест, несмотря на внешние воздействия, точно вызывает поэтический
                холодный цинизм, хотя по данному примеру нельзя судить об авторских
                оценках. Даже в этом коротком фрагменте видно, что ритмический рисунок
                вызывает метафоричный верлибр, потому что сюжет и фабула различаются.</dd>
            <dt ondblclick="return {file: '4.mp4'}"><img src="/img/i/wizard/4.png" title="Планируем"/></dt>
            <dd>Текст. Длинный или не очень.</dd>
        </dl>
        <a
            id = "playerWizard"
            class = "video"
            href="/flv/test.mp4"
            style="display:block;width:640px;height:480px"
        ></a>
    </div>
</div>
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