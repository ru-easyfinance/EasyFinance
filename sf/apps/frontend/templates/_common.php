<?php
/**
 * Подключение всплывающих панелей .. вроде того
 *
 * @see /views/common.html
 */
include_partial('global/common/accountEdit');
include_partial('global/common/guid');
include_partial('global/common/operationEdit');
include_partial('global/common/systemDialogs');

?>
<div class="ramka3" style="margin:7px 0;border: none;">
    <ul class="buttons_block">
        <li id="op_addoperation_but"><a title="Добавить операцию">Добавить операцию</a></li>
        <li id="op_addtocalendar_but"><a title="Добавить в календарь">Добавить в календарь</a></li>
    </ul>
</div>
