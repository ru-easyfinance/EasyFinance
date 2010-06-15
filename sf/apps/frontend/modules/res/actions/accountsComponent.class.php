<?php
/**
 * Готовит js объект res.accounts
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
        $userId = $this->getUser()->getUserRecord()->getId();

        $accounts = Doctrine::getTable('Account')
            ->queryFindWithBalanceAndInitPayment($userId)
            ->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

        $data = array();
        #Max: не делай так, меня уже трясет в старом коде от $k => $v
        foreach ($accounts as $k => $v) {
            $data[$v['id']] = $accounts[$k];
        }

        // Рассчитать суммы зарезервированные на финцели
        $ids = array_keys($data);
        $reserves = Doctrine::getTable('Account')
            ->queryCountReserves($ids)
            ->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

        foreach ($data as $k => $v) {
            // нужно ли ставить 0 или только при положительных значениях?
            if (isset($reserves[$v['id']])) {
                $data[$v['id']]['reserve'] = (float) $reserves[$v['id']]['reserve'];
            } else {
                $data[$v['id']]['reserve'] = 0;
            }
        }

        $this->setVar('data', $data, $noEscape = true);
    }

}
