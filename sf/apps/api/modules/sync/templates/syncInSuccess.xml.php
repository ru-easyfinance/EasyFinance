<?php
/**
 * #Max:
    - не забывай про phpdoc для шаблонов
    - может сделаем формат:
    <record id="123" cid="456" success="true">OK</record>
    <record id="123" cid="456" success="false">Error message</record>
    Или вообще назовем не record, если не кошерно
 */
?>
<resultset type="account">
    <?php foreach ($results as $result): ?>
    <record id="<?php echo $result['id'] ?>" cid="<?php echo $result['cid'] ?>" success="<?php
        echo $result['success'] ? 'true' : 'false'
    ?>"<?php echo isset($result['message']) ? sprintf("><error>%s</error></record>\n", $result['message']) : " />\n" ?>
    <?php endforeach; ?>
</resultset>
