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
        $user = $this->getUser()->getUserRecord();

        $data = array(
            'default' => (int) $user->getCurrencyId(),
        );

        // массив PK используемых пользователем валют
        $currencies = unserialize($user->getCurrencyList());

        $toChange = Doctrine_Query::create()
            ->select('q.id, q.symbol, q.code, q.rate')
            ->from('Currency q')
            ->whereIn('q.id', $currencies)
            ->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

        $exchange = sfContext::getInstance()->getMyCurrencyExchange();

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
