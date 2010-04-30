<?php
class Account_Model 
{
    /**
     * идентификатор базы данных
     * @var db
     */
    private $db = NULL;

    /**
     * Пользователь
     * @var
     */
    private $user_id = NULL;

    public function __construct()
    {
        $this->db = Core::getInstance()->db;
        $this->user_id = Core::getInstance()->user->getId();
    }

    /**
     * Записывает информацию по счёту в БД.
     * @param array $args
     */
    public function create( $args )
    {
        //получаем массив всех параметров для записи 
        //записываем общие поля.
        $sql = "INSERT INTO accounts (account_name, account_type_id, account_description
                , account_currency_id, user_id)
                VALUES (?, ?, ?, ?, ?)";

        $exec = $this->db->query($sql, $args[1], $args[2], $args[7], $args[29], $this->user_id);
        $acc_id = mysql_insert_id();//определяем айдишник только что созданного счёта
        unset($args[1]);//название счёта
        unset($args[2]);//тип счёта
        unset($args[7]);//описание счёта
        unset($args[29]);//валюта счёта
        unset($args['id']);//убираем из массива поля , которые не будем записывать в таблицу доп полей. айди

        foreach ($args as $k=>$v){
            $sql = "INSERT INTO Acc_Values (field_id, field_value, account_id) VALUES (?, ?, ?)";
            $exec = $this->db->query($sql, $k, $v, $acc_id);
        }
        return $acc_id;

        
    }

    function getFirstOperation($account_id=0)
    {
        $sql = "SELECT money FROM operation WHERE user_id=? AND account_id=? AND comment='Начальный остаток'";
        $first = $this->db->query($sql, $this->user_id, $account_id);
        return ( (isset($first[0]))?($first[0]['money']):0 );
    }

    /**
     * Функция создаёт первую операцию со счётом 
     * @param array $data
     */
    function new_operation($data)
    {
        //если счёт долговой , то добавляем первую операцию со знаком минус.
        //7 - займ полученный
        //8 - кредитная карта
        //9 - кредит
        $tip = "SELECT account_type_id FROM accounts WHERE account_id=? AND user_id=?";
        $acc = $this->db->query($tip, $data['id'], $this->user_id);//тип счёта
        $drain = 0;
        if ( ($acc[0]['account_type_id'] == 7) || ($acc[0]['account_type_id'] == 8) || ($acc[0]['account_type_id'] == 9)){
            if ( $data['initPayment'][0] == '-')
                $data['initPayment'] = substr($data['initPayment'],1);
            $data['initPayment'] = '-'.$data['initPayment'];
            $drain=1;
        }

       $model = new Operation_Model();
       $model->add(str_replace(' ', '', $data['initPayment']), /*date('Y.m.d')*/'0000-00-00', 0,$drain,
           'Начальный остаток', $data['id']);
    }

    function edit_operation($data)
    {
        $tip = "SELECT account_type_id FROM accounts WHERE account_id=? AND user_id=?";
        $acc = $this->db->query($tip, $data['id'], $this->user_id);//тип счёта
        $drain = 0;
        if ( ($acc[0]['account_type_id'] == 7) || ($acc[0]['account_type_id'] == 8) || ($acc[0]['account_type_id'] == 9)){
            if ( $data['initPayment'][0] == '-')
                $data['initPayment'] = substr($data['initPayment'],1);
            $data['initPayment'] = '-'.$data['initPayment'];
            $drain=1;
        }

        $sql = "SElECT `id` FROM operation WHERE account_id=? AND user_id=? ORDER BY `dt_create`";
        $oid = $this->db->selectCell($sql,$data['id'],$this->user_id);
        $model = new Operation_Model();
        $model->edit($oid,str_replace(' ', '', $data['initPayment']),'0000-00-00',0,0,'Начальный остаток', $data['id']);

    }

    /**
     * Редактирует информация о счёты в ДБ
     * @param array $args
     */
    public function update($args)
    {
        $id = $args['id'];
        $sql = "UPDATE accounts SET account_name=?, account_type_id=?, account_description=?
            , account_currency_id=?  WHERE account_id=? ";
        $exec = $this->db->query($sql, $args[1], $args[2], $args[7], $args[29], $id);
        unset($args[1]);//название счёта
        unset($args[2]);//тип счёта
        unset($args[7]);//описание счёта
        unset($args[29]);//валюта счёта
        unset($args['id']);//убираем из массива поля , которые не будем записывать в таблицу доп полей. айди

        foreach ($args as $k=>$v){
            $sql = "UPDATE Acc_Values SET field_value=? WHERE field_id=? AND account_id=?";
            $exec = $this->db->query($sql, $v, $k, $id);
        }

    }

    /**
     * Удаляет всё информацию по счёту из БД.
     * @param array $args
     */
    public function delete($args)
    {
        $id = $args['id'];
        $sql = "DELETE FROM accounts WHERE account_id=?";
        $exec = $this->db->query($sql, $id);//удаляем запись из таблицы счетов
        $sql = "DELETE FROM Acc_Values WHERE account_id=?";
        $exec = $this->db->query($sql, $id);//удаляем все записи по счёту из таблицы доп. значений
    }

    public function loadAccountById($account_id)
    {
        $sql = "SELECT account_name as name, account_type_id as type, account_description as comment, account_currency_id as currency, account_id FROM
            Acc_Object WHERE account_id=?";
        $exec = $this->db->query($sql, $account_id);

        $return = array();
        $sql = "SELECT f.name as name, f.description as des, v.field_value FROM Acc_Values v, Acc_Fields f
            WHERE v.field_id=f.account_type AND account_id=?";
        $dop = $this->db->query($sql, $account_id);
        foreach ($exec as $k1=>$v1){
            $return[$k1] = $v1;
        }
        foreach ($dop as $k2=>$v2){
            $return[0][$v2['name']] = $v2['field_value'];
        }
        return $return;
        
    }


    public function getTypeByID( $id = 0 ){
        $sql = "SELECT account_type_id as id FROM accounts WHERE account_id = ?";
        $exec = $this->db->query($sql, $id);
        return $exec[0]['id'];
    }
    /**
     * Возвращает массив счетов пользователя, со всеми прилагающимися параметрами
     * @param User $user
     * @return array
     */
    public function loadAll($user)
    {
        $sql = "SELECT
            account_name as name,
            account_type_id as type,
            account_description as comment,
            account_currency_id as currency,
            account_id
            FROM accounts
            WHERE user_id=?
            ORDER BY account_name";

        $string = "";

        // список основных параметров по счетам
        foreach($this->db->query($sql, $user) as $value) {

            $accounts[$value['account_id']] = $value;
            $accounts[$value['account_id']]['id'] = $value['account_id'];
            if ($string) {
                $string .= ',';
            }

            $string .=  $value['account_id'] ;

        }

        // Подробный список параметров
        $sql = "SELECT DISTINCT v.account_id, f.name as field, v.field_value
                FROM Acc_Values v, Acc_Fields f, Acc_ConnectionTypes c, accounts o
                WHERE o.account_type_id = c.type_id AND c.field_id = f.id AND f.id = v.field_id
                AND v.account_id IN ({$string})";

        $fields = $this->db->select($sql);

        foreach($fields as $value) {
            $accounts[$value['account_id']][$value['field']] = $value['field_value'];
        }

        return $accounts;
    }

    /**
     * Функция возвращает зарезервированную сумму по айди пользователя.
     * @param int $acc_id
     */
    public function countReserve($acc_id)
    {
        $reservquery = "SELECT sum(money) AS s
                FROM target_bill tb, target t
                WHERE t.id=tb.target_id AND
                 tb.bill_id = ? AND t.done=0";
        $result = $this->db->query($reservquery, $acc_id);

        $ret = $result[0]['s'];
        if ( $ret == null )
            $ret = 0;
        return $ret;
    }

    /**
     * По айди счёта определяет общий баланс счёта
     * @param int $acc_id
     * @return float
     */
    public function countTotalBalance($acc_id = 0){
        $balance = 0; //общий баланс счёта, с учётом всех его операций
        $op = new Operation_Model();
        $balance = $op->getTotalSum($acc_id);
        return $balance;
    }

    public function countSumInDefaultCurrency($total = 0, $curr = 1)
    {
        $ucur = Core::getInstance()->user->getUserCurrency();
        $cur_k = array_keys($ucur);
        return round(
                $total * $ucur[$curr]['value']/$ucur[$cur_k[0]]['value'],
                2
        );
    }

    /**
     * Связывание счёта с АМТ
     *
     * @param int $id Ид счёта, к которому нужно привязаться
     */
    public function bindingAmt($id)
    {
        // Удаляем все существующие привязки
        $sql = "DELETE FROM Acc_Values 
            WHERE account_id IN (SELECT account_id FROM accounts WHERE user_id=? AND account_type_id=?) 
            AND field_id=?";
        $this->db->query($sql, $this->user_id, Account_Collection::ACCOUNT_TYPE_DEBETCARD, Account::FIELD_BINDING);

        // Привязываем текущий счёт
        $sql = "INSERT INTO Acc_Values(`field_id`, `field_value`, `account_id`) 
            VALUES(?, ?, ?)";

        if ($this->db->query($sql, Account::FIELD_BINDING, 'amt', $id)) {
            return true;
        } else {
            return false;
        }
    }
}
