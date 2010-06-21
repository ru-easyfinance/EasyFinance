<?php
require_once dirname(__FILE__).'/../../bootstrap/all.php';


/**
 * Подписки на услуги
 */
class model_ServiceSubscriptionTest extends myUnitTestCase
{
    /**
     * Отношения
     */
    public function testRelations()
    {
        $ss = new ServiceSubscription();

        // Пользователь
        $this->assertType('User', $ss->User);

        // Услуга
        $this->assertType('Service', $ss->Service);
    }


    /**
     * Тест каскадных удалений
     *
     */
    public function testCascade()
    {
        $ss = new ServiceSubscription;

        // Создаем пользователя, услугу и транзакцию
        $user = $this->helper->makeUser();
        $user->save();

        $service = new Service();
        $service->save();

        $ss->setUserId( $user->getId() );
        $ss->setServiceId( $service->getId() );
        $ss->save();

        // При удалении службы подписка удаляется
        $service->delete();
        $findSs = Doctrine::getTable('ServiceSubscription')->find( $ss->getId() );
        $this->assertEquals( $findSs, null );

        $service = new Service();
        $service->save();

        $ss->setServiceId( $service->getId() );
        $ss->save();

        // При удалении пользователя подписка удаляется
        $user->delete();
        $findSs = Doctrine::getTable('ServiceSubscription')->find( $ss->getId() );
        $this->assertEquals( $findSs, null );
    }
}