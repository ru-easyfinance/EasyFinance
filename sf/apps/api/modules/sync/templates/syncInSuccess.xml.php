<?php
/**
 * Список результатов синхронизации
 *
 * @param  array $results    Массив результатов обработки
 */
?>
<?php printf('<resultset type="%s">', $type); ?>
    <?php foreach ($results as $result): ?>
        <?php
            echo sprintf(
                '<record id="%s" cid="%s" success="%s">%s</record>',
                $result['id'],
                $result['cid'],
                ($result['success'] ? 'true' : 'false'),
                (isset($result['message']) ? $result['message'] : 'OK')
            );
        ?>
    <?php endforeach; ?>
</resultset>
