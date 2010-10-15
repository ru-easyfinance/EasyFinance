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
    <div class="w-citipage-wrapper js-control js-control-tabs">
        <ul>
            <li><a href="#cititabs-1">Преимущества</a></li>
            <li><a href="#cititabs-2">Тарифы</a></li>
            <li><a href="#cititabs-3">Анкета</a></li>
        </ul>
        <div id="cititabs-1">
            <!-- зюда фставлять ис одминге кусок про преимущества -->
            <!-- каменты жжгут, патом удалить -->
        </div>
        <div id="cititabs-2">
            <!-- зюда фставлять ис одминге кусок про ториффы -->
            <!-- каменты жжгут, патом удалить -->
        </div>
        <div id="cititabs-3" style="width: 400px">
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
