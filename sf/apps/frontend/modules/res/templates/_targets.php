<?php
/**
 * Финцели
 *
 * @param array $data
 */
?>
<?php
$map = array(
    '1' => 'Квартира',
    '2' => 'Автомобиль',
    '3' => 'Отпуск',
    '4' => 'Финансовая подушка',
    '5' => 'Другое',
    '6' => 'Свадьба',
    '7' => 'Бытовая техника',
    '8' => 'Компьютер',
);

if (isset($targetCategories) && is_array($targetCategories)) {
    foreach ($targetCategories as & $targetCategory) {
        $targetCategory['title'] = $map[$targetCategory['category_id']];
        $targetCategory['cat_id'] = $targetCategory['category_id'];
    }
}
?>
res.popup_targets = <?php echo json_encode($targetCategories) ?>;
res.user_targets = <?php echo json_encode($userTargets) ?>;
