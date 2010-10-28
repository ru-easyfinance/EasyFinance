<?php
require_once dirname(__FILE__).'/../../bootstrap/all.php';

/**
 * CsvImport
 */
class util_myCsvImportVkoshelkeTest extends myUnitTestCase
{
    /**
     * Тестовые данные
     */
    private $_csvData = 'ReceiptDate,TransactionType,Account,Value,Currency,Place,Category,Comment,Aim
08.06.2010 0:00:00,Расход,Конверт Br,"20000,00",BYR,,Сотовый,Тане,
07.06.2010 0:00:00,Расход,Конверт Br,"83400,00",BYR,,Бензин,"",
07.06.2010 0:00:00,Расход,Конверт Br,"34440,00",BYR,,Остальные продукты,"",
06.06.2010 0:00:00,Перевод зачисление,Конверт Br,"300000,00",BYR,,,"",
06.06.2010 0:00:00,Перевод списание,Конверт $,"100,00",USD,,,"",
06.06.2010 0:00:00,Расход,Конверт Br,"81670,00",BYR,,Одежда и обувь,Саше 3 майки,';

    /**
     * @var User
     */
    private $_user;

    /**
     * @var myTestObjectHelper
     */
    protected $helper;

    /**
     * (non-PHPdoc)
     * @see sfPHPUnitTestCase::setUp()
     */
    public function setUp()
    {
        parent::setUp();
        $this->_user = $this->helper->makeUser();
    }

    /**
     * Проверяем что из csv получился yaml
     */
    public function testExecuteProducesYaml()
    {
        $csvImport  = new myCsvImportVkoshelke($this->_csvData);
        $yamlFile   = $csvImport->execute($this->_user);
        $this->assertNotEquals(false, $yamlFile);

        $yaml       = file_get_contents($yamlFile);
        $yamlParser = new sfYamlParser();
        $data       = $yamlParser->parse($yaml);

        $this->assertEquals(
            array (
                'user_id'  => $this->_user->getId(),
                'date'     => '2010-06-08',
                'amount'   => '20000.00',
                'comment'  => 'Тане',
                'type'     => 0,
                'Account'  => 'Account_1',
                'Category' => 'Category_1',
            ),
            $data['Operation']['Operation_1']
        );

        $this->assertEquals(
            array (
                'user_id'         => $this->_user->getId(),
                'date'            => '2010-06-06',
                'amount'          => '100.00',
                'comment'         => '',
                'type'            => 2,
                'Account'         => 'Account_2',
                'transfer_amount' => '300000.00',
                'TransferAccount' => 'Account_1',
            ),
            $data['Operation']['Operation_4']
        );
    }

    /**
     * Проверим что полученный yaml загружается в БД
     * @see Doctrine_Core::loadData()
     */
    public function testLoadData()
    {
        $csvImport  = new myCsvImportVkoshelke($this->_csvData);
        $yamlFile   = $csvImport->execute($this->_user);
        $this->assertNotEquals(false, $yamlFile);

        $yaml       = file_get_contents($yamlFile);
        $yamlParser = new sfYamlParser();
        $data       = $yamlParser->parse($yaml);

        Doctrine_Core::loadData($yamlFile, true);
        $transfer = Doctrine::getTable('Operation')
            ->findOneByTransferAmount('300000.00', Doctrine::HYDRATE_ARRAY);

        $this->assertNotEmpty($transfer, 'Перевод должен попасть в БД');
        $this->assertEquals(
            Operation::TYPE_TRANSFER,
            $transfer['type'],
            'Тип операции перевода должен быть ' . Operation::TYPE_TRANSFER
        );
        $this->assertEquals(
            '2010-06-06',
            $transfer['date'],
            'Дата операции должна совпадать с датой в CSV'
        );
    }


    /**
     * Проверяем обработку BOM
     */
    public function testBomRaplacing()
    {
        // Маленькая подлость от MS Excel - BOM вначале файла
        $csv        = pack('H*', "EFBBBF") . $this->_csvData;
        $csvImport  = new myCsvImportVkoshelke($csv);
        $yamlFile   = $csvImport->execute($this->_user);
        $this->assertNotEquals(
            false,
            $yamlFile,
            'Импорт должен работать не смотря на BOM'
        );
    }

    /**
     * Проверяем реакцию на неправильный формат
     */
    public function testWrongCsvFormat()
    {
        $csv        = "FirstName,LastName\n";
        $csv       .= "Крошка,Енот";
        $csvImport  = new myCsvImportVkoshelke($csv);
        $yamlFile   = $csvImport->execute($this->_user);
        $this->assertEquals(
            false,
            $yamlFile,
            'Формат CSV не тот - надо возвращать false'
        );
    }
}
