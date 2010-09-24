<!--Ввод операции-->
<form style="display:none;" class="op_addoperation" onsubmit="return false;">
    <input type="hidden" id="op_id" /><input type="hidden" id="op_accepted" /><input type="hidden" id="op_chain_id" />
    <div class="b-form-skeleton b-operations-journal-options fixed-width">
        <div class="b-row cols2">
            <div class="b-col">
                <div class="b-col-indent">
                    <label class="b-custom-input-label">Счет:</label>
                    <div class="b-custom-input suggest">
                        <div class="b-custom-input-border">
                            <div class="b-custom-select-wrap">
                                <select class="b-custom-select" id="op_account"></select>
                            </div>
                            <div class="b-custom-select-trigger">
                                <i></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="b-col">
                <div class="b-col-indent">
                    <label class="b-custom-input-label">Тип операции:</label>
                    <div class="b-custom-input suggest">
                        <div class="b-custom-input-border">
                            <div class="b-custom-select-wrap">
                                <select class="b-custom-select" id="op_type"></select>
                            </div>
                            <div class="b-custom-select-trigger">
                                <i></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="b-row">
            <div class="b-col">
                <div class="b-col-indent">
                    <label class="b-custom-input-label">Категория:</label>
                    <div class="b-custom-input suggest">
                        <div class="b-custom-input-border">
                            <div class="b-custom-select-wrap">
                                <select class="b-custom-select" id="op_category"></select>
                            </div>
                            <div class="b-custom-select-trigger">
                                <i></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Финансовая цель -->
        <div id="op_target_fields" style="display:none;">
            <div class="b-row">
                <div class="b-col">
                    <div class="b-col-indent">
                        <label class="b-custom-input-label">Финансовая цель:</label>
                        <div class="b-custom-input suggest">
                            <div class="b-custom-input-border">
                                <div class="b-custom-select-wrap">
                                    <select class="b-custom-select" id="op_target"></select>
                                </div>
                                <div class="b-custom-select-trigger">
                                    <i></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="b-row">
                <div class="b-col">
                    <div class="b-col-indent">
                        <input type="hidden" id="op_close2" value="0" />
                        <label>
                            <input type="checkbox" id="op_close" name="op_close" value="Закрытая финансовая цель" />
                            Закрыть финансовую цель
                        </label>
                    </div>
                </div>
            </div>
            <div class="b-row cols2">
                <div class="b-col text-right">
                    <div class="b-col-indent">
                        Стоимость цели:
                    </div>
                </div>
                <div class="b-col">
                    <div class="b-col-indent">
                        <span id='op_amount_target'></span>
                        <span class='op_currency'></span>
                    </div>
                </div>
            </div>
            <div class="b-row cols2">
                <div class="b-col text-right">
                    <div class="b-col-indent">
                        Сумма накопленная:
                    </div>
                </div>
                <div class="b-col">
                    <div class="b-col-indent">
                        <span id='op_amount_done'></span>
                        <span class='op_currency'></span>
                    </div>
                </div>
            </div>
            <div class="b-row cols2">
                <div class="b-col text-right">
                    <div class="b-col-indent">
                        Прогноз:
                    </div>
                </div>
                <div class="b-col">
                    <div class="b-col-indent">
                        <span id='op_forecast_done'></span>%
                    </div>
                </div>
            </div>
            <div class="b-row cols2">
                <div class="b-col text-right">
                    <div class="b-col-indent">
                        Процент готовности:
                    </div>
                </div>
                <div class="b-col">
                    <div class="b-col-indent">
                        <span id='op_percent_done'></span>%
                    </div>
                </div>
            </div>
        </div>

        <!-- Трансфер -->
        <div id="op_transfer_fields" style="display:none;">
            <div class="b-row">
                <div class="b-col">
                    <div class="b-col-indent">
                        <label class="b-custom-input-label">На счет:</label>
                        <div class="b-custom-input suggest">
                            <div class="b-custom-input-border">
                                <div class="b-custom-select-wrap">
                                    <select class="b-custom-select" id="op_AccountForTransfer"></select>
                                </div>
                                <div class="b-custom-select-trigger">
                                    <i></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="b-row cols2" id="op_operationTransferCurrency" style="display:none;">
                <div class="b-col text-right">
                    <div class="b-col-indent">
                        <div class="b-custom-input">
                            <div class="b-custom-input-border">
                                <input class="b-custom-input-field" type="text" id="op_currency" value="" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="b-row cols4">
            <div class="b-col">
                <div class="b-col-indent">
                    <label class="b-custom-input-label">Дата:</label>
                    <div class="b-custom-input b-date">
                        <div class="b-custom-input-border">
                            <input class="b-custom-input-field" type="text" id="op_date" value="" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="b-col invert-width">
                <div class="b-col-indent">
                    <label class="b-custom-input-label">Сумма:</label>
                    <div class="b-custom-input-container icon calculator">
                        <div class="b-custom-input">
                            <div class="b-custom-input-border">
                                <input class="b-custom-input-field" type="text" id="op_amount" value="" />
                            </div>
                        </div>
                        <i class="b-custom-input-icon" id="btnCalcSum"></i>
                    </div>
                    <p class="b-custom-input-hint">Пример: 12 + 33 * 45 &lt;Enter&gt;</p>
                </div>
            </div>
        </div>
        <div id="div_op_transfer_line" style="display:none;">
            <div class="b-row cols2">
                <div class="b-col">
                    <div class="b-col-indent">
                        <label class="b-custom-input-label">Курс:</label>
                        <div class="b-custom-input">
                            <div class="b-custom-input-border">
                                <input class="b-custom-input-field hasDatepicker" type="text" autocomplete="off" id="op_conversion" value="" />
                            </div>
                        </div>
                        <p class="b-custom-input-hint" id="op_conversion_text"></p>
                    </div>
                </div>
                <div class="b-col">
                    <div class="b-col-indent">
                        <label class="b-custom-input-label">Сумма полученная:</label>
                        <div class="b-custom-input">
                            <div class="b-custom-input-border">
                                <input class="b-custom-input-field hasDatepicker" type="text" id="op_transfer" autocomplete="off" value="" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="b-row">
            <div class="b-col">
                <div class="b-col-indent">
                    <label class="b-custom-input-label">Метки:</label>
                    <div class="b-custom-input-container icon tags">
                        <div class="b-custom-input">
                            <div class="b-custom-input-border">
                                <input class="b-custom-input-field" type="text" id="op_tags" value="" />
                            </div>
                        </div>
                        <i class="b-custom-input-icon"></i>
                    </div>
                    <p class="b-custom-input-hint">Пометки для быстрого поиска. Например: аванс</p>
                </div>
            </div>
        </div>
        <div class="b-row">
            <div class="b-col">
                <div class="b-col-indent">
                    <label class="b-custom-textarea-label">Комментарии:</label>
                    <div class="b-custom-textarea">
                        <div class="b-custom-textarea-border" id="div_op_comment">
                            <textarea class="b-custom-textarea-field" rows="3"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- BEGIN поля для планирования -->
        <div id="operationEdit_planning" style="display:none;">
            <div class="b-row">
                <div class="b-col">
                    <div class="b-col-indent">
                        <label class="b-custom-textarea-label">Повторить:</label>
                        <div class="b-custom-input">
                            <div class="b-custom-input-border">
                                <input class="b-custom-input-field" type="text" disabled="disabled" />
                                <div class="b-custom-select-wrap">
                                    <select class="b-custom-select" id="cal_repeat">
                                        <option value="0">Без повторения</option>
                                        <option value="1">Каждый день</option>
                                        <option value="7">Каждую неделю</option>
                                        <option value="30">Каждый месяц</option>
                                        <option value="90">Каждый квартал</option>
                                        <option value="365">Каждый год</option>
                                    </select>
                                </div>
                                <div class="b-custom-select-trigger">
                                    <i></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="b-row" id="operationEdit_weekdays" style="display:none;">
                <div class="b-col">
                    <div class="b-col-indent">
                        <table class="week">
                            <tbody>
                                <tr>
                                    <td>
                                        <label for="cal_mon">Пн</label>
                                    </td>
                                    <td>
                                        <label for="cal_tue">Вт</label>
                                    </td>
                                    <td>
                                        <label for="cal_wed">Ср</label>
                                    </td>
                                    <td>
                                        <label for="cal_thu">Чт</label>
                                    </td>
                                    <td>
                                        <label for="cal_fri">Пт</label>
                                    </td>
                                    <td>
                                        <label for="cal_sat">Сб</label>
                                    </td>
                                    <td>
                                        <label for="cal_sun">Вс</label>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <input type="checkbox" value="1" id="cal_mon" name="mon" />
                                    </td>
                                    <td>
                                        <input type="checkbox" value="1" id="cal_tue" name="tue" />
                                    </td>
                                    <td>
                                        <input type="checkbox" value="1" id="cal_wed" name="wed" />
                                    </td>
                                    <td>
                                        <input type="checkbox" value="1" id="cal_thu" name="thu" />
                                    </td>
                                    <td>
                                        <input type="checkbox" value="1" id="cal_fri" name="fri" />
                                    </td>
                                    <td>
                                        <input type="checkbox" value="1" id="cal_sat" name="sat" />
                                    </td>
                                    <td>
                                        <input type="checkbox" value="1" id="cal_sun" name="sun" />
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div id="operationEdit_repeating" style="display:none;">
                <div class="b-row cols2">
                    <div class="b-col text-right center-input">
                        <div class="b-col-indent">
                            <label for="cal_rep_every">Повторить несколько раз</label>
                            <input type="radio" checked="checked" rep="1" id="cal_rep_every" name="rep_type" />
                        </div>
                    </div>
                    <div class="b-col">
                        <div class="b-col-indent">
                            <div class="b-custom-input b-date">
                                <div class="b-custom-input-border">
                                    <input class="b-custom-input-field" type="text" id="cal_count" value="1" maxlength="3" name="count" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="b-row cols2">
                    <div class="b-col text-right center-input">
                        <div class="b-col-indent">
                            <label for="cal_rep_to">Повторить до даты</label>
                            <input type="radio" rep="3" id="cal_rep_to" name="rep_type" />
                        </div>
                    </div>
                    <div class="b-col">
                        <div class="b-col-indent">
                            <div class="b-custom-input b-date">
                                <div class="b-custom-input-border">
                                    <input class="b-custom-input-field" type="text" id="cal_date_end" value="" name="date_end" disabled="disabled" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="b-row" id="operationEdit_reminders" style="display:none;">
            <div class="b-col">
                <div class="b-col-indent">
                    <?php include_partial('global/common/reminders') ?>
                    <p class="b-custom-input-hint" id="operationEdit_noReminders" style="display:none;">Для использования напоминаний необходимо подключить услугу <a href="/my/services/">"Напоминания о событиях"</a>!</p>
                </div>
            </div>
        </div>
        <b class="clear"></b>
    </div>
</form>
<!--/Ввод операции-->
