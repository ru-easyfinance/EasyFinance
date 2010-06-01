<?php
require_once(dirname(__FILE__).'/../lib/BaseFrontendAccountCreateAction.php');

/**
 * Счета
 */
class createAction extends BaseFrontendAccountCreateAction
{
    /**
     * Success
     */
    protected function success(Account $account, $balance)
    {
        // TODO: вынести в шаблон
        $props = $account->toArray(false);
        $result = array(
            'id'           => (int)$account['id'],
            'type'         => (int)$account['type_id'],
            'name'         => $account['name'],
            'currency'     => (int)$account['currency_id'],
            'comment'      => $account['description'],
            'initPayment'  => $balance,
            'totalBalance' => $balance,
        );

        // Content-Type ставим ручками поскольку не используем шаблоны
        $this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');
        return $this->renderText(json_encode(array('result' => array(
            'account' => $result,
            'text'    => 'Счёт успешно добавлен',
        ))));
    }


    /**
     * Error
     */
    protected function error(AccountWithBalanceForm $form)
    {
        $this->getResponse()->setStatusCode(400);
        return $this->renderText(json_encode(array('error'=>array('text' => 'Ошибка при создании счета'))));
    }

}
