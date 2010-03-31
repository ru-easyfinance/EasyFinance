<?php
require_once dirname(__FILE__).'/../../bootstrap/all.php';



/**
 * Список производителей
 */
class api_dataImportAmtTest extends myFunctionalTestCase
{
    protected $app = 'api';


    /**
     * Массив валидных данных для запроса
     *
     * @return array
     */
    private function _getValidData()
    {
        $user = $this->helper->makeUser();
        return array(
            'email'       => $user->getUserServiceMail(),
            'type'        => 0,
            'account'     => $this->helper->makeText('Номер счета', false),
            'timestamp'   => '2005-08-15T15:52:01+000',
            'amount'      => '1234.56',
            'description' => $this->helper->makeText(' Комментарий', false),
            'place'       => $this->helper->makeText('Место совершения операции', false),
            'balance'     => '23456.04',
        );
    }


    /**
     * Сделать запрос и проверить ответ
     *
     * @param  array  $data    - массив данных для запроса
     * @param  int    $code    - код ответа
     * @param  string $message - сообщение ответа
     */
    private function checkController(array $data, $code, $message)
    {
        $this->browser
            ->post($this->generateUrl('data_import_amt'), $data)
            ->with('response')->isStatusCode(200)
            ->with('request')->checkModuleAction('dataImportAmt', 'import')
            ->with('form')->begin()
                ->isInstanceOf('OperationImportAmtForm')
                ->hasErrors(1 != $code)
            ->end()
            ->with('response')->begin()
                ->checkElement(sprintf('result code:contains("%d")', $code))
                ->checkElement(sprintf('result message:contains("%s")', $message))
            ->end()
            ->with('model')->check('Operation', array(), (int)(1 == $code));
    }


    // Test
    // -------------------------------------------------------------------------


    /**
     * Только POST запрос
     */
    public function testPostRequestOnly()
    {
        $this->browser
            ->get($this->generateUrl('data_import_amt'))
            ->with('response')->isStatusCode(404);
    }


    /**
     * Успешный запрос
     */
    public function testOk()
    {
        $this->checkController($this->_getValidData(), $code = 1, $message = 'OK');
    }


    /**
     * Ошибки валидации
     */
    public function testValidationErrors()
    {
        $this->checkController(array(), $code = 3, $message = 'Required');
    }


    /**
     * Юзер не найден
     */
    public function testUserNotFound()
    {
        $data = $this->_getValidData();
        $data['email'] = 'some email';

        $this->checkController($data, $code = 2, $message = 'User not found');
    }

}
