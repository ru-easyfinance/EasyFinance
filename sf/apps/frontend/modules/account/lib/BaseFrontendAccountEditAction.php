<?php

/**
 * Отредактировать счет
 */
abstract class BaseFrontendAccountEditAction extends sfAction
{
    /**
     * Execute
     */
    final public function execute($request)
    {
        $account = $this->getRoute()->getObject();

        $this->form = new AccountWithBalanceForm($account);
        $data = $request->getPostParameters();
        if ($request->hasParameter("oneValue")) {
            $data["name"] = $account->getName();
            $data["type_id"] = $account->getTypeId();
            $data["currency_id"] = $account->getCurrencyId();
            $data["initBalance"] = $account->getInitBalance();
        }
        $this->form->bind($data);
        if ($this->form->isValid()) {
            $account = $this->form->save();
            $balance = (float) $this->form->getValue('initBalance');

            return $this->success($account, $balance);
        }

        return $this->error($this->form);
    }
    
    /**
     * Success
     */
    abstract protected function success(Account $account, $balance);


    /**
     * Error
     */
    abstract protected function error(AccountWithBalanceForm $form);
}
