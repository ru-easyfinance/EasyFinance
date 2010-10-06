<div class="b-special-rows">
    <div id="divUseReminders" class="hidden">
        <div class="b-row">
            <div class="b-col">
                <div class="b-col-indent">
                    <label>
                        <input type="checkbox" id="op_checkReminders" />
                        Напомнить об операции по SMS или E-mail
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div id="reminderOptions_profile" class="hidden">
        <div class="b-row cols">
            <div class="b-col">
                <div class="b-col-indent">
                    <label class="b-custom-input-label">Телефон:</label>
                    <div class="b-custom-input b-phone">
                        <div class="b-custom-input-border">
                            <input class="b-custom-input-field" id="smsPhone" name="phone" />
                        </div>
                    </div>
                    <p class="b-custom-input-hint">Пример: +79161234567</p>
                    <p class="b-custom-input-hint_msg">
                        <b>Настройки по умолчанию</b>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div id="tableReminders" class="hidden">
        <div class="b-row cols2">
            <div class="b-col">
                <div class="b-col-indent">
                    <label>
                        <input type="checkbox" id="checkReminderMail" />
                        По E-Mail
                    </label>
                </div>
            </div>
            <div class="b-col">
                <div class="b-col-indent">
                    <label>
                        <input type="checkbox" id="checkReminderSms" />
                        По SMS
                    </label>
                </div>
            </div>
        </div>
        <div class="b-row cols2">
            <div class="b-col">
                <div class="b-col-indent">
                    <div class="b-custom-input">
                        <div class="b-custom-input-border">
                            <input class="b-custom-input-field" type="text" disabled="disabled" />
                            <div class="b-custom-select-wrap">
                                <select class="b-custom-select" id="mailDaysBefore" name="mailDaysBefore">
                                    <option value="0">В тот же день</option>
                                    <option value="1">За 1 день</option>
                                    <option value="2">За 2 дня</option>
                                    <option value="3">За 3 дня</option>
                                    <option value="7">За неделю</option>
                                    <option value="31">За месяц</option>
                                </select>
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
                    <div class="b-custom-input">
                        <div class="b-custom-input-border">
                            <input class="b-custom-input-field" type="text" disabled="disabled" />
                            <div class="b-custom-select-wrap">
                                <select class="b-custom-select" id="smsDaysBefore" name="smsDaysBefore">
                                    <option value="0">В тот же день</option>
                                    <option value="1">За 1 день</option>
                                    <option value="2">За 2 дня</option>
                                    <option value="3">За 3 дня</option>
                                    <option value="7">За неделю</option>
                                    <option value="31">За месяц</option>
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
        <div class="b-row cols2">
            <div class="b-col">
                <div class="b-col-indent">
                    <div class="b-custom-input-inline">
                        <label>В</label>
                        <div class="b-custom-input b-notification_hour">
                            <div class="b-custom-input-border">
                                <input class="b-custom-input-field" type="text" disabled="disabled" />
                                <div class="b-custom-select-wrap">
                                    <select class="b-custom-select" id="mailHour" name="mailHour">
                                        <option value="0">00</option>
                                        <option value="1">01</option>
                                        <option value="2">02</option>
                                        <option value="3">03</option>
                                        <option value="4">04</option>
                                        <option value="5">05</option>
                                        <option value="6">06</option>
                                        <option value="7">07</option>
                                        <option value="8">08</option>
                                        <option value="9">09</option>
                                        <option value="10">10</option>
                                        <option value="11">11</option>
                                        <option value="12">12</option>
                                        <option value="13">13</option>
                                        <option value="14">14</option>
                                        <option value="15">15</option>
                                        <option value="16">16</option>
                                        <option value="17">17</option>
                                        <option value="18">18</option>
                                        <option value="19">19</option>
                                        <option value="20">20</option>
                                        <option value="21">21</option>
                                        <option value="22">22</option>
                                        <option value="23">23</option>
                                    </select>
                                </div>
                                <div class="b-custom-select-trigger">
                                    <i></i>
                                </div>
                            </div>
                        </div>
                        <label class="delimiter">:</label>
                        <div class="b-custom-input b-notification_min">
                            <div class="b-custom-input-border">
                                <input class="b-custom-input-field" type="text" disabled="disabled" />
                                <div class="b-custom-select-wrap">
                                    <select class="b-custom-select" id="mailMinutes" name="mailHour">
                                        <option value="0">00</option>
                                        <option value="15">15</option>
                                        <option value="30">30</option>
                                        <option value="45">45</option>
                                    </select>
                                </div>
                                <div class="b-custom-select-trigger">
                                    <i></i>
                                </div>
                            </div>
                        </div>
                        <b class="clear"></b>
                    </div>
                </div>
            </div>
            <div class="b-col">
                <div class="b-col-indent">
                    <div class="b-custom-input-inline">
                        <label>В</label>
                        <div class="b-custom-input b-notification_hour">
                            <div class="b-custom-input-border">
                                <input class="b-custom-input-field" type="text" disabled="disabled" />
                                <div class="b-custom-select-wrap">
                                    <select class="b-custom-select" id="smsHour" name="mailHour">
                                        <option value="0">00</option>
                                        <option value="1">01</option>
                                        <option value="2">02</option>
                                        <option value="3">03</option>
                                        <option value="4">04</option>
                                        <option value="5">05</option>
                                        <option value="6">06</option>
                                        <option value="7">07</option>
                                        <option value="8">08</option>
                                        <option value="9">09</option>
                                        <option value="10">10</option>
                                        <option value="11">11</option>
                                        <option value="12">12</option>
                                        <option value="13">13</option>
                                        <option value="14">14</option>
                                        <option value="15">15</option>
                                        <option value="16">16</option>
                                        <option value="17">17</option>
                                        <option value="18">18</option>
                                        <option value="19">19</option>
                                        <option value="20">20</option>
                                        <option value="21">21</option>
                                        <option value="22">22</option>
                                        <option value="23">23</option>
                                    </select>
                                </div>
                                <div class="b-custom-select-trigger">
                                    <i></i>
                                </div>
                            </div>
                        </div>
                        <label class="delimiter">:</label>
                        <div class="b-custom-input b-notification_min">
                            <div class="b-custom-input-border">
                                <input class="b-custom-input-field" type="text" disabled="disabled" />
                                <div class="b-custom-select-wrap">
                                    <select class="b-custom-select" id="smsMinutes" name="mailHour">
                                        <option value="0">00</option>
                                        <option value="15">15</option>
                                        <option value="30">30</option>
                                        <option value="45">45</option>
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
        </div>
        <div class="b-row">
            <div class="b-col">
                <p class="b-custom-input-hint b-custom-input-hint__warning" id="operationEdit_noReminders" style="display: none;">
                    Для использования напоминаний необходимо 
                    <a href="/my/services/">подключить услугу &laquo;Напоминания по календарю&raquo;</a>.
                </p>
            </div>
        </div>
    </div>
    <b class="clear"></b>
</div>