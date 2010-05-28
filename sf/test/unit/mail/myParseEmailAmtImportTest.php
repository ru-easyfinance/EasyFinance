<?php
require_once dirname(__FILE__).'/../../bootstrap/all.php';

/**
 * Test class for myParseEmailAmtImport.
 */
class mail_myParseEmailAmtImportTest extends myUnitTestCase
{
    /**
     * Получить массив с данными для создания письма
     *
     * @return array
     */
    private function _getData()
    {
        return array(
            'id'          => '243f 2k1 - k13lkhgv',
            'email'       => 'vasisualiy.pupkin@mail.easyfinance.ru',
            'type'        => '0',
            'account'     => '4123',
            'timestamp'   => '2005-08-15T15:52:01+0000',
            'amount'      => '5000.00',
            'payment'     => '2 500.00 USD',
            'description' => 'описание операции (Снятие наличных/Платеж)',
            'place'       => 'KHAZAKHSTAN OSTANA BGG',
            'balance'     => '100000.20'
        );
    }


    /**
     * Получить данные AMT из текста письма
     */
    public function testGetAmtDataFromMailPart()
    {
        $email = new myCreateEmailAmtImport($data = $this->_getData());
        $email->useAddPart();
        $getEmail = new myParseEmailAmtImport((string)$email);
        $this->assertEquals($data, $getEmail->getAmtData());
    }


    /**
     * Поле payment отсутствует
     */
    public function testNoPaymentField()
    {
        $data = $this->_getData();
        unset($data['payment']);
        $email = new myCreateEmailAmtImport($data);

        $email->useAddPart();
        $getEmail = new myParseEmailAmtImport((string)$email);
        $data['payment'] = '';
        $this->assertEquals($data, $getEmail->getAmtData());
    }


    /**
     * Получить данные AMT из вложения
     */
    public function testGetAmtDataFromAttachment()
    {
        $email = new myCreateEmailAmtImport($data = $this->_getData());
        $email->useAttachment();

        $getEmail = new myParseEmailAmtImport((string)$email);
        $this->assertEquals($data, $getEmail->getAmtData());
    }


    /**
     * Исключение, если данные не найдены
     */
    public function testExpcetionIfDataNotFound()
    {
        $email = new myCreateEmailAmtImport($data = $this->_getData());
        $getEmail = new myParseEmailAmtImport((string)$email);

        $this->setExpectedException('Exception', 'not found');
        $getEmail->getAmtData();
    }

}
