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


    /**
     *
     *
     * @param sfRequest $request A request object
     */
    public function executeCurrencies(sfRequest $request) {
        $_data = Doctrine_Query::create()
            ->select('user_currency_list AS list, user_currency_default AS default')
            ->from('User a')
            ->andWhere('a.id = ?', $user = $this->getUser()->getUserRecord()->getId())
            ->limit(1)
            ->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);

        $data = array(
            'default' => (int)$_data['default'],
        );

        // массив PK используемых пользователем валют
        $currencies = unserialize($_data['list']);

        $toChange = Doctrine_Query::create()
            ->select('q.id, q.symbol, q.code, q.rate')
            ->from('Currency q')
            ->whereIn('q.id', $currencies)
            ->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

        require_once sfConfig::get('sf_root_dir') . '/../classes/Currency/efCurrencyExchange.php';
        require_once sfConfig::get('sf_root_dir') . '/../classes/Currency/efMoney.php';

        $exchange = new efCurrencyExchange();
        foreach ($toChange as $row) {
            $exchange->setRate($row['id'], $row['rate'], efCurrencyExchange::BASE_CURRENCY);
        }
        unset($row);

        foreach ($toChange as $row) {
            $data[$row['id']] = array(
                'cost' => number_format($exchange->getRate($row['id'], $data['default']), 4, '.', ''),
                'name' => $row['code'],
                'text' => $row['symbol'],
            );
        }

        $this->setVar('data', $data, $noEscape = true);
    }

}
