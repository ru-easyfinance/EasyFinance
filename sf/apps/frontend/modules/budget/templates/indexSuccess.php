<div class="ramka3">
    <div class="ct">
        <div class="head">
            <h2>Бюджет</h2>
            <ul class="action">
                <li class="over3"><a href="#" title="свернуть">свернуть</a></li>
            </ul>
        </div>
    </div>
<center>
    <div id="budget" class="inside budget">
        <div class="addbudget"><a href="#" id="btnBudgetWizard" class="efTooltip" title="Создать новый или отредактировать уже существующий бюджет">Мастер создания и редактирования бюджета</a></div>
        <div class="budget datebar">
            <ul id="budgetMonthPicker" class="month_cal">
                <li class="y_prev" title="Переход на предыдущий год"><a></a></li>
                <li class="m_prev" title="Переход на предыдущий месяц"><a></a></li>
                <li class="cur" title="Переход на текущий месяц"><a></a></li>
                <li class="m_next" title="Переход на следующий месяц"><a></a></li>
                <li class="y_next" title="Переход на следующий год"><a></a></li>
            </ul>
        </div>
        <div class="budget info">
            <!-- здесь скрипт выведет информацию -->
        </div>
        <div class="budget list">
            <div class="list head">
                <table>
                    <tr>
                        <td class="w1" class="efTooltip" title="Категория, по которой будет учитываться бюджет"></td>
                        <td class="w2" class="efTooltip" title="Сумма, выделенная в бюджете на эту категорию"></td>
                        <td class="w3" class="efTooltip" title="Риска в линии времени отображает позицию текущей даты в выбранном периоде. Зелёная полоска сигнализирует, что вы укладывайтесь в бюджет, а красная - что с текущим темпом трат, вы можете не уложиться в планируемую сумму"></td>
                        <td class="w5" class="efTooltip" title=""></td>
                        <td class="w6" class="efTooltip" title="Разница между планом и фактом"></td>
                    </tr>
                </table>
            </div>
            <div class="timeline hidden" style="position: relative; left: 209px; width: 63px; height: 20px; padding-top: 10px; top: 50px;">
                <div style="position: absolute; left: -10px; width: 20px;">1</div>
                <div class="budgetPeriodEnd" style="position: absolute; left: 70px; width: 60px;"></div>
                <div style="position: relative; left: 0px; top: 7px; width: 64px; height: 1px; background-color: rgb(170, 170, 170);"></div>
                <div id="budgetTimeLine" class="line hidden" style="position: absolute; z-index: 2000; left: 0%; width: 1px; height: 226px; background-color: #AAAAAA;"></div>
            </div>
            <div class="list body">
                загрузка&hellip;
            </div>
            <div class="timeline  hidden" style="position: relative; left: 204px; width: 64px; height: 20px; padding-top: 10px; ">
                <div style="position: absolute; left: -10px; width: 20px;">1</div>
                <div class="budgetPeriodEnd" style="position: absolute; left: 70px; width: 60px;"></div>
                <div style="position: relative; left: 0px; top: 7px; width: 64px; height: 1px; background-color: rgb(170, 170, 170);"></div>
            </div>
        </div>

    </div>
</center>
    <div class="cb"><div></div></div>
</div>


<!-- master -->
<div id="master" class="budget" style="display: none">
    <div class="l-dialog-content">
    <!-- step1.get date -->
        <div id="step1" class="step">
            <div class="master head">
                <h4>Шаг 1 из 3. Мастер планирования бюджета.</h4>
            </div>
            <div class="master body">
                <p><input type="radio" id="new" name="type" plantype="new" checked="checked"><label for="new">Создать новый или отредактировать имеющийся</label></p>
                <p><input type="radio" id="copy" name="type" plantype="copy"><label for="copy"> Создать новый из копии существующего</label></p>
                <p>Месяц, на который нужно запланировать бюджет</p>
                <p>
                    <select id="month" name="month">
                        <option value="01">Январь</option>
                        <option value="02">Февраль</option>
                        <option value="03">Март</option>
                        <option value="04">Апрель</option>
                        <option value="05">Май</option>
                        <option value="06">Июнь</option>
                        <option value="07">Июль</option>
                        <option value="08">Август</option>
                        <option value="09">Сентябрь</option>
                        <option value="10">Октябрь</option>
                        <option value="11">Ноябрь</option>
                        <option value="12">Декабрь</option>
                    </select>
                    <input type="text" value="2009" id="year" name="year"/>
                </p>
                <p class="copy hidden">Месяц, с которого нужно cкопировать бюджет</p>
                <p class="copy hidden">
                    <select id="copy_month" name="copy_month">
                        <option value="01">Январь</option>
                        <option value="02">Февраль</option>
                        <option value="03">Март</option>
                        <option value="04">Апрель</option>
                        <option value="05">Май</option>
                        <option value="06">Июнь</option>
                        <option value="07">Июль</option>
                        <option value="08">Август</option>
                        <option value="09">Сентябрь</option>
                        <option value="10">Октябрь</option>
                        <option value="11">Ноябрь</option>
                        <option value="12">Декабрь</option>
                    </select>
                    <input type="text" value="2009" id="copy_year" name="copy_year"/>
                </p>
            </div>
            <div class="master foot">
                <div id="tostep2" class="button master next">Следующий шаг</div>
            </div>
        </div>
        <!-- step2. conntrol profit -->
        <div id="step2" class="waste_list step">
            <div class="master head">
                <h4>Заголовок заполняется скриптом</h4>
            </div>
            <div class="master body">
                <div class="list head">
                    <table>
                        <tr>
                            <td class="w1" class="efTooltip" title="Категория, по которой будет учитываться бюджет">Категория</td>
                            <td class="w2" class="efTooltip" title="Сумма, выделенная в бюджете на эту категорию">Сумма, руб.</td>
                            <td class="w4" class="efTooltip" title="">Сред. доход, руб.</td>
                        </tr>
                    </table>
                </div>
                <div class="list body"></div>
                <div class="f_field3"></div>
            </div>
            <div class="master foot">
                <div id="tostep1" class="button master prev">Предыдущий шаг</div>
                <div id="tostep3" class="button master next">Следующий шаг</div>
            </div>
        </div>
        <!-- step3. conntrol drain -->
        <div id="step3" class="waste_list step">
            <div class="master head">
                <h4>Заголовок заполняется скриптом</h4>
            </div>
            <div class="master body">
                <div class="list head">
                    <table>
                        <tr>
                            <td class="w1" class="efTooltip" title="Категория, по которой будет учитываться бюджет">Категория</td>
                            <td class="w2" class="efTooltip" title="Сумма, выделенная в бюджете на эту категорию">Сумма, руб.</td>
                            <td class="w4" class="efTooltip" title="">Сред. расход, руб.</td>
                        </tr>
                    </table>
                </div>
                <div class="list body"></div>
                <div class="f_field3"></div>
            </div>
            <div class="master foot">
                <div id="tostep2" class="button master prev">Предыдущий шаг</div>
                <div id="tosave" class="button master next">Сохранить</div>
            </div>
        </div>

    </div>
</div>
<!-- /master -->
