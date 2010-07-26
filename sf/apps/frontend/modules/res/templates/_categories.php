<?php
/**
 * Категории: res.category.*
 *
 * @param  array $system  системные
 * @param  array $users   пользовательские
 * @param  array $recent  частые
 */

// подготовить хардкод
$resCategory = array('system' => array(0 => array('id' => '0', 'name' => 'Не установлена',)));
?>

res.category = <?php echo json_encode($resCategory) ?>;

res.category.system = <?php echo json_encode($system) ?>;
res.category.user = <?php echo json_encode($user) ?>;
res.category.recent = <?php echo json_encode($recent) ?>;
