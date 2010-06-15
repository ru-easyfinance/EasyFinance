<?php
/**
 * Готовит js объект res.currency
 */
class currenciesComponent extends sfComponent
{

    /**
     * Execute
     *
     * @param sfRequest $request A request object
     */
    public function execute($request)
    {
        #Max: пля, ну сделай ты $user->getCurrencies() с проксированием к таблице
        #     и маппингу полей здесь или во вью
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

        #Max: мы же договорились, что перенесешь под sf и переименуешь
        #     сделай отдельным коммитом, я его черри-пикну в дев
        require_once sfConfig::get('sf_root_dir') . '/../classes/Currency/efCurrencyExchange.php';
        require_once sfConfig::get('sf_root_dir') . '/../classes/Currency/efMoney.php';

        # Вообще это глобальная вещь, и по хорошему надо инициализировать onDemand в конфиге и класть в контекст
        # Но давай пока оставим здесь, если нельзя быстро переделать
        $exchange = new efCurrencyExchange();
        foreach ($toChange as $row) {
            $exchange->setRate($row['id'], $row['rate'], efCurrencyExchange::BASE_CURRENCY);
        }
        unset($row);

        #Max: мапишь? а может лучше в шаблоне
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
