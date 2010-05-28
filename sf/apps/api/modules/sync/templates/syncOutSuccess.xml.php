<?php
/**
 * Список объектов
 *
 * @param string $model   - Название модели
 * @param array  $list    - Массив объектов
 * @param array  $columns - Список колонок, которые надо отобразить
 */
use_helper('Sync');

$table = Doctrine::getTable($model);
?>

<recordset type="<?php echo $model; ?>">
    <?php foreach ($list as $item): ?>
    <record id="<?php echo $item['id']; ?>"<?php if ($table->hasColumn('deleted_at') && $item['deleted_at']) { echo ' deleted="deleted"'; } ?>>

        <?php foreach ($columns as $columnName): ?>
        <?php
            $type = $table->getTypeOf($columnName);
            if ('date' == $type || 'timestamp' == $type || 'datetime' == $type) {
                echo content_tag($columnName, sync_date($item[$columnName]));
            } else {
                echo content_tag($columnName, $item[$columnName]);
            }
        ?>
        <?php endforeach; ?>
        <created_at><?php echo sync_date($item['created_at']); ?></created_at>
        <updated_at><?php echo sync_date($item['updated_at']); ?></updated_at>
    </record>
    <?php endforeach; ?>
</recordset>
