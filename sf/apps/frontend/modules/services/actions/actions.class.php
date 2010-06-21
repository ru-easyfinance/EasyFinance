<?php

/**
 * services actions.
 *
 * @package    EasyFinance
 * @subpackage services
 * @author     EasyFinance
 */
class servicesActions extends sfActions
{
    /**
     * Список услуг, статусы активности и т.п.
     *
     * @param sfRequest $request A request object
     */
    public function executeIndex(sfWebRequest $request)
    {
        // Получаем список услуг и передаем в шаблон
        $services = Doctrine_Query::create()->from('Service')->fetchArray();

        $sf_user = $this->getUser()->getUserRecord();

        if ( !$sf_user ) {
            return sfView::ERROR;	
        }
        
        // Получаем список подписок пользователя
        $subscriptions = Doctrine_Query::create()
            ->select('*')
            ->from('ServiceSubscription')
            ->where('user_id=?')
            ->fetchArray( $sf_user->getId() );

        // Построим массив услуг пользователя
        $userServices = array();
        foreach ( $services as $service ) {

            $isActive = false;
            $subscribedTill = '';
            foreach ( $subscriptions as $subscription ) {
            if ( ( $subscription['service_id'] == $service['id'] ) &&
                    ( strtotime( $subscription['subscribed_till'] ) > time() ) ) {
                   $isActive = true;
                   $subscribedTill = date( 'd.m.Y', strtotime( $subscription['subscribed_till'] ) );
               }
            }

            $userServices[] = array (
               'id'            => $service['id'],
               'service_name'  => $service['name'],
               'service_price' => $service['price'],
               'is_active'     => $isActive,
               'till'          => $subscribedTill
            );
        }

        // Если в сессии есть статус, отправляем во вью
        $this->status = $this->getUser()->getFlash('robokassa_status');
        $this->userServices = $userServices;

        return sfView::SUCCESS;
    }
}