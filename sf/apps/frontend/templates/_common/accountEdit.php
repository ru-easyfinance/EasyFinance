<!-- диалог добавления/редактирования счетов -->
<div id="widgetAccountEdit" class="addoperation">
    <div class="line">
        <div class="f_field">
            <label>Тип счета:</label>
            <select name="type" id="acc_type" style="width:200px;">
                <option value="1">Наличные</option>
                <option value="2">Дебетовая карта</option>
                <option value="9">Кредит</option>
                <option value="5">Депозит</option>
                <option value="6">Мне должны</option>
                <option value="7">Я должен</option>
                <option value="8">Кредитная карта</option>
                <option value="15">Электронный кошелек</option>
                <option value="16">Банковский счёт</option>
            </select>
        </div>
        <div id="account_form_fields">
            <div id="divAccountEdit">
                <br class="clr"/>
                <table class="tableForm" cellspacing="0" cellpadding="0">
                    <tr>
                        <td><label>Статус:</label></td>
                    </tr>
                    <tr>
                        <td>
                            <label><input type="radio" value="0" id="acc_state" name="acc_state">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Обычный</label>
                            <label><input type="radio" value="1" id="acc_state" name="acc_state"><img src="/img/i/star.png"/>&nbsp;Избранный</label>
                            <label><input type="radio" value="2" id="acc_state" name="acc_state"><img src="/img/i/archive.png"/>&nbsp;Архивный</label>
                        </td>

                    </tr>
                    <tr>
                        <td><div style="float: left;"><label>Название:</label></div></td>
                    </tr>
                    <tr>
                        <td><input type="text" name="name" id="acc_name" class="" style="" value="" maxlength="20" /></td>
                    </tr>
                    <tr>
                        <td><label>Примечание:</label></td>
                    </tr>
                    <tr>
                        <td><div id="div_acc_comment"></div></td>
                    </tr>
                    <tr id="accountEditTrLabelBalance">
                        <td id="accountEditLabelBalance">Начальный баланс:</td>
                    </tr>
                    <tr id="accountEditTrBalance" class='useCalculator'>
                        <td><input type="text" name="starter_balance" id="acc_balance" class="" style="" value="" /></td>
                    </tr>
                    <tr><td>Валюта счёта:</td></tr>
                    <tr><td><select name="currency_id" id="acc_currency"></select>
                    &nbsp;<a href="/profile/#currency">настроить валюты</a></td></tr>
                </table>
            </div>
        </div>
    </div>
</div>
