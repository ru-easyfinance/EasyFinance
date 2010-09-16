<?php
/**
 * Сборка объекта res из компонентов
 */


    $res = array();

    // пользователь
    $res['user'] = array('name' => $sf_user->getName());

    # Svel: А что это за хардкод? )
    # TODO: если он так и должен тут быть - мб его не парсить через php?
    #       или оно не должно быть хардкодом?
    #       "не аккуратненько" (c) :-)
    $res['targets_category'] = array(
        '1' => 'Квартира',
        '2' => 'Автомобиль',
        '3' => 'Отпуск',
        '4' => 'Финансовая подушка',
        '6' => 'Свадьба',
        '7' => 'Бытовая техника',
        '8' => 'Компьютер',
        '5' => 'Прочее'
    );

    // части, которые еще не написаны
    // но без дефолтных данных все падает :/
    $res['profile'] = array(
        'integration' => array(
            'email'   => '',
            'account' => '',
        ),
    );

    $res['informers'] = array(
        0 => array(
            'title'       => '',
            'description' => '',
            'value'       => 0,
        ),
        1 => array(
            'title'       => '',
            'description' => '',
            'value'       => 0,
        ),
        2 => array(
            'title'       => '',
            'description' => '',
            'value'       => 0,
        ),
        3 => array(
            'title'       => '',
            'description' => '',
            'value'       => 0,
        ),
        4 => array(
            'title'       => '',
            'description' => '',
            'value'       => 0,
        ),
    );

    $res['errors'] = array();
?>

<script type="text/javascript">
    var res = <?php echo json_encode($res), "\n"; ?>
    res.calendar = {};
<?php if ($sf_user->isAuthenticated()) : ?>
    <?php include_component('res', 'accounts', array()) ?>
    <?php include_component('res', 'currencies', array()) ?>
    <?php include_component('res', 'categories', array()) ?>
    <?php include_component('res', 'tags', array()) ?>
    <?php include_component('res', 'calendar', array('dateStart' => null, 'dateEnd' => null)) ?>
    <?php include_component('res', 'future', array()) ?>
    <?php include_component('res', 'overdue', array()) ?>
<?php endif; ?>
</script>