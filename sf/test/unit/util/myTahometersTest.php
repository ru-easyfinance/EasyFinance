<?php
require_once dirname(__FILE__).'/../../bootstrap/all.php';

/**
 * Тахометры
 */
class myTahometersTest extends myUnitTestCase
{
    /**
     * Считать сумму с учетом коэффициента стажа для экстраполяции значений
     * через рефлексию, т.к. метод protected и данные подсунуть можно )
     */
    public function testMakeMoneyAccuracyByExpirience()
    {
        $user = $this->getMock('User', array());
        $tahometers = new myTahometers($user);

        $class = new ReflectionClass('myTahometers');
        $method = $class->getMethod('makeMoneyAccuracyByExpirience');
        $method->setAccessible(true);
        $exp = $class->getProperty('userExpirience');
        $exp->setAccessible(true);

        $exp->setValue($tahometers, $userExpirienceDays = 6);

        $resultCurrentMonths = $method->invokeArgs($tahometers, array($amount = 99.50, $months = 0));
        $resultThreeMonths = $method->invokeArgs($tahometers, array($amount, $months = 3));
        $expectedhreeMonthsAmount = (strtotime(date('Y-m-d')) - strtotime(date('Y-m-d') . " - " . $months . " month"))
                        / 86400 / $userExpirienceDays * $amount;
        $resultNullMonths = $method->invokeArgs($tahometers, array($amount, $months = null));

        $this->assertEquals($amount, $resultCurrentMonths, 'Значение суммы не поменялось', 0.01);
        $this->assertEquals($expectedhreeMonthsAmount, $resultThreeMonths, 'Экстраполированное значение за 3 месяца', 0.01);
        $this->assertEquals($amount, $resultNullMonths, 'Значение без среза не меняется', 0.01);
    }

}
