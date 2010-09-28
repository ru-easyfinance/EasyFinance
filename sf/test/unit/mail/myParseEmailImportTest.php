<?php
require_once dirname(__FILE__).'/../../bootstrap/all.php';

/**
 * Test class for myParseEmailImport.
 */
class mail_myParseEmailImportTest extends myUnitTestCase
{
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
        $this->_operationId = time();
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
            'id'          => $this->_operationId
        );
    }

    /**
     * Получить данные из текста письма
     */
    public function testGetDataFromMailPart()
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
        $parser->setSubjectRegexp( "описание операции \(Снятие наличных\/Платеж\)" );
        $parser->setAccountRegexp("Номер карты: (\\d\\d\\d\\d)");
        $parser->setTotalRegexp("списано средств: ([\\d\\.,]+) ");
        $parser->setDescriptionRegexp("Описание: (.+)");
        $parser->setType( $this->_type );
        $parser->save();

        $email = new myCreateEmailImport($this->_getEmailData());
        $email->addPart('qwerty');
        $mailArray = myParseEmailImport::getEmailData((string)$email);
        $getEmail = new myParseEmailImport($mailArray['body'], $parser, $mailArray['to']);
        $this->assertEquals($this->_getParseData(), $getEmail->getData( $this->_operationId ));

        // Проверим, работает ли регексп чистки суммы
        $this->_amount = '123,-400x.a00';
        $parser->setTotalRegexp("списано средств: ([\\d\\.,a-zA-Z\\-]+) ");
        $parser->save();
        $email = new myCreateEmailImport($this->_getEmailData());
        $mailArray = myParseEmailImport::getEmailData((string)$email);
        $getEmail = new myParseEmailImport($mailArray['body'], $parser, $mailArray['to']);
        $data = $getEmail->getData( $this->_operationId );
        $this->assertEquals('123400.00', $data['amount']);
    }
}
