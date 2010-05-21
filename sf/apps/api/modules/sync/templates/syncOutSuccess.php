<?php
/**
 * Список объектов
 *
 * @param string $model   - Название модели
 * @param array  $list    - Массив объектов
 * @param array  $columns - Список колонок, которые надо отобразить
 */
?>

<recordset type="<?php echo $model; ?>">
    <?php foreach ($list as $item): ?>
    <record id="<?php echo $item['id']; ?>">
        <?php foreach ($columns as $columnName): ?>
        <?php echo content_tag($columnName, $item[$columnName]); ?>
        <?php endforeach; ?>
    </record>
    <?php endforeach; ?>
</recordset>
