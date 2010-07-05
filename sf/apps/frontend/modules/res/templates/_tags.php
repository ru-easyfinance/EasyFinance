<?php
/**
 * Вкладка тэгов
 *
 * @param array $data пользовательские теги
 */

$tags = array();
$cloud = array();

// мапим данные
foreach ($data as $tag) {
    $tags[] = $tag['name'];
    $cloud[] = array(
        'cnt'  => $tag['count'],
        'name' => $tag['name'],
    );
}
?>

res.tags = <?php echo json_encode($tags) ?>;
res.cloud = <?php echo json_encode($cloud) ?>;
