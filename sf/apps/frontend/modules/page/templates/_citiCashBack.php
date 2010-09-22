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
    'email'        => 'vasya@nail.ru',
);
$form = new CitiBankApplicationForm();
$form->bind($data);
?>
<div style="padding: 20px; max-width: 940px; margin: 0 auto 0 auto;">
<form id="citiBankApplicationForm" method="post" action="/my/dev.php/easybank/citi-cashback-apply">
<table>
    <tbody>
    <?php foreach ($form as $name => $field) : ?>
    <?php if (!in_array($name, array('mobile_code', 'mobile_phone'))) : ?>
        <tr>
            <td>
                <?php echo $form[$name]->renderLabel(); ?>:
                <?php if ($form->getValidator($name)
                    ->getOption('required')) : ?>*<?php endif; ?>
            </td>
            <td><?php echo $form[$name]; ?></td>
        </tr>
    <?php elseif ($name == 'mobile_code') : ?>
        <tr>
            <td>Мобильный телефон: *</td>
            <td>
                Код <?php echo $form['mobile_code']
                    ->render(array('size' => 3)); ?>
                Номер <?php echo $form['mobile_phone']
                    ->render(array('size' => 7)); ?>
            </td>
        </tr>
    <?php endif;?>
    <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <td>
                <input type="submit" value="Отправить" id="btnPrintForm" />
            </td>
        </tr>
    </tfoot>
</table>
</form>
</div>
<script type="text/javascript">
$(document).ready(function() {
    prepareBlankAction = function() {
        // запоминаем событие в Google Analytics
        try { _gaq.push(['_trackEvent', 'Анкета', 'Заполнена', 'Citi CashBack']); } catch(err) {};
        var data = wzGetFormData($('citiBankApplicationForm'));

        return {
            'data': data,
            'idleMessage': 'Отправляем анкету в банк ...'
        };
    };

    $('#btnPrintForm').ajaxInitiator
    (
        $.ajaxInitiator.requestType.post, // request type
        '/my/easybank/citi-cashback-apply', // url
        'json', // data type
        { // notification params
            'animationPosition': $.actionInitiator.animationPosition.right,
            'align': $.actionInitiator.align.left,
            'notificationPlace': $.actionInitiator.notificationPlace.nearTheInitiator,
            'notificationLifetime': 7000,
            'notificationTextNode': $('#finish div.notification-text-node'),
        },
        {
            'prepareData': prepareBlankAction,
            'processSuccess': function (data) {
                return 'Анкета успешно отправлена в банк';
            },
            'processError': function (data) {
                // передача обработки ошибки инициатору
            }
        }
    );

    function wzGetFormData(frm) {
        var i =0;
        var j = 0;
        var data = {"length": 0};

        if (frm.elements && (typeof(frm.elements) != 'undefined') && (frm.elements.length > 0)) {
            for (i = 0; i < frm.elements.length; i++) {
                if (frm.elements[i].name) {
                    if ((frm.elements[i].type == 'checkbox') || (frm.elements[i].type == 'radio')) {
                        if (frm.elements[i].checked) {
                            data[frm.elements[i].name] = frm.elements[i].value;
                            j++;
                        }
                    } else {
                        data[frm.elements[i].name] = frm.elements[i].value;
                        j++;
                    }
                }
            }

            data.length = j;
        }

        return data;
    }
});
</script>