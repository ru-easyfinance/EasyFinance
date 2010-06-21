<?php
require_once dirname(__FILE__).'/../../bootstrap/all.php';


/**
 * Транзакции биллинга
 */
class model_BillingTransactionTest extends myUnitTestCase
{
    /**
     * Отношения
     */
    public function testRelations()
    {
        $bt = new BillingTransaction;

        // Пользователь
        $this->assertType('User', $bt->User);

        // Услуга
        $this->assertType('Service', $bt->Service);

        // Подписка на услугу
        $this->assertType('ServiceSubscription', $bt->ServiceSubscription);
    }


    /**
     * Тест каскадных удалений
     *
     */
    public function testCascade()
    {
        $bt = new BillingTransaction;

        // Создаем пользователя, услугу и транзакцию
        $user = $this->helper->makeUser();
        $user->save();

        $service = new Service();
        $service->save();

        $bt->setUserId( $user->getId() );
        $bt->setServiceId( $service->getId() );
        $bt->save();

        // При удалении службы транзакция остается
        $service->delete();
        $findBt = Doctrine::getTable('BillingTransaction')->find( $bt->getId() );
        $this->assertType('BillingTransaction', $findBt );
        $this->assertEquals( $findBt->getId(), $bt->getId() );

        // При удалении пользователя удаляется запись о транзакции
        $user->delete();
        $findBt = Doctrine::getTable('BillingTransaction')->find( $bt->getId() );
        $this->assertEquals( $findBt, null );
    }
}