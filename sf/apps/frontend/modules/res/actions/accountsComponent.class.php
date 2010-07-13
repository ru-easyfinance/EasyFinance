<?php
/**
 * Готовит js объект res.accounts
 *                 и res.accountsRecent
 */
class accountsComponent extends sfComponent
{
    /**
     * Execute
     *
     * @param sfRequest $request A request object
     */
    public function execute($request)
    {
        $user = $this->getUser()->getUserRecord();

        $accounts = Doctrine::getTable('Account')
            ->queryFindWithBalanceAndBalanceOperation($user)
            ->fetchArray();

        // выбрать идентификаторы счетов
        $accountsIds = array();
        foreach ($accounts as $account) {
            $accountsIds[] = $account['id'];
        }

        // Рассчитать суммы зарезервированные на финцели
        $reserves = Doctrine::getTable('TargetTransaction')
            ->queryCountReserves($accountsIds, $user)
            ->fetchArray();

        // Получить последние использовавшиеся счета
        $accountsRecent = Doctrine::getTable('Operation')->getMonthCountByUser($user);

        $this->setVar('accounts', $accounts, $noEscape = true);
        $this->setVar('reserves', $reserves, $noEscape = true);
        $this->setVar('accountsRecent', $accountsRecent, $noEscape = true);
    }

}
