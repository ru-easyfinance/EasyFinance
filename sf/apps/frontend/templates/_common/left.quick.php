<div id="leftPanel" class="block1 b-leftpanel js-leftpaneltabs">
    <ul class="b-leftpanel_tabs control js-leftpaneltabs-tabs">
        <li class="b-leftpanel_tabs_item b-leftpanel_tabs_item_accounts" title="Счета"><i></i></li>
        <li class="b-leftpanel_tabs_item b-leftpanel_tabs_item_tags" title="Метки"><i></i></li>
        <li class="b-leftpanel_tabs_item b-leftpanel_tabs_item_operations" title="Операции"><i></i></li>
        <li class="b-leftpanel_tabs_item b-leftpanel_tabs_item_targets" title="Финансовые цели"><i></i></li>
    </ul>

<!--счета-->
    <div class="listing accounts c2 js-leftpaneltabs-panel" style="display:none">
        <div class="title">
            <h2><a href="#" title="Добавить счёт" class="addaccountlink">Добавить счёт</a></h2>
            <a href="#" title="Добавить счёт" class="add">Добавить счёт</a>
        </div>
        <!-- <h2 class="b-leftpanel-title addaccountlink"><span>Счета</span><i></i></h2> -->
        <dl class="bill_list">
            <dt class="hidden b-accountgroup js-type-favourite">
                <span class="b-accountgroup-name">Избранные</span>
                <span class="b-accountgroup-sum js-acc-sum"></span>
            </dt>
            <dd id="accountsPanelAcc0" class="hidden"> <!-- --> </dd>

            <dt class="hidden b-accountgroup js-type-cash">
                <span class="b-accountgroup-name">Деньги</span>
                <span class="b-accountgroup-sum js-acc-sum"></span>
            </dt>
            <dd id="accountsPanelAcc1" class="hidden"> <!-- --> </dd>

            <dt class="hidden b-accountgroup js-type-borrowed">
                <span class="b-accountgroup-name">Мне должны</span>
                <span class="b-accountgroup-sum js-acc-sum"></span>
            </dt>
            <dd id="accountsPanelAcc2" class="hidden"> <!-- --> </dd>

            <dt class="hidden b-accountgroup js-type-owe">
                <span class="b-accountgroup-name">Я должен</span>
                <span class="b-accountgroup-sum js-acc-sum"></span>
            </dt>
            <dd id="accountsPanelAcc3" class="hidden"> <!-- --> </dd>

            <dt class="hidden b-accountgroup js-type-invested">
                <span class="b-accountgroup-name">Инвестиции</span>
                <span class="b-accountgroup-sum js-acc-sum"></span>
            </dt>
            <dd id="accountsPanelAcc4" class="hidden"> <!-- --> </dd>

            <dt class="hidden b-accountgroup js-type-property">
                <span class="b-accountgroup-name">Имущество</span>
                <span class="b-accountgroup-sum js-acc-sum"></span>
            </dt>
            <dd id="accountsPanelAcc5" class="hidden"> <!-- --> </dd>

            <dt>Сумма:</dt>
            <dd id="accountsPanel_amount" class="amount"> <!-- --> </dd>

            <dt class="hidden b-accountgroup js-type-archive">
                <span class="b-accountgroup-name">Скрытые</span>
                <span class="b-accountgroup-sum js-acc-sum"></span>
            </dt>
            <dd id="accountsPanelAccArchive" class="hidden"> <!-- --> </dd>
        </dl>
    </div>
    <div id="accountDeletionConfirm" style="display:none;">
        <div class="l-dialog-content">
            <p>При удалении счета будут удалены все операции по нему, и учет может &laquo;поплыть&raquo;.</p>
            <p>Вместо удаления предлагаем скрыть счет &mdash; он будет отображаться только в группе &laquo;Скрытые&raquo; и не будет Вам мешать.</p>
        </div>
    </div>


<!-- Тэги -->
    <div class="listing tags_list c3 js-leftpaneltabs-panel" style="display:none">
        <div class="title">
            <h2><a href="#" title="Добавить метку" class="addlink">Добавить метку</a></h2>
            <a href="#" title="Добавить метку" class="add">Добавить метку</a>
        </div>
        <!-- <h2 class="b-leftpanel-title addtaglink"><span>Добавить метку</span><i></i></h2> -->
    </div>
    <div class="edit_tag" style="display:none">
        <center>
            <div class="f_field">
                <label for="tag">Метка</label>
                <input type="text" value="" id="tag" name="tag" />
                <input type="hidden" value="" id="old_tag" name="old_tag" />
            </div>
        </center>
    </div>
    <div class="add_tag" style="display:none">
        <center>
            <div class="f_field">
                <label for="tags">Метка</label>
                <input type="text" value="" id="tag" name="tag" />
            </div>
        </center>
    </div>


<!-- Календарь -->
    <div id="calendarLeft" class="listing transaction c4 js-leftpaneltabs-panel" style="display:none">
        <div class="overdue"></div>
        <div class="future"></div>
        &nbsp;&nbsp;&nbsp;&nbsp;<a href="/calendar/#list" id="AshowEvents">Журнал событий</a><br><br>
    </div>

<!-- Финансовые цели -->
    <div class="listing financobject c5 js-leftpaneltabs-panel" style="display:none"></div>

</div>
