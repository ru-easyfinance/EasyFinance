<?php
$data = array(
    'city'         => 'Уфа',
    'employment'   => 'Работаю по постоянному контракту',
    'birthday'     => date('Y-m-d', strtotime('30 years ago')),
    'name'         => 'Василий',
    'patronymic'   => 'Иванович',
    'surname'      => 'Тёркин',
    'mobile_code'  => '123',
    'mobile_phone' => '1234567',
    'email'        => 'vasya@example.com',
);
$form = new CitiBankApplicationForm();
$form->bind($data);

function simpleOptions($arr)
{
    $result = array();
    foreach ($arr as $key => $val) {
        $result[] = '<option value="' . $val . '">' . $val . '</option>';
    }
    return implode($result, '');
}

$api_url = url_for("easybank_citi_cashback_apply");

$cityfield = array(
    'label'     => 'Город',
    'name'      => 'city',
    'id'        => 'city',
    'selectbox' => simpleOptions($form['city']->getWidget()->getChoices())
);

$employmentfield = array(
    'label'     => 'Форма трудоустройства',
    'name'      => 'employment',
    'id'        => 'employment',
    'selectbox' => simpleOptions($form['employment']->getWidget()->getChoices())
);

$date = array(
    'label'         => 'Дата рождения',
    'name'          => 'birthday',
    'hint'          => 'В формате ДД.ММ.ГГГГ',
    'inputClass'    => 'js-control js-control-datepicker',
    'jsparams'      => sprintf(
        '{
            dateFormat: "dd.mm.yy",
            changeMonth: true,
            changeYear: true,
            maxDate: new Date("%s"),
            minDate: new Date("%s"),
            yearRange: "%s:%s"
        }',
        date('r', strtotime('22 years ago')),
        date('r', strtotime('60 years ago')),
        date('Y', strtotime('60 years ago')),
        date('Y', strtotime('20 years ago'))
    )
);

$name = array(
    'label' => 'Имя',
    'name'  => 'name'
);

$patronymic = array(
    'label' => 'Отчество',
    'name'  => 'patronymic'
);

$surname = array(
    'label' => 'Фамилия',
    'name'  => 'surname'
);

$phone = array(
    'label'         => 'Мобильный телефон',
    'name'          => 'mobile_number',
    'hint'          => '+7 (xxx) yyyyyyy',
    'inputClass'    => 'js-control js-control-phonefield',
    'jsparams'      => '{forceRussia: true}'
);

$email = array(
    'label' => 'Email',
    'name'  => 'email',
    'type'  => 'text'
);

?>

