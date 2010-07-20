<div id="divUseReminders" class="hidden" style="padding-left: 4px;">
    <input type="checkbox" id="op_checkReminders">
    <label for="op_checkReminders">Напомнить об операции по SMS или E-mail</label>
</div>

<div id="reminderOptions_profile" class="hidden">
    <table>
        <tr id="trPhone">
            <td>
                <label for="checkReminderPhone">Телефон:</label>
                <input id="smsPhone" name="phone" />
            </td>
        </tr>
    </table><br />

    <b>Настройки по умолчанию</b><br />
</div>

<table id="tableReminders" class="hidden">
    <tr>
        <td>
            <table>
                <tr>
                    <td>
                        <input id="checkReminderMail" type="checkbox" />
                        <label for="checkReminderMail">По E-Mail</label>
                    </td>
                </tr>
                <tr>
                    <td>
                        <select id="mailDaysBefore" name="mailDaysBefore">
                            <option value="0">В тот же день</option>
                            <option value="1">За 1 день</option>
                            <option value="2">За 2 дня</option>
                            <option value="3">За 3 дня</option>
                            <option value="7">За неделю</option>
                            <option value="31">За месяц</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>В
                        <select id="mailHour" name="mailHour">
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
                        :
                        <select id="mailMinutes" name="mailHour">
                            <option value="00">00</option>
                            <option value="15">15</option>
                            <option value="30">30</option>
                            <option value="45">45</option>
                        </select>
                    </td>
                </tr>
            </table>
        </td>
        <td>
            <table>
                <tr>
                    <td>
                        <input id="checkReminderSms" type="checkbox" />
                        <label for="checkReminderSms">По SMS</label>
                    </td>
                </tr>
                <tr>
                    <td>
                        <select id="smsDaysBefore" name="smsDaysBefore">
                            <option value="0">В тот же день</option>
                            <option value="1">За 1 день</option>
                            <option value="2">За 2 дня</option>
                            <option value="3">За 3 дня</option>
                            <option value="7">За неделю</option>
                            <option value="31">За месяц</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>В
                        <select id="smsHour" name="mailHour">
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
                        :
                        <select id="smsMinutes" name="mailHour">
                            <option value="00">00</option>
                            <option value="15">15</option>
                            <option value="30">30</option>
                            <option value="45">45</option>
                        </select>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
