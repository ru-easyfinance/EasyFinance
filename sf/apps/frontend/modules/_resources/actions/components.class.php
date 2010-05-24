<?php
/**
 * Набор компонентов для сборки массива ресурсов пользовательского интерфейса
 *
 */
class _resourcesComponents extends sfComponents
{
    /**
     *
     *
     * @param sfRequest $request A request object
     */
    public function executeAccounts(sfRequest $request)
    {
        $_data = Doctrine_Query::create()
            ->select('a.account_name as name,
                a.account_type_id as type,
                a.account_description as comment,
                a.account_currency_id as currency,
                a.account_id id, a.account_id account_id')
            ->from('Account a')
            ->andWhere('a.user_id = ?', $userId = $this->getUser()->getUserRecord()->getId())
            ->orderBy('a.account_name')
            ->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

        // @TODO это дерьмо считать не в цикле
        $data = array();
        foreach ($_data as $k => $v) {
            $data[$v['id']] = $_data[$k];
            // баланс аккаунта
            $data[$v['id']]['totalBalance'] = (float) Doctrine_Query::create()
                ->select('SUM(o.money) balance')
                ->from('Operation o')
                ->addWhere('o.user_id = ?', $userId)
                ->addWhere('o.account_id = ?', $v['id'])
                ->addWhere('o.accepted = 1')
                ->limit(1)
                ->fetchOne(array(), Doctrine_Core::HYDRATE_SINGLE_SCALAR);
            // начальный баланс аккаунта
            $data[$v['id']]['initPayment'] = (float) Doctrine_Query::create()
                ->select('o.money')
                ->from('Operation o')
                ->andWhere('o.user_id = ?', $userId)
                ->andWhere('o.account_id = ?', $v['id'])
                ->andWhere('comment="Начальный остаток"')
                ->limit(1)->fetchOne(array(), Doctrine_Core::HYDRATE_SINGLE_SCALAR);
            // SELECT sum(money) AS s FROM target_bill tb, target t WHERE t.id=tb.target_id AND tb.bill_id = ? AND t.done=0
            // if ( !( 10 <= $v['type'] ) and ( $v['type'] <=15 ) )
            $data[$v['id']]['reserve'] = (float) 0;
        }

        $this->setVar('data', $data, $noEscape = true);
    }

}
