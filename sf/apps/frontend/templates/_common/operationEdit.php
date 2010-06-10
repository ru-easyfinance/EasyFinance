<!--Ввод операции-->
<form style="display:none;" class="op_addoperation" onsubmit="return false;">
    <input type="hidden" id="op_id" /><input type="hidden" id="op_accepted" /><input type="hidden" id="op_chain_id" />
    <div class="op_addoperation" style="display: block;">
        <div>
            <div class="op_f_field" id="div_op_first_line">
                <div id="div_op_account_select">
                    <label for="op_account">
                        Счет
                    </label>
                    <select id="op_account"></select>
                </div>
                <div id="div_op_type_select">
                    <label for="op_type">
                        Тип операции
                    </label>
                    <select id="op_type">
                        <option selected="selected" value="0">Расход</option>
                        <option value="1">Доход</option>
                        <option value="2">Перевод со счёта</option>
                        <option value="4">Перевод на фин. цель</option>
                    </select>
                </div>
            </div>
        </div>
        <div>
            <div id="op_category_fields">Категория<br />
                <select id="op_category"></select>
            </div>
        </div>
        <div class="op_line">
            <div class="op_field" id="op_target_fields" style="display:none;">
                <label for="op_target">
                    Финансовая цель:
                </label>
                <select id="op_target"></select>
                <div>
                    <div>
                        Стоимость цели: <span id='op_amount_target'></span>
                        <span class='op_currency'></span>
                    </div>
                    <div>
                        Сумма накопленная: <span id='op_amount_done'></span>
                        <span class='op_currency'></span>
                    </div>
                    <div>
                        Прогноз: <span id='op_forecast_done'></span>
                        %
                    </div>
                    <div>
                        Процент готовности: <span id='op_percent_done'></span>
                        %
                    </div>
                </div>
                <input type="hidden" id="op_close2" value="0"><input style="display:none" type="checkbox" id="op_close" name="op_close" value="Закрытая финансовая цель" />
                <label style="display:none" for="op_close">
                    Закрытая финансовая цель
                </label>
            </div>
        </div>
        <div class="op_line op_line_transfer">
            <div class="op_field" id="op_transfer_fields" style="display:none;">
                <label for="op_AccountForTransfer">
                    На счет:
                </label>
                <select id="op_AccountForTransfer"></select>
                <span id="op_operationTransferCurrency" style="display:none;"><span></span><input type='text' id='op_currency'/><span id="op_convertSumCurrency"></span></span>
            </div>
        </div>
        <div class="op_line">
            <div class="op_f_field op_amount" id="op_amount_fields">
                <label id="lblOpAmount" for="op_amount">
                    Сумма
                </label>
                <div class="amount">
                    <div class="amount_general">
                        <input type="text" id="op_amount" >
                        <span id="btnCalcSum" title="калькулятор" border="0" ></span>
                    </div>
                    <div class="amount_help">
                        12 + 33 * 45 &lt;Enter&gt;
                    </div>
                </div>
            </div>
            <div class="op_f_field date" id="op_date_fields">
                <label for="op_date">
                    Дата
                </label>
                <input type="text" value="{$date}" id="op_date" class="op_inp" >
            </div>
        </div>
        <div id="div_op_transfer_line" class="op_f_field">
            <div id="op_conversion_fields">
                <label for="op_conversion">
                    Курс
                </label>
                <div id="div_op_type">
                    <input type="text" class="op_inp hasDatepicker " autocomplete="off" id="op_conversion" value="" />
                    <div id="op_conversion_text" align="right" style="float: right; position: relative; right: 10px;">
                        &nbsp;
                    </div>
                </div>
            </div>
            <div id="op_account_select">
                <label for="op_account">
                    Сумма полученная
                </label>
                <div id="div_op_account">
                    <input type="text" value="" id="op_transfer" autocomplete="off" class="op_inp hasDatepicker ">
                    <span id="btnCalcSumTransfer" title="калькулятор" border="0" ></span>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;или
                </div>
            </div>
        </div>
        <div class="op_line">
            <div class="op_f_field op_tags">
                <label for="op_tags" class="efTooltip" title="Пометки для быстрого поиска. Например: аванс">
                    Метки
                </label>
                <input type="text" value="" id="op_tags" class="op_inp efTooltip" title="Пометки для быстрого поиска. Например: аванс" autocomplete="off" /><a id="op_tags" title="Редактировать"></a>
            </div>
            <div class="op_tags_could" id="op_tags">
            </div>
        </div>
        <div class="op_line">
            <label for="op_comment">
                Комментарии
            </label>
            <div id="div_op_comment"></div>
        </div>
        <div class="op_line hidden" id="operationEdit_planning">
            <div class="line" style="margin-bottom: 6px;">
                <label for="cal_repeat">
                    Повторить
                </label>
                <select id="cal_repeat" name="repeat">
                    <option value="0">Без повторения</option>
                    <option value="1">Каждый день</option>
                    <option value="7">Каждую неделю</option>
                    <option value="30">Каждый месяц</option>
                    <option value="90">Каждый квартал</option>
                    <option value="365">Каждый год</option>
                </select>
            </div>
            <div class="line periodic event hidden" id="operationEdit_weekdays">
                <div class="cell">
                    <table class="week">
                        <tbody>
                            <tr>
                                <td>
                                    <label for="cal_mon">
                                        Пн
                                    </label>
                                </td>
                                <td>
                                    <label for="cal_tue">
                                        Вт
                                    </label>
                                </td>
                                <td>
                                    <label for="cal_wed">
                                        Ср
                                    </label>
                                </td>
                                <td>
                                    <label for="cal_thu">
                                        Чт
                                    </label>
                                </td>
                                <td>
                                    <label for="cal_fri">
                                        Пт
                                    </label>
                                </td>
                                <td>
                                    <label for="cal_sat">
                                        Сб
                                    </label>
                                </td>
                                <td>
                                    <label for="cal_sun">
                                        Вс
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <input type="checkbox" value="1" id="cal_mon" name="mon">
                                </td>
                                <td>
                                    <input type="checkbox" value="1" id="cal_tue" name="tue">
                                </td>
                                <td>
                                    <input type="checkbox" value="1" id="cal_wed" name="wed">
                                </td>
                                <td>
                                    <input type="checkbox" value="1" id="cal_thu" name="thu">
                                </td>
                                <td>
                                    <input type="checkbox" value="1" id="cal_fri" name="fri">
                                </td>
                                <td>
                                    <input type="checkbox" value="1" id="cal_sat" name="sat">
                                </td>
                                <td>
                                    <input type="checkbox" value="1" id="cal_sun" name="sun">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="hidden" style="margin-bottom: 6px;" id="operationEdit_repeating">
                <div class="" style="position: relative;">
                    <label for="cal_rep_every">
                        Повторить несколько раз
                    </label>
                    <input type="radio" checked="checked" rep="1" class="rep_type" id="cal_rep_every" name="rep_type"><input type="text" maxlength="3" value="1" class="ui-widget-content" id="cal_count" name="count">
                </div>
                <div class="">
                    <label for="cal_rep_to">
                        Повторить до даты
                    </label>
                    <input type="radio" rep="3" class="rep_type" id="cal_rep_to" name="rep_type"><input type="text" value="" id="cal_date_end" name="date_end" disabled="disabled">
                </div>
            </div>
        </div>
    </div>
</form><!--/Ввод операции-->
