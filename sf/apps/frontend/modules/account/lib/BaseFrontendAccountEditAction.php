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
        $this->form->bind($request->getPostParameters());
        if ($this->form->isValid()) {
            $account = $this->form->save();
            $balance = (float) $this->form->getValue('initPayment');

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
