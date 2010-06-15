<?php
#Max: давай к шаблону добавим расширение json и будем здесь мапить свойства счетов
?>
res.accounts = <?php echo json_encode($data) ?>;
