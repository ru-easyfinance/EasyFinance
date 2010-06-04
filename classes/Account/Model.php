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
        throw new Exception(__METHOD__.": Deprecated");
    }


    /**
     * Функция создаёт первую операцию со счётом
     * @param array $data
     */
    function new_operation($data)
    {
        throw new Exception(__METHOD__.": Deprecated");
    }

    function edit_operation($data)
    {
        $sql = "SELECT `id` FROM operation WHERE account_id=? AND user_id=? AND cat_id IS NULL LIMIT 0, 1";
        $oid = $this->db->selectCell($sql, $data['id'], $this->user_id);
        $model = new Operation_Model();
        $model->edit($oid, str_replace(' ', '', $data['initPayment']),'0000-00-00',0,0,'Начальный остаток', $data['id']);
    }

    /**
     * Редактирует информация о счёты в ДБ
     * @param array $args
     */
    public function update($args)
    {
        $id = $args['id'];
        $sql = "UPDATE accounts SET account_name=?, account_type_id=?, account_description=?
            , account_currency_id=?, updated_at=NOW()  WHERE account_id=? ";
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


    public function getTypeByID($id = 0) {
        $sql = "SELECT account_type_id as id FROM accounts WHERE account_id = ?";
        $exec = $this->db->query($sql, $id);
        return $exec[0]['id'];
    }

    /**
     * Возвращает список счетов пользователя
     *
     * @param  int $user
     * @return array
     */
    public function loadAll($user)
    {
        $sql = "
        SELECT
            account_id AS id,
            account_type_id as type,
            account_currency_id as currency,
            account_name as name,
            account_description as comment
        FROM accounts
        WHERE
                user_id=?
            AND deleted_at IS NULL
        ORDER BY account_name
        ";

        $accounts = array();
        foreach($this->db->query($sql, (int)$user) as $value) {
            $accounts[$value['id']] = $value;
        }

        return $accounts;
    }


    /**
     * Возвращает список счетов пользователя со статистикой по балансу и пр.
     *
     * @param  int $user
     * @return array
     */
    public function loadAllWithStat($user)
    {
        $accounts = $this->loadAll($user);

        if ($accounts) {
            $ids = array_keys($accounts);
            $totals = $this->_getTotalByAccounts($ids);
            $initBalances = $this->_getInitBalanceByAccounts($ids);

            foreach ($accounts as &$account) {
                $account['totalBalance'] = isset($totals[$account['id']]) ? (float)$totals[$account['id']] : 0;
                if ( !( 10 <= $account['type'] ) and ( $account['type'] <=15 ) )
                    $account['reserve']     = (float)$this->countReserve($account['id']);
                    $account['initPayment'] = isset($initBalances[$account['id']]) ? (float)$initBalances[$account['id']] : 0;
            }
        }

        return $accounts;
    }


    /**
     * Получить начальный баланс для списка счетов
     *
     * @param  array $accountIds
     * @return array (id => float)
     */
    private function _getInitBalanceByAccounts(array $accountIds)
    {
        $sql = "
            SELECT
                account_id AS ARRAY_KEY,
                money
            FROM operation
            WHERE
                account_id IN (?a)
                AND cat_id IS NULL
            GROUP BY account_id
        ";
        return $this->db->selectCol($sql, $accountIds);
    }


    /**
     * Получить итоги для списка счетов
     *
     * @param  array $accountIds
     * @return array (id => float)
     */
    private function _getTotalByAccounts(array $accountIds)
    {
        // в счетах отображаем общую сумму как сумму по доходам и расходам. + учесть перевод с нужным знаком.
        $sql = "
            SELECT
                account_id AS ARRAY_KEY,
                SUM(money) as sum
            FROM operation
            WHERE
                    accepted= 1
                AND deleted_at IS NULL
                AND account_id IN (?a)
            GROUP BY account_id
        ";

        return $this->db->selectCol($sql, $accountIds);
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
     * @param int $account_id Ид счёта, к которому нужно привязаться
     */
    public function bindingAmt($account_id)
    {
        // Удаляем все существующие привязки
        $sql = "DELETE FROM Acc_Values
            WHERE account_id IN (SELECT account_id FROM accounts WHERE user_id=? AND account_type_id=?)
            AND field_id=?";
        $this->db->query($sql, $this->user_id, Account_Collection::ACCOUNT_TYPE_DEBETCARD, Account::FIELD_BINDING);

        // Привязываем текущий счёт
        $sql = "INSERT INTO Acc_Values(`field_id`, `field_value`, `account_id`)
            VALUES(?, ?, ?)";

        if ($this->db->query($sql, Account::FIELD_BINDING, 'amt', $account_id)) {
            return true;
        } else {
            return false;
        }
    }
}
