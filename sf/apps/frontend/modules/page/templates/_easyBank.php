<iframe src="http://wiki.<?php echo URL_ROOT_MAIN; ?>/"
    style="
        width: 100%;
        height: 600px;
        border: none;
        overflow: visible;
        margin: 0 auto;
        display: block;
        min-height: 500px;
    "
    ></iframe>
<?php use_stylesheet('screens/integration.screen.css?r='.REVISION); ?>
<?php use_javascript('screens/integration.screen.js?r='.REVISION); ?>
<?php use_javascript('widgets/profile/userIntegrations.widget.js?r='.REVISION); ?>
<?php use_javascript('integration/omni.js?r='.REVISION); ?>
<?php use_javascript('integration/tabs.js?r='.REVISION); ?>
<?php use_javascript('integration/validator.js?r='.REVISION); ?>
<?php use_javascript('integration/countries.js?r='.REVISION); ?>
<div id="integrationSteps">
    <?php if ($sf_user->isAuthenticated()) : ?>
        <h3><a id="integrationStep1" href="#" class="complete">1. Войти в систему &mdash; готово!</a></h3>
    <?php else : ?>
        <h3><a id="integrationStep1" href="#">1. Зарегистрироваться или войти</a></h3>
    <?php endif; ?>
<div class="step">
    <?php if ($sf_user->isAuthenticated()) : ?>
        <div>Блок для авторизованных пользователей</div><br>
    <?php else : ?>
        <div align="left">
            <input id="btnShowLogin" type="button" value="Войти" />&nbsp;&nbsp;&nbsp;или&nbsp;&nbsp;
            <input id="btnShowRegister" type="button" value="Зарегистрироваться" />&nbsp;&nbsp;&nbsp;
            <a href="/registration" target="_blank">зарегистрироваться</a>
            (форма регистрации откроется в новом окне, после регистрации и входа в систему обновите это окно)<br>
            <?php include_partial('global/common/registration'); ?>
            <?php include_partial('global/common/authentication'); ?>
        </div>
    <?php endif; ?>
</div>
<h3><a id="integrationStep2" href="#">2. Создать E-Mail</a></h3>
<div class="step">
    <div id="integration" class="profile" style="display:block">
        <div id="content" class="inside form">
            <div class="email line">
                <div class="description">
                    <!--
                    <p>Вы можете заводить операции по email автоматически. Т.е. из банковских сообщений об операциях с  карточкой или счетом.</p>
                    <p>Для этого нужно создать в системе специальный секретный email, письма на который будут обрабатываться автоматически.</p>
                    -->
                    <p>Необходимо создать в системе специальный email, который Вы укажете в анкете на получение карты, и на который из банка будет приходить информация о Ваших операциях.</p>

                    <ul class="numberWithPoint">Email должен быть:
                        <li>легко запоминаемым, чтобы безошибочно указать его в анкете</li>
                        <li>не связанным с Вашими личными данными – логином, именем, фамилией, паролем, другим email и т.п. – чтобы другой пользователь, даже обнаружив его существование при попытке создать свой email, ничего о Вас не узнал</li>
                    </ul>
                </div><br>
                <div class="notExist">
                    <p>
                        <label for="txtIntegrationEmail" class="eventDescription">Желаемый email:</label>
                    </p>
                </div>
                <div id="errorMessageInvalidEmail"></div>
                <div class="notExist"><p>
                        <input type="text" value="" id="txtIntegrationEmail">
                        <label for="txtIntegrationEmail" class="emailPostfix">@mail.easyfinance.ru</label>
                        <button id="btnGetIntegrationEmail">Создать</button>
                    </p>
                </div>
                <div class="exist">Ваш e-mail для интеграции: <span id="lblIntegrationEmail">default@ef.ru</span>
                    <button id="btnIntegrationEmailRemove" class="remove" title="Удалить">Удалить</button>
                </div>
            </div>
            <button id="btnIntegrationMailNext" class="hidden" style="font-weight: bold">Далее</button>
        </div>
    </div>
</div>
<h3><a id="integrationStep3" href="#">3. Задать счет для интеграции</a></h3>
<div class="step">
    <!-- <div>Здесь инструкция</div><br> -->
    <div>
        <button id="btnCreateAccount">Создайте счёт</button> для карты "EASYFINANCE"<br><br>

        <div id="divAmtAccounts" class="hidden">Или выберите счёт из списка (отображаются только дебетовые карты):
        <select id="optionAccount"></select><br><br></div>

        <button id="btnBackToEmail" style="font-weight: bold">Назад</button>
        <button id="btnLinkAccount" style="font-weight: bold">Далее</button>

    </div>
</div>
<h3><a id="integrationStep4" href="#">4. Заполнить и распечатать анкету</a></h3>
<div class="step">
    <button id="btnBackToAccount" style="font-weight: bold">Назад</button><br><br>
    <?php include_partial('page/amt/wizard'); ?>
</div>
</div>