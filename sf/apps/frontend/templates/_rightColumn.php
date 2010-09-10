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