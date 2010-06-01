<?php
require_once(dirname(__FILE__).'/../lib/BaseFrontendAccountCreateAction.php');

/**
 * Счета
 * TODO: вынести PDA в отльеный app
 */
class createForPdaAction extends BaseFrontendAccountCreateAction
{
    /**
     * Success
     */
    protected function success(Account $account, $balance)
    {
        return $this->redirect('/info');
    }


    /**
     * Error
     */
    protected function error(AccountWithBalanceForm $form)
    {
        return $this->redirect('/accounts/add');
    }
}
