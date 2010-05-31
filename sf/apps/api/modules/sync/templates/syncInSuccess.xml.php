<resultset type="account">
    <?php foreach ($results as $result): ?>
    <record id="<?php
        echo $result['id'];
    ?>" cid="<?php
        echo $result['cid'];
    ?>" success="<?php echo $result['success'] ? 'true' : 'false' ?>" />
    <?php endforeach; ?>
</resultset>
