<?php
require_once dirname(__FILE__).'/../../bootstrap/all.php';

/**
 * Операции
 */
class task_importOperationFromEmailTastTest extends myUnitTestCase
{
    private $_tmpFile;
    private $_cwd;

    private $_account      = '1234';
    private $_amount       = '1200.00';
    private $_email        = 'vasisualiy.pupkin@mail.easyfinance.ru';
    private $_type         = '0';
    private $_from         = 'test@testbank.ru';
    private $_subject      = 'описание операции (Снятие наличных/Платеж)';
    private $_id           = '123456';
    private $_source       = 'test';
    private $_description  = "test description";

    private $_operationId;

    /**
     * SetUp
     */
    protected function _start()
    {
        $this->_tmpFile = tempnam(sys_get_temp_dir(), __CLASS__);

        $this->_cwd = getcwd();
        chdir(sfConfig::get('sf_root_dir'));

        $this->_operationId = time();
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
        $task = new importOperationFromEmailTask(new sfEventDispatcher, new sfFormatter);
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
        $this->checkCmd("", $code = importOperationFromEmailTask::ERROR_EMPTY_INPUT );
    }


    /**
     * Ошибка валидации формы
     */
    public function testErrorFormValidation()
    {
        $this->checkCmd('Some Data', $code = importOperationFromEmailTask::ERROR_EMAIL_FORMAT );
    }


    /**
     * Успешный вызов
     */
    public function testOk()
    {
        // Подготовить письмо
        $user = $this->helper->makeUser();
        $this->_email = $user->getUserServiceMail();

        // Создаем отправителя и парсер
        $parser = $this->_createSourceAndParser();

        // Письмо
        $email = new myCreateEmailImport($this->_getEmailData());

        // Импорт
        $this->checkCmd((string)$email, $code = 0);

        // Залезть в БД и проверть операцию
        $expected = array(
            'user_id'   => $user->getId(),
            'amount'     => abs((float) $this->_amount),
            'type'      => Operation::TYPE_EXPENSE,
            'accepted'  => Operation::STATUS_DRAFT,
        );

        $this->assertEquals(1, $this->queryFind('Operation', $expected)->count(), 'Expected found 1 object');
    }

    /**
     * Успешный вызов с расширенным email-ом
     */
    public function testOkWithLongEmail()
    {
        // Подготовить письмо
        $user = $this->helper->makeUser();
        $this->_email = $user->getUserServiceMail();

        // Создаем отправителя и парсер
        $parser = $this->_createSourceAndParser();

        // Письмо
        $emailData = $this->_getEmailData();
        $emailData['from'] = array('test@testbank.ru' => "Проверочный отправитель" );
        $email = new myCreateEmailImport( $emailData );

        // Импорт
        $this->checkCmd((string)$email, $code = 0);

        // Залезть в БД и проверть операцию
        $expected = array(
            'user_id'   => $user->getId(),
            'amount'     => abs((float) $this->_amount),
            'type'      => Operation::TYPE_EXPENSE,
            'accepted'  => Operation::STATUS_DRAFT,
        );

        $this->assertEquals(1, $this->queryFind('Operation', $expected)->count(), 'Expected found 1 object');
    }


    /**
     * Проверка, что письма от AMT проходят по своему алгоритму
     */
    public function testAmt()
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


    /**
     * Получить массив с данными для создания письма
     *
     * @return array
     */
    private function _getEmailData()
    {
        return array(
            'email'       => $this->_email,
            'from'        => $this->_from,
            'subject'     => $this->_subject,
            'body'        => "Номер карты: {$this->_account},\n списано средств: {$this->_amount} RUB,\n Описание: {$this->_description}\n\n"
        );
    }


    /**
     * Получить массив с данными для сравнения с результатами парсинга
     *
     * @return array
     */
    private function _getParseData()
    {
        return array(
            'email'       => $this->_email,
            'amount'      => $this->_amount,
            'description' => $this->_description,
            'type'        => $this->_type,
            'account'     => $this->_account,
            'source'      => $this->_source . $this->_account,
            'id'          => $this->_operationId,
        );
    }


    /**
     * Создать отправителя и парсер
     *
     * @return EmailParser
     */
    private function _createSourceAndParser()
    {
        // Создаем отправителя
        $source = new EmailSource();
        $source->setName($this->_source);
        $source->setEmailList("anytest@test.tst, " . $this->_from);
        $source->save();
        $sourceId = $source->getId();

        // Создаем парсер
        $parser = new EmailParser();
        $parser->setEmailSourceId( $sourceId );
        $parser->setName( $this->_subject );
        $parser->setSubjectRegexp( "описание операции \\(Снятие наличных\\/Платеж\\)" ); // MySQL regexp!
        $parser->setAccountRegexp("Номер карты: (\\d\\d\\d\\d)");
        $parser->setTotalRegexp("списано средств: ([\\d\\.,]+) ");
        $parser->setDescriptionRegexp("Описание: (.+)");
        $parser->setType( $this->_type );
        $parser->save();

        return $parser;
    }

}
