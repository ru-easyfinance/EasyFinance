<?php

/**
 * source_operations: Расширяем колонку source_operation_uid до 255 символов
 */
class Migration042_SourceOperations_ChangeColumn_SourceOperationUid extends Doctrine_Migration_Base
{
    public function up()
    {
        $this->changeColumn('source_operations', 'source_operation_uid', 'string', 255);
    }

    public function down()
    {
        $this->changeColumn('source_operations', 'source_operation_uid', 'string', 32);
    }
}

