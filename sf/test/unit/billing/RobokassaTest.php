<?php
require_once dirname(__FILE__).'/../../bootstrap/all.php';


/**
 * Проверка хэша для робокассы
 */
class billing_RobokassaTest extends myUnitTestCase
{
    /**
     * Тест составления URL-а
     */
    public function testGetPaymentURL()
    {
        $url = Robokassa::getPaymentURL( 1, 2, 3 );
        
        $this->assertRegExp("/shpa=3/", $url);
        $this->assertRegExp("/OutSum=2/", $url);
        $this->assertRegExp("/InvId=1/", $url);
    }


    /**
     * Тест метода checkResult
     */
    public function testCheckResult()
    {
        $config = $this->_getConfig();
        $this->_setConfig();

        $invId = 1;
        $outSum = 2;
        $term = 3;

        $hash = md5( $outSum . ':' . $invId . ':' . $config['pass2'] . ':' . 'shpa=' . $term );
        $testResult = Robokassa::checkResult( $invId, $outSum, $term, $hash );

        $this->assertEquals($testResult, true);

        $testResult = Robokassa::checkResult($invId, $outSum, $term, 'wrong_hash');
        $this->assertEquals($testResult, false);

    }


    /**
     * Тест метода checkSuccessAndFailSignature
     */
    public function testCheckSuccessAndFailSignature()
    {
        $config = $this->_getConfig();
        $this->_setConfig();

        $invId = 1;
        $outSum = 2;
        $term = 3;

        $hash = md5( $outSum . ':' . $invId . ':' . $config['pass1'] . ':' . 'shpa=' . $term );
        $testResult = Robokassa::checkSuccessAndFailSignature( $invId, $outSum, $term, $hash );
        $this->assertEquals($testResult, true);

        $testResult = Robokassa::checkSuccessAndFailSignature( $invId, $outSum, $term, 'wrong hash');
        $this->assertEquals($testResult, false);
    }
    


    /**
     * Установить конфиг
     */
    private function _setConfig()
    {
        sfConfig::set('app_billing_robokassa', $this->_getConfig());
    }


    /**
     * Получить массив конфигурации робокассы
     */
    private function _getConfig()
    {
        return array(
            'url'       => 'prod_url',
            'test_url'  => 'test_url',
            'test'      => 1,
            'login'     => 'test',
            'pass1'     => 'password1',
            'pass2'     => 'password2'
        );
    }
}
