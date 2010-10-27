<?php
require_once dirname(__FILE__).'/../../bootstrap/all.php';

/**
 * CsvImport
 */
class util_myCsvImportVkoshelkeTest extends myUnitTestCase
{
    /**
     * Проверяем пока единственный метод
     */
    function testExecute()
    {
        $csv  = 'ReceiptDate,TransactionType,Account,Value,Currency,Place,Category,Comment,Aim
08.06.2010 0:00:00,Расход,Конверт Br,"20000,00",BYR,,Сотовый,Тане,
07.06.2010 0:00:00,Расход,Конверт Br,"83400,00",BYR,,Бензин,"",
07.06.2010 0:00:00,Расход,Конверт Br,"34440,00",BYR,,Остальные продукты,"",
06.06.2010 0:00:00,Перевод зачисление,Конверт Br,"300000,00",BYR,,,"",
06.06.2010 0:00:00,Перевод списание,Конверт $,"100,00",USD,,,"",
06.06.2010 0:00:00,Расход,Конверт Br,"81670,00",BYR,,Одежда и обувь,Саше 3 майки,';

        $csvImport = new myCsvImportVkoshelke($csv);
        $yamlFile  = $csvImport->execute();
        $this->assertNotEquals(false, $yamlFile);
        $this->markTestIncomplete('Доделать тест');
    }
}
