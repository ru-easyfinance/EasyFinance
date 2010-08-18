<?php
/**
 * Категории: res.category.*
 *
 * @param  array $system  системные
 * @param  array $users   пользовательские
 * @param  array $recent  частые
 */

// инициализация
?>
res.category = {};
<?php
// мапим данные
$systemCategories = array();
foreach ($system as $category) {
    $systemCategories[$category['id']] = array(
        'id'   => $category['id'],
        'name' => $category['name'],
    );
}

$systemCategories = array_merge($systemCategories, array(
    '0' => array(
        'id' => '0',
        'name' => 'Не установлена',
    )
));

$userCategories = array();
foreach ($users as $category) {
    $userCategories[$category['id']] = array(
        'id'      => $category['id'],
        'name'    => $category['name'],
        'parent'  => $category['parent_id'],
        'system'  => $category['system_id'],
        'type'    => $category['type'],
        'visible' => 1, # Svel: всегда видимо, см. старый код
        'custom'  => $category['custom'],
    );
}

// часто используемые категории
$recent = array();
$countMax      = sfConfig::get('app_categories_recent_limit', 10);
$operationsMin = sfConfig::get('app_categories_recent_operationsFor', 3);

foreach ($users as $category) {
    if ($category['count'] >= $operationsMin) {
        $recent[$category['count']] = $category['id'];
    }
}
krsort($recent);

if (count($recent) > $countMax) {
    $recent = array_slice($recent, 0, $countMax, true);
}

// заполняем
?>
res.category.system = <?php echo json_encode($systemCategories) ?>;
res.category.user = <?php echo json_encode($userCategories) ?>;
res.category.recent = <?php echo json_encode($recent) ?>;
