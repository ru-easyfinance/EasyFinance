<resultset type="account">
    <?php foreach ($results as $result): ?>
    <record id="<?php echo $result['id'] ?>" cid="<?php echo $result['cid'] ?>" success="<?php
        echo $result['success'] ? 'true' : 'false'
    ?>"<?php echo isset($result['message']) ? sprintf("><error>%s</error></record>\n", $result['message']) : " />\n" ?>
    <?php endforeach; ?>
</resultset>
