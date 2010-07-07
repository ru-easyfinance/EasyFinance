<?php
require_once dirname(__FILE__).'/../../bootstrap/all.php';

/**
 * Операции
 */
class task_importOperationFromAmtEmailTastTest extends myUnitTestCase
{
    private $_tmpFile;
    private $_cwd;


    /**
     * SetUp
     */
    protected function _start()
    {
        $this->_tmpFile = tempnam(sys_get_temp_dir(), __CLASS__);

        $this->_cwd = getcwd();
        chdir(sfConfig::get('sf_root_dir'));
    }


    /**
     * TearDown
     */
    protected function _end()
    {
        chdir($this->_cwd);
        unlink($this->_tmpFile);
    }


    /**
     * Запустить команду и проверить ответ
     *
     * @param  string $inputData       - Строка на вход скрипту
     * @param  int    $expectedCode    - Код ответа
     * @return void
     */
    public function checkCmd($inputData, $expectedCode)
    {
        file_put_contents($this->_tmpFile, $inputData);
        $task = new importOperationFromAmtEmailTask(new sfEventDispatcher, new sfFormatter);
        $code = $task->run(
            $args = array('file' => $this->_tmpFile),
            $options = array('env' => 'test')
        );

        $this->assertEquals($expectedCode, $code, "Expected exit code `{$expectedCode}`");
    }


    /**
     * Ошибка: пустой input
     */
    public function testErrorEmptyInput()
    {
        $this->checkCmd(null, $code = 1);
    }


    /**
     * Ошибка валидации формы
     */
    public function testErrorFormValidation()
    {
        $this->checkCmd('Some Data', $code = 2);
    }


    /**
     * Успешный вызов
     */
    public function testOk()
    {
        // Подготовить письмо
        $user = $this->helper->makeUser();
        $input = array(
            'id'          => $this->helper->makeText('ABC123', false), // уникальный id в пределах источника
            'email'       => $user->getUserServiceMail(),
            'type'        => Operation::TYPE_PROFIT,
            'account'     => $this->helper->makeText('Номер счета', false),
            'timestamp'   => '2005-08-15T15:52:01+000',
            'amount'      => '1234.56',
            'payment'     => '231 234.34 RUR',
            'description' => $this->helper->makeText(' Комментарий', false),
            'place'       => $this->helper->makeText('Место совершения операции', false),
            'balance'     => '23456.04',
        );

        $date = new DateTime($input['timestamp']);
        $date->setTimezone(new DateTimeZone(date_default_timezone_get()));

        // Письмо
        $email = new myCreateEmailAmtImport($input);
        $email->useAddPart();

        // Импорт
        $this->checkCmd((string)$email, $code = 0);

        // Залезть в БД и проверть операцию
        $expected = array(
            'user_id'   => $user->getId(),
            'money'     => abs((float) $input['amount']),
            'date'      => $date->format('Y-m-d'),
            'type'      => Operation::TYPE_PROFIT,
            'source_id' => Operation::SOURCE_AMT,
            'accepted'  => Operation::STATUS_DRAFT,
        );
        $this->assertEquals(1, $this->queryFind('Operation', $expected)->count(), 'Expected found 1 object');
    }

}
