<?php

/**
 * robokassa actions.
 *
 * @package    EasyFinance
 * @subpackage robokassa
 * @author     EasyFinance
 */
class robokassaActions extends sfActions
{
    /**
     * /robokassa/init
     * Инициализация транзакции и возврат json-обекта
     *
     * @param sfWebRequest $request
     */
    public function executeInit(sfWebRequest $request)
    {
        $userId = $this->getUser()->getUserRecord()->getId();

        // В качестве параметра ожидаем ID услуги (POST параметр service)
        $serviceId = (int)$request->getParameter("service", 0);
        $term      = (int)$request->getParameter("term", 1);

        // Получаем стоимость услуги
        $service = Doctrine::getTable('Service')->find( $serviceId );
        $this->forward404Unless( $service );

        $price = $service->getPrice();

        // Инициализация транзакции
        $transaction = new BillingTransaction();
        $transaction->setUserId( $userId );
        $transaction->setPaysystem( 'robokassa' );
        $transaction->setServiceId( $serviceId );
        $transaction->setPrice( $price );
        $transaction->setTerm( $term );
        $total = round( $term * $price, 2 );
        $transaction->setTotal( $total );
        $transaction->save();

        $url = Robokassa::getScriptURL($transaction);


        $b = new sfWebBrowser();
        $b->get($url);
        $text = $b->getResponseText();

        $matches = array();

        preg_match_all("/^(.*)document.write\(\'(.*)\'\)/isu", $text, $matches);

        $return = array(
            "result" => array(
                "script" => html_entity_decode(trim($matches[1][0])),
                "html" => html_entity_decode(trim($matches[2][0]))
            )
        );

        $this->getResponse()->setHttpHeader('Content-Type', 'application/json');

        return $this->renderText( json_encode($return) );

    }

    /**
     * /robokassa/result
     * Отвечает на запрос робокассы, о результате операции
     *
     * @param sfWebRequest $request
     */
    public function executeResult(sfWebRequest $request)
    {
        // Web-debug панеька не нужна в dev
        sfConfig::set('sf_web_debug', false);

        // Получаем POST параметры
        $transactionId = (int)$request->getPostParameter("InvId", 0);
        $price         = $request->getPostParameter("OutSum", 0);
        $signature     = $request->getPostParameter("SignatureValue", "");
        $term          = (int)$request->getPostParameter("shpa", 0);

        // Проверяем ID транзакции
        $transaction = Doctrine::getTable('BillingTransaction')->find( $transactionId );
        $this->forward404Unless( $transaction );

        // Проверяем цену
        $this->forward404Unless( floatval( $transaction->getTotal() ) == floatval( $price ) );

        // Проверяем подпись
        $this->forward404Unless( Robokassa::checkResult( $transactionId, $price, $term, $signature ) );

        // Если проверка успешна, отоправляем "OK+номер транзакции" без обвязки!
        $this->getResponse()->setContent( "OK" . $transactionId );
        return sfView::NONE;
    }


    /**
     * /robokassa/success
     * Экшн для редиректа в случае успешного прохождения платежа
     *
     * @param sfWebRequest $request
     */
    public function executeSuccess(sfWebRequest $request)
    {
        $transaction = $this->checkSuccessAndFailParams( $request );

        // Транзакция уже завершена, повторая попытка
        if ( $transaction->getStatus() ) {
            $this->forward('services', 'index');
        }

        // Все верно, ставим флаг "оплачено"
        $transaction->setStatus( 1 );
        $transaction->setSuccess( 1 );
        $transaction->save();

        // Создаем либо обновляем сабскрипшн
        $subscription = Doctrine::getTable('ServiceSubscription')
            ->findOneByUserIdAndServiceId(
                $transaction->getUserId(),
                $transaction->getServiceId()
            );

        // Если еще нет сабскрипшна на эту услугу - создаем
        if ( !$subscription ) {
            $subscription = new ServiceSubscription();
            $subscription->setUserId( $transaction->getUserId() );
            $subscription->setServiceId( $transaction->getServiceId() );
            $subscription->setSubscribedTill( date( 'Y-m-d H:i:s', time() ) );
        }

        // Количество месяцев
        $term = (int)$request->getPostParameter("shpa", 1);

        // Накидываем 30 дней к услуге и умножаем на количество месяцев
        $subscribedTill = strtotime( $subscription->getSubscribedTill() ) + ( 3600 * 24 * 30 * $term );

        $subscription->setSubscribedTill( date( 'Y-m-d H:i:s', $subscribedTill ) );
        $subscription->save();

        $transaction->setSubscriptionId( $subscription->getId() );
        $transaction->save();

        $this->getUser()->setFlash('robokassa_status', 1);
        $this->forward( 'services', 'index');

        return sfView::NONE;
    }


    /**
     * /robokassa/fail
     * Экшн для редиректа в случае неудачного платежа
     *
     * @param sfWebRequest $request
     */
    public function executeFail(sfWebRequest $request)
    {
        $transaction = $this->checkSuccessAndFailParams( $request );

        // Выставляем флаг "ошибка"
        $transaction->setStatus( 2 );
        $transaction->setSuccess( 0 );
        $transaction->save();

        $this->getUser()->setFlash('robokassa_status', 2);
        $this->forward( 'services', 'index' );

        return sfView::NONE;
    }


    /**
     * Проверка для success и fail одинаковая, так что, выносим в отдельный метод
     *
     * @param sfWebRequest $request
     * @return BillingTransaction
     */
    private function checkSuccessAndFailParams( sfWebRequest $request )
    {
        $userId = $this->getUser()->getUserRecord()->getId();

        // Получаем POST параметры
        $transactionId = (int)$request->getPostParameter("InvId", 0);
        $price         = $request->getPostParameter("OutSum", 0);
        $signature     = $request->getPostParameter("SignatureValue", "");
        $term          = (int)$request->getPostParameter("shpa", 0);

        // Получаем транзакцию
        $transaction = Doctrine::getTable('BillingTransaction')->find( $transactionId );
        $this->forward404Unless($transaction);

        // Проверяем ID пользователя
        $this->forward404Unless( (int)$transaction->getUserId() == $userId );

        // Проверяем совпадение суммы
        $this->forward404Unless( floatval( $transaction->getTotal() ) == floatval( $price ) );

        // Проверяем подпись
        $this->forward404Unless( Robokassa::checkSuccessAndFailSignature( $transactionId, $price, $term, $signature ) );

        return $transaction;
    }
}