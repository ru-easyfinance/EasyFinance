<?php

//ниже приведены айди полей для таблицы дополнительных значений. и ещё названия - ключи ассоциативного
//массива, который передаём в обе стороны
/*                                      field_id                       
Название                                1   name                       
Тип счёта                               2   type
Начальная операция                      3   initPament
Доступный остаток                       30  available
Зарезервировано                         31  reserve
Общий остаток                           4   amount
Итого годовой %                         5   sumYearPercent
Текущая рыночная стоимость              6   currentMarketCost
Примечание                              7   comment
Банк                                    8   bank
Заимополучатель                         9   loanReceiver
Заимодавец                              10  loanGiver
% годовых                               11  yearPercent
Доходность % годовых                    12  incomeYearPercent
Дата выдачи                             13  dateGive
Дата возврата                           14  dateReturn
Дата открытия                           15  dateOpen
Дата закрытия                           16  dateClose
Дата получения                          17  dateGet
Дата погашения                          18  dateOff
Кредитный лимит                         19  creditLimit
Свободный остаток                       20  remainAmount
Грейс-период                            21  graisePeriod
Тип карты \ Платежная система           22  paySystem
Срок действия                           23  validityPeriod
Тип платежа                             24  typePayment
Обеспечение                             25  support
Тип металла                             26  typeMetal
УК                                      27  UK
Тип имущества                           28  typeProperty
Валюта                                  29  currency
*/

class Account_Collection extends _Core_Abstract_Collection
{

    CONST ACCOUNT_TYPE_CASH = 1;
    CONST ACCOUNT_TYPE_DEBETCARD = 2;
    CONST ACCOUNT_TYPE_DEPOSIT = 5;
    CONST ACCOUNT_TYPE_LOANGIVE = 6;
    CONST ACCOUNT_TYPE_LOANGET = 7;
    CONST ACCOUNT_TYPE_CREDITCARD = 8;
    CONST ACCOUNT_TYPE_CREDIT = 9;
    CONST ACCOUNT_TYPE_METALLIC = 10;
    CONST ACCOUNT_TYPE_SHARE = 11;
    CONST ACCOUNT_TYPE_PIF = 12;
    CONST ACCOUNT_TYPE_OFBU = 13;
    CONST ACCOUNT_TYPE_PROPERTY = 14;
    CONST ACCOUNT_TYPE_ELECTPURSE = 15;

    /**
     *
     * @param Account $account
     */
    /*function add(Account $account)
    {
        array_push($this->container, $account);
    }*/

    /**
     * Загружает список счетов из базы данных
     * @param User $user
     */
    public function load( $user )
    {
        $model = new Account_Model();
        $res = $model->loadAll($user);
        //$res = Account_Model::loadAll($user);
        //$res = $prepare;
        //$this->container=array();
        //$ret = array();
        foreach ($res as $k=>$v){
            $v['totalBalance'] = $model->countTotalBalance($v['id']);
            if ( !( 10 <= $v['type'] ) and ( $v['type'] <=15 ) )
                $v['reserve'] = (float)$model->countReserve($v['id']);
                $v['defCur'] = $model->countSumInDefaultCurrency($v['totalBalance'], $v['currency']);
                $v['initPayment'] = (float)$model->getFirstOperation($v['id']);
                
                $ret[$v['id']]=$v;
        }

        //die (print_r($ret));
        $ret = array('result'=>array(
            'data'=>$ret
            ));
        return $ret;
    }

    /**
     * Загружает информацию по конкретному счёту
     * @param int $acc_id
     */
    public function loadOneAcc( $acc_id )
    {
        $model = new Account_Model();
        $res = $model->loadAccountById($acc_id);
        //$this->container = array();
        //array_push($this->container, $res);

        return $res;
    }

    public function countAcc( $user )
    {
        $howmuch = $this->load($user);
        
    }

}

//// Создаём счёт наличные
//$account = Account_Cache::create($user, 'Название счёта', 'Комментарий счёта');
//$account = Account_Cache::create($user, $_POST['name'], 'Комментарий счёта', 1000);
//$accountCollection->add($account);
//
//// Модифицируем код
//$account->setName('имя 2');
//$accountCollection->add($account);
//

