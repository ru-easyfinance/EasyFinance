<?php
require_once dirname(__FILE__).'/../../bootstrap/all.php';


/**
 * Операции
 */
class task_ImportOperationFromAmtEmailTastTest extends myUnitTestCase
{
    /**
     * Запустить команду и проверить ответ
     *
     * @param  string $pipeData        - Строка на вход скрипту
     * @param  string $expectedOputput - Текст на выходе
     * @param  int    $expectedCode    - Код ответа
     * @return void
     */
    public function checkCmd($pipeData, $expectedOputput, $expectedCode)
    {
        $cmd = sfConfig::get('sf_root_dir') . '/symfony api:import-amt-email';
        exec("echo '{$pipeData}' | {$cmd};", $result, $code);

        $this->assertSame($expectedCode, $code, "Expected exit code `{$expectedCode}`");
        $this->assertContains($expectedOputput, implode(PHP_EOL, $result), 'Output');
    }


    /**
     * Инициализация таска
     */
    public function testTaskInitialize()
    {
        $task = new apiImportOperationFromAmtEmailTask(new sfEventDispatcher, new sfFormatter);
        $this->assertEquals('api', $task->getNamespace());
        $this->assertEquals('import-amt-email', $task->getName());
    }


    /**
     * Ошибка: пустой STDIN
     */
    public function testErrorStdinEmpty()
    {
        $this->checkCmd($input = null, $out = 'Expected STDIN data', $code = 1);
    }


    /**
     * Ошибка валидации формы
     */
    public function testErrorFormValidation()
    {
        $this->checkCmd($input = 'Some data', $out = 'Required', $code = 2);
    }


    /**
     * Успешный вызов
     */
    public function testOk()
    {
        $this->markTestIncomplete();

        // Подготовить письмо
        $user = $this->helper->makeUser();
        $input = array(
            'email'       => $user->getUserServiceMail(),
            'type'        => Operation::TYPE_PROFIT,
            'account'     => $this->helper->makeText('Номер счета', false),
            'timestamp'   => '2005-08-15T15:52:01+000',
            'amount'      => '1234.56',
            'description' => $this->helper->makeText(' Комментарий', false),
            'place'       => $this->helper->makeText('Место совершения операции', false),
            'balance'     => '23456.04',
        );
        $date = new DateTime($input['timestamp']);
        $date->setTimezone(new DateTimeZone(date_default_timezone_get()));
        // Письмо
        $email = 1;


        // Импорт
        $this->checkCmd($email, $out = 'Done', $code = 0);

        // Залезть в БД и проверть операцию
        $expected = array(
            'user_id'   => $user->getId(),
            'money'     => abs((float) $input['amount']),
            'date'      => $date->format('Y-m-d'),
            'time'      => $date->format('H:i:s'),
            'drain'     => Operation::TYPE_PROFIT,
            'type'      => Operation::TYPE_PROFIT,
            'source_id' => Operation::SOURCE_AMT,
            'accepted'  => Operation::STATUS_DRAFT,
        );
        $this->assertEquals(1, $this->queryFind('Operation', $expected)->count(), 'Expected found 1 object');
    }

}
