<?php
require_once(dirname(__FILE__).'/../lib/BaseFrontendAccountEditAction.php');

/**
 * Счета: редактирование
 */
class editAction extends BaseFrontendAccountEditAction
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
            'initBalance'  => $balance,
            'totalBalance' => $balance,
        );

        // Content-Type ставим ручками поскольку не используем шаблоны
        $this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');
        return $this->renderText(json_encode(array('result' => array(
            'account' => $result,
            'text'    => 'Счёт успешно изменён',
        ))));
    }


    /**
     * Error
     */
    protected function error(AccountWithBalanceForm $form)
    {
        $this->getResponse()->setStatusCode(400);
        $this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');

        return $this->renderText(json_encode(array('error'=>array('text' => 'Ошибка при редактировании счета'))));
    }

}
