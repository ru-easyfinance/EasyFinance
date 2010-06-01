<?php


/**
 * Счета
 */
class accountActions extends sfActions
{
    /**
     * Создать счет
     */
    public function executeCreate(sfRequest $request)
    {
        $user = $this->getUser()->getUserRecord();

        $account = new Account;
        $account->setUser($user);

        $this->form = new AccountWithBalanceForm($account);
        $this->form->bind($request->getPostParameters());
        if ($this->form->isValid()) {
            $account = $this->form->save();
            $balance = (float) $this->form->getValue('initPayment');

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
            return $this->renderText(json_encode(array('result' => $result)));
        }

        $this->getResponse()->setStatusCode(400);
        return $this->renderText(json_encode(array('error'=>array('text' => 'Ошибка при создании счета'))));
    }

}