<div id="cititabs" class="b-citipage js-widget js-widget-citipage">
    <h2>Кредитная карта Cash Back для тех, кто рационально относится к своим расходам</h2>
    <div class="w-citipage-wrapper js-control js-control-tabs">
        <ul>
            <li><a href="#cititabs-1">Преимущества</a></li>
            <li><a href="#cititabs-2">Анкета</a></li>
        </ul>
        <div id="cititabs-1" class="b-citipage-tabs b-citipage-advantages">
            <h3>
                <img src="/img/i/citi/card.png" alt="Кредитная карта CASH BACK" align="left" style="margin-right: .5em;"/>Кредитная карта Ситибанка CASH BACK<br/>
                Деньги возвращаются!
            </h3>
            <p style="clear: both;">Кредитная карта CASH BACK создана для тех, кто умеет считать деньги и планировать свой бюджет. Вы просто
                оплачиваете свои ежедневные покупки кредитной картой CASH BACK и получаете <strong>1% от потраченной суммы обратно
                на Ваш счет</strong><a href="#fn1" class="b-footnote">1</a>. Погашая задолженность в срок, установленный в ежемесячной выписке,
                Вы не платите проценты<a href="#fn2" class="b-footnote">2</a>.</p>
            <p>Оформив кредитную карту Ситибанка CASH BACK, Вы сможете автоматически вести учет операций по карте с
                помощью системы <strong>EasyFinance.ru</strong>. Теперь у Вас не будет необходимости вручную вносить информацию
                по проведенным операциям, и Вы сможете более рационально планировать свои расходы, используя средства
                банка.</p>
            <p>А благодаря напоминаниям от <strong>EasyFinance.ru</strong> Вы своевременно сможете погашать задолженность по кредитной карте
                и не платить проценты за пользование кредитными средствами банка.</p>

            <h4 style="text-align: center; font-size: 125%; margin-bottom: 1em;">Кредитная карта CASH BACK + EasyFinance.ru — это удобный
                способ оптимизировать Ваш бюджет!</h4>

            <h3>Оплачивая покупки кредитной картой CASH BACK, Вы зарабатываете деньги:</h3>
            <ul class="b-citipage-list">
                <li>1% от  суммы, потраченной по карте, возвращается обратно на Ваш счет<a href="#fn1" class="b-footnote">1</a></li>
            </ul>

            <div class="b-citipage-banner">
                <div class="b-banner-circletxt"><big>300</big><br/>рублей<br/>в подарок</div>
                <div class="b-banner-plaintxt">
                    <h6 class="b-banner-header" style="color: red;">300 рублей в подарок</h6>
                    <p class="b-banner-text">Совершите первую покупку по карте и получите
                        300 рублей<a href="#fn1" class="b-footnote">1</a> на счет в подарок от Ситибанка!</p>
                </div>
            </div>

            <h3>Другие преимущества</h3>
            <ul class="b-citipage-list">
                <li>До 300 000 рублей — возобновляемая кредитная линия</li>
                <li>0 % на срок до 50 дней — льготный период кредитования<a href="#fn2" class="b-footnote">2</a></li>
                <li>До 20 % — скидки в магазинах-партнерах Ситибанка<a href="#fn3" class="b-footnote">3</a></li>
                <li>До 3-х лет — оплата товаров и услуг в рассрочку<a href="#fn4" class="b-footnote">4</a></li>
            </ul>

            <h3>А также:</h3>
            <ul class="b-citipage-list">
                <li>Бесплатный интернет-банкинг<a href="#fn5" class="b-footnote">5</a> и мобильный банкинг<a href="#fn6" class="b-footnote">6</a></li>
                <li>Оплата мобильной связи, интернета и коммунальных услуг без комиссии</li>
                <li>Дополнительные карты для Ваших близких</li>
                <li>Безопасная оплата покупок и услуг в сети Интернет</li>
            </ul>
            <br/>
            <p><span class="pseudo js-toform">Заполните онлайн-заявку на оформление кредитной карты</span></p>

            <div class="b-citipage-footnotes">
                <ol class="b-citipage-noteslist">
                    <li><a name="fn1"></a>Кредитная карта Ситибанка CASH BACK выпускается только гражданам РФ.
                        Вознаграждение начисляется при совершении Клиентом любых операций по Счету за исключением операций
                        по снятию наличных денежных средств, операций по переводу денежных средств со Счета на счет
                        третьего лица в Ситибанке и в другом банке, операций по переводу денежных средств со Счета на свой
                        счет в Ситибанке (в том числе в рамках услуги «Универсальный перевод» и «Универсальный перевод плюс»)
                        и в другом банке, операций по оплате услуг Ситибанка и страховых компаний через Ситибанк. Полученное
                        вознаграждение подлежит налогообложению в порядке, предусмотренном законодательством РФ.</li>
                    <li><a name="fn2"></a>В случае непогашения задолженности в установленный срок проценты по кредиту
                        начисляются в полном объеме с момента возникновения задолженности, при этом условия Льготного периода
                        кредитования не распространяются на операции по снятию наличных, а также на операции по
                        программе «Заплати в рассрочку!»</li>
                    <li><a name="fn3"></a>Скидки предоставляются компаниями по их усмотрению и в предусмотренном ими порядке.
                        ЗАО КБ «Ситибанк», корпорация Citigroup Inc. и их аффилированные лица не несут никаких обязательств
                        по предложениям этих компаний, в частности, касающимся предоставления указанных скидок, или в связи
                        с ними. Информация о скидках приводится на основании данных, полученных от соответствующей компании.
                        Скидки не суммируются с другими скидками и специальными предложениями. Сроки действия предложений
                        ограничены.</li>
                    <li><a name="fn4"></a>Процентная ставка по кредиту в рамках программы «Заплати в рассрочку!» устанавливается
                        Ситибанком в размере 22-28 % годовых.</li>
                    <li><a name="fn5"></a>Система удаленного банковского обслуживания Citibank Online позволяет следить за
                        состоянием счета, совершать переводы, оплачивать услуги и покупки, а также коммунальные платежи.</li>
                    <li><a name="fn6"></a>Бесплатная мобильная версия Citibank Online, специально разработанная для доступа
                        к счетам через мобильный телефон, позволяет осуществлять основные операции по  счетам (следить за
                        состоянием счета, совершать переводы, оплачивать услуги и покупки, а также коммунальные платежи),
                        используя GPRS/EDGE, WIFI, 3G или другие Интернет-соединения, доступные на мобильном телефоне.</li>
                </ol>
                <p>Стоимость годового обслуживания кредитной карты Citibank MasterCard CASH BACK &mdash; 1 199 рублей.
                    Процентная ставка по кредиту составляет 29,9% годовых. Комиссия за снятие наличных через отделения/банкоматы
                    Ситибанка и других банков составляет 3,5% (но не менее 350 рублей). Комиссия за операции, совершенные
                    в иностранной валюте, составляет 2% от суммы операции в рублях. Комиссия взимается Ситибанком в момент
                    списания суммы операции с рублевого счета.</p>
                <p>Кредитная карта оформляется по усмотрению Ситибанка. Все условия кредитного договора опубликованы на
                    www.citibank.ru. ЗАО КБ «Ситибанк».</p>

            </div>
        </div>
        <div id="cititabs-2" class="b-citipage-tabs b-citipage-form">
            <h2>Заполните заявку на оформление кредитной карты прямо сейчас</h2>
            <p>В течение одного рабочего дня сотрудник Ситибанка свяжется с Вами по указанному в заявке телефону и
                договорится о встрече в любое удобное для Вас время и месте, чтобы помочь оформить заявление и собрать
                пакет необходимых документов.</p>
            <p>Вы можете подать оформить кредитную карту CASH BACK, если:</p>
            <ul class="b-citipage-list">
                <li>Вы являетесь гражданином РФ,</li>
                <li>Ваш возраст — от 22 до 60 лет,</li>
                <li>Ваш подтвержденный  ежемесячный доход после уплаты всех налогов составляет не менее 15 000 рублей,</li>
                <li>Вы живете и работаете в Москве или ближайшем Подмосковье, Санкт-Петербурге или Ленинградской области, Екатеринбурге, Самаре, Ростове-на-Дону, Уфе, Волгограде, Новосибирске, Казани, Нижнем Новгороде.</li>
            </ul>
            <br/>
            <p><strong style="color: #3366ff;">Необходимо заполнить все поля</strong></p>
            <form method="post" action="<?php echo $api_url ?>" class="b-form-skeleton">
                <div class="b-row">
                    <div class="b-col">
                        <div class="b-col-indent">
                            <?php include_partial('global/common/ui/textfield', $cityfield); ?>
                        </div>
                    </div>
                </div>
                <div class="b-row">
                    <div class="b-col">
                        <div class="b-col-indent">
                            <?php include_partial('global/common/ui/textfield', $employmentfield); ?>
                        </div>
                    </div>
                </div>
                <div class="b-row">
                    <div class="b-col">
                        <div class="b-col-indent">
                            <?php include_partial('global/common/ui/textfield', $date); ?>
                            <input type="hidden" name="birthday[day]"/>
                            <input type="hidden" name="birthday[month]"/>
                            <input type="hidden" name="birthday[year]"/>
                        </div>
                    </div>
                </div>
                <div class="b-row">
                    <div class="b-col">
                        <div class="b-col-indent">
                            <?php include_partial('global/common/ui/textfield', $name); ?>
                        </div>
                    </div>
                </div>
                <div class="b-row">
                    <div class="b-col">
                        <div class="b-col-indent">
                            <?php include_partial('global/common/ui/textfield', $patronymic); ?>
                        </div>
                    </div>
                </div>
                <div class="b-row">
                    <div class="b-col">
                        <div class="b-col-indent">
                            <?php include_partial('global/common/ui/textfield', $surname); ?>
                        </div>
                    </div>
                </div>
                <div class="b-row">
                    <div class="b-col">
                        <div class="b-col-indent">
                            <?php include_partial('global/common/ui/textfield', $phone); ?>
                            <input type="hidden" name="mobile_code"/>
                            <input type="hidden" name="mobile_phone"/>
                        </div>
                    </div>
                </div>
                <div class="b-row">
                    <div class="b-col">
                        <div class="b-col-indent">
                            <?php include_partial('global/common/ui/textfield', $email); ?>
                        </div>
                    </div>
                </div>
                <div class="b-row">
                    <div class="b-col">
                        <div class="b-col-indent">
                            <?php include_partial('global/common/ui/simplebutton', array()); ?>
                        </div>
                    </div>
                </div>
                <div class="b-citipage-notice js-form-notice hidden">
                    <p>
                        <big><span class="js-username"></span> <span class="js-userpatronymic"></span></big>,<br/>
                        спасибо за интерес, проявленный к Ситибанку. Специалист Ситибанка перезвонит вам
                        в течение одного рабочего дня по указанному телефону:
                        +7 (<span class="js-usermobile_code"></span>) <span class="js-usermobile_phone"></span>.
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>
