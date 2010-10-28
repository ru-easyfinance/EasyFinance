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
    'jsparams'      => '{dateFormat: "dd.mm.yy", changeMonth: true, changeYear: true, maxDate: "", minDate: ""}'
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
    'inputClass'    => 'js-control js-control-phonefield'
);

$email = array(
    'label' => 'Email',
    'name'  => 'email',
    'type'  => 'email'
);

?>

<div id="cititabs" class="b-citipage js-widget js-widget-citipage">
    <h2>Кредитная карта Cash Back для тех, кто рационально относится к своим расходам</h2>

    <p>Для тех, кто считает деньги и планирует свой бюджет, создана кредитная карта Ситибанка CASH BACK.</p>
    <div class="w-citipage-wrapper js-control js-control-tabs">
        <ul>
            <li><a href="#cititabs-1">Преимущества</a></li>
            <li><a href="#cititabs-2">Тарифы</a></li>
            <li><a href="#cititabs-3">Анкета</a></li>
        </ul>
        <div id="cititabs-1" class="b-citipage-tabs b-citipage-advantages">
            <!-- зюда фставлять ис одминге кусок про преимущества -->
            <p>Кредитная карта CASH BACK создана для тех, кто умеет считать деньги и планировать свой бюджет. Вы просто
            оплачиваете свои ежедневные покупки кредитной картой CASH BACK и получаете 1% от потраченной суммы
            обратно на Ваш счет. Погашая задолженность в срок, установленный в ежемесячной выписке, Вы не платите
            проценты – 0% на срок до 50 дней.</p>

            <p>Оформив кредитную карту Ситибанка CASH BACK Вы сможете автоматически вести учет операций по
            банковской карте с помощью системы EasyFinance.ru. Теперь у Вас не будет необходимости вручную вносить
            информацию по проведенным операциям, и Вы сможете более рационально планировать свой бюджет,
            используя средства банка.</p>

            <p>А благодаря напоминаниям от EasyFinance.ru Вы своевременно сможете погашать задолженность по
            кредитной карте и не платить проценты за пользование кредитными средствами банка.</p>
            <!-- каменты жжгут, патом удалить -->
        </div>
        <div id="cititabs-2" class="b-citipage-tabs b-citipage-tariffs">
            <!-- зюда фставлять ис одминге кусок про ториффы -->
            <table class="b-citipage-tariffs-table">
                <tbody>
                    <tr>
                        <td>Максимальный кредитный лимит</td>
                        <td>300 000 рублей</td>
                    </tr>
                    <tr>
                        <td>Выдача Кредитной карты</td>
                        <td>бесплатно</td>
                    </tr>
                </tbody>
            </table>
            <!-- каменты жжгут, патом удалить -->
        </div>
        <div id="cititabs-3" class="b-citipage-tabs b-citipage-form">
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
            </form>
        </div>
    </div>
</div>
