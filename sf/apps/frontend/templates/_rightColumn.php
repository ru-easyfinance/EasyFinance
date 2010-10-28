<!--правая колонка-->
    <div class="block3 ramka3 b-rightpanel">
        <div class="ct head">
            <h2>Информер</h2>
        </div>
        <!--Финсостояние-->
        <div class="calendar_block">
            <h2>Фин. состояние</h2>
            <div class="flash informerGauge efTooltip" id="divInformer0">
                <div id="divGaugeMain"></div>
            </div>
        </div>
        <!--/Финсостояние-->
        <!--Курсы валют-->
        <dl class="b-rates">
            <dt>Курсы валют</dt>
            <dd></dd>
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