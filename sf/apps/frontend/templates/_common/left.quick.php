<div id="leftPanel" class="block1">
    <ul class="control">
        <li id="c1"><a title="Навигация"></a></li>
        <li id="c2"><a title="Счета"></a></li>
        <li id="c3"><a title="Метки">Tags</a></li>
        <li id="c4"><a title="Операции"></a></li>
        <li id="c5"><a title="Фин. цели"></a></li>
    </ul>
    <div class="listing navigation c1" style="display:none">
        <ul>
            <li class="act"><span>Навигация</span>
            <ul>
                <li>
                    <a href="/info/">Инфо-панель</a>
                    <ul>
                        <li><a href="/info/">Инфо-панель</a></li>
                        <li><a href="/profile/">Профиль</a></li>
                        <li><a href="/my/services/">Услуги</a></li>
                    </ul>
                </li>
                <li>
                    <a href="/accounts/" class="parent">Счета</a>
                    <ul>
                        <li><a href="/accounts/">Счета</a></li>
                        <li><a href="/operation/">Операции</a></li>
                        <li><a href="/category/">Категории</a></li>
                    </ul>
                </li>
                <li>
                    <a href="/budget/" class="parent">Бюджет</a>
                    <ul>
                        <li><a href="/budget/">Бюджет</a></li>
                        <li><a href="/targets/">Фин. цели</a></li>
                    </ul>
                </li>
                <li>
                    <a href="/report/">Отчёты</a>
                </li>
                <li>
                    <a href="/calendar/" class="parent">Календарь</a>
                    <ul>
                        <li><a href="/calendar/#calend">Календарь</a></li>
                        <li><a href="/calendar/#list">Список событий</a></li>
                    </ul>
                </li>
                <li>
                    <a href="/logout/">Выход</a>
                </li>
            </ul>
            </li>
            <li class="last"><span>Прочее</span>
                <ul>
                    <li><a href="/review/">Обзор</a></li>
                    <li><a href="/feedback/">Отзывы</a></li>
                    <li><a target="_blank" id="blog" href="http://easyfinance-ru.livejournal.com/">Блог</a></li>
                    <li><a href="/rules/">Правила использования</a></li>
                    <li><a href="/security/">Безопасность</a></li>
                    <li><a href="/about/">О компании</a></li>
                </ul>
            </li>
        </ul>
    </div>
<!--Теги-->
<div class="listing tags_list c3" style="display:none">
        <div class="title">
            <h2><a href="#" title="Добавить метку" class="addlink">Добавить метку</a></h2>
            <a href="#" title="Добавить метку" class="add">Добавить</a>
    </div>
    </div>
    <div class="edit_tag" style="display:none">
        <center>
            <div class="f_field">
                <label for="tag">Метка</label>
                <input type='text' value='' id='tag' name='tag' />
                <input type='hidden' value='' id='old_tag' name='old_tag' />
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
<!--/Теги-->
<!--счета-->
    <div class="listing accounts c2"style="display:none">
        <div class="title">
            <h2><a href="#" class="addaccountlink">Добавить счёт</a></h2>
            <a title="Добавить" class="add">Добавить</a>
    </div>
    <dl class="bill_list">
            <dt class="hidden">Деньги</dt>
            <dd id="accountsPanelAcc0" class="hidden"> <!-- --> </dd>
            <dt class="hidden">Мне должны</dt>
            <dd id="accountsPanelAcc1" class="hidden"> <!-- --> </dd>
            <dt class="hidden">Я должен</dt>
            <dd id="accountsPanelAcc2" class="hidden"> <!-- --> </dd>
            <dt class="hidden">Инвестиции</dt>
            <dd id="accountsPanelAcc3" class="hidden"> <!-- --> </dd>
            <dt class="hidden">Имущество</dt>
            <dd id="accountsPanelAcc4" class="hidden"> <!-- --> </dd>
            <dt>Сумма:</dt>
            <dd id="accountsPanel_amount" class="amount"> <!-- --> </dd>
        </dl>
    </div>
<!--/счета-->
<!--Финансовые цели-->
    <div class="listing financobject c5"style="display:none">
 <!-- -->
    </div>
<!--/Регулярные транзакции-->
    <div id="calendarLeft" class="listing transaction c4"style="display:none">
        <div class="overdue"></div>
        <div class="future"></div>
        &nbsp;&nbsp;&nbsp;&nbsp;<a href="/calendar/#list" id="AshowEvents">Журнал событий</a><br><br>
    </div>
</div>