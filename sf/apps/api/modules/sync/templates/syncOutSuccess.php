<?php
/**
 * Список объектов
 *
 * @param string $model   - Название модели
 * @param array  $list    - Массив объектов
 * @param array  $columns - Список колонок, которые надо отобразить
 */

$table = Doctrine::getTable($model);
?>

<recordset type="<?php echo $model; ?>">
    <?php foreach ($list as $item): ?>
    <record id="<?php echo $item['id']; ?>">
        <?php foreach ($columns as $columnName): ?>
        <?php
            $type = $table->getTypeOf($columnName);
            if ('date' == $type || 'timestamp' == $type || 'datetime' == $type) {
                $date = new DateTime($item[$columnName]);
                echo content_tag($columnName, $date->format(DATE_ISO8601));
            } else {
                echo content_tag($columnName, $item[$columnName]);
            }
        ?>
        <?php endforeach; ?>
    </record>
    <?php endforeach; ?>
</recordset>
