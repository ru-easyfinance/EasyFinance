<div class="js-widget js-widget-budgetwizard" style="display: none">
    <div class="js-control-dialogue">
        <div class="l-dialog-content b-budgetwizard">

            <div class="b-budgetwizard-step b-budgetwizard-step__1">
                <div class="b-budgetwizard-step-head">
                    <h4>Шаг 1 из 3. Мастер планирования бюджета.</h4>
                </div>
                <div class="b-budgetwizard-step-body">
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
                <div class="b-budgetwizard-footer">
                    <div class="bb-budgetwizard-footer-next">Следующий шаг</div>
                </div>
            </div>


            <div class="b-budgetwizard-step b-budgetwizard-step__2">
                <div class="b-budgetwizard-step-head">
                    <h4>Заголовок заполняется скриптом</h4>
                </div>
                <div class="b-budgetwizard-step-body">
                    <table class="b-budgetwizard-table b-collapsible_table js-control js-control-collapsible_table">
                        <colgroup>
                            <col class="b-budgetwizard-columns__catname"/>
                            <col class="b-budgetwizard-columns__amount"/>
                            <col class="b-budgetwizard-columns__mean"/>
                            <col class="b-budgetwizard-columns__calendar"/>
                        </colgroup>

                        <thead class="b-budgetwizard-table__caption">

                        </thead>

                        <thead class="b-collapsible_table-thead b-collapsible_table-thead__expanded">
                            <tr>
                                <td class="js-collapsible_table-collapser">
                                    <i class="g-icon"></i>
                                </td>
                                <td colspan="3"></td>
                            </tr>
                        </thead>

                        <tbody class="b-collapsible_table-tbody">

                        </tbody>
                    </table>

                    <div class="b-budgetwizard-summary">
                        <div class="b-budgetwizard-summary-profit"></div>
                        <div class="b-budgetwizard-summary-drain"></div>
                        <div class="b-budgetwizard-summary-remainder"></div>
                    </div>

                    <div class="b-budgetwizard-footer">
                        <div class="b-budgetwizard-footer-prev">Предыдущий шаг</div>
                        <div class="b-budgetwizard-footer-next">Следующий шаг</div>
                    </div>
                </div>
            </div>


            <div class="b-budgetwizard-step b-budgetwizard-step__3">
                <div class="b-budgetwizard-step-head">
                    <h4>Заголовок заполняется скриптом</h4>
                </div>
                <div class="b-budgetwizard-step-body">
                    <table class="b-budgetwizard-table b-collapsible_table js-control js-control-collapsible_table">
                        <colgroup>
                            <col class="b-budgetwizard-columns__catname"/>
                            <col class="b-budgetwizard-columns__amount"/>
                            <col class="b-budgetwizard-columns__mean"/>
                            <col class="b-budgetwizard-columns__calendar"/>
                        </colgroup>

                        <thead class="b-budgetwizard-table__caption">

                        </thead>

                        <thead class="b-collapsible_table-thead b-collapsible_table-thead__expanded">
                            <tr>
                                <td class="js-collapsible_table-collapser">
                                    <i class="g-icon"></i>
                                </td>
                                <td colspan="3"></td>
                            </tr>
                        </thead>

                        <tbody class="b-collapsible_table-tbody">

                        </tbody>
                    </table>

                    <div class="b-budgetwizard-summary">
                        <div class="b-budgetwizard-summary-profit"></div>
                        <div class="b-budgetwizard-summary-drain"></div>
                        <div class="b-budgetwizard-summary-remainder"></div>
                    </div>
                    <div class="b-budgetwizard-footer">
                        <div class="b-budgetwizard-footer-prev">Предыдущий шаг</div>
                        <div class="b-budgetwizard-footer-next">Сохранить</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>