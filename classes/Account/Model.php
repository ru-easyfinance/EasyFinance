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


    public function getTypeByID($id = 0) {
        $sql = "SELECT account_type_id as id FROM accounts WHERE account_id = ? LIMIT 1";
        $exec = $this->db->query($sql, $id);
        return $exec[0]['id'];
    }


    /**
     * Удаляет всё информацию по счёту из БД.
     * @param array $args
     */
    static public function delete($userId, $accountId)
    {
        Core::getInstance()->db->query("START TRANSACTION");

        // Удалить счет
        $sql = "
            UPDATE accounts
            SET deleted_at=NOW(),
                updated_at=NOW()
            WHERE
                    account_id = ?
                AND user_id = ?
            LIMIT 1";
        Core::getInstance()->db->query($sql, $accountId, $userId);

        // Удалить все операции
        $opModel = new Operation_Model(Core::getInstance()->user);
        $opModel->deleteOperationsByAccountId($accountId);

        Core::getInstance()->db->query("COMMIT");
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
     * Найти счет по ID
     *
     * @param  int $accountId
     * @return array
     */
    static public function findById($accountId)
    {
        $sql = "SELECT * FROM accounts WHERE account_id = ? LIMIT 1";
        return Core::getInstance()->db->selectRow($sql, $accountId);
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
                    $account['initBalance'] = isset($initBalances[$account['id']]) ? (float)$initBalances[$account['id']] : 0;
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
                AND cat_id IS NULL AND type = ?
            GROUP BY account_id
        ";
        return $this->db->selectCol($sql, $accountIds, Operation::TYPE_BALANCE);
    }


    /**
     * Получить итоги для списка счетов
     *
     * @param  array $accountIds
     * @return array (id => float)
     */
    private function _getTotalByAccounts(array $accountIds)
    {
        $sql = "SELECT
                    o.account_id AS account_id,
                    SUM(CASE 
                        	WHEN o.account_id = a.account_id THEN o.money
                        	WHEN IFNULL(o.transfer_amount, 0) = 0 THEN ABS(o.money)  
                        	ELSE o.transfer_amount END)
                    FROM accounts acc
                    INNER JOIN 
                    	ON o.accepted = 1
                    	AND acc.account_id IN (?a) 
                    	AND o.deleted_at IS NULL
                    	AND (o.account_id = acc.account_id OR o.transfer_account_id = acc.account_id) 
                    GROUP BY acc.account_id";

        $accountsBallance = array();

        // Подсчитываем сумму по каждому счёту
        foreach ($this->db->select($sql, $accountIds, $accountIds) as $accountMoney) {
            if (isset($accountsBallance[$accountMoney['account_id']])) {
                $accountsBallance[$accountMoney['account_id']] += $accountMoney['sum'];
            } else {
                $accountsBallance[$accountMoney['account_id']] = $accountMoney['sum'];
            }
        }

        return $accountsBallance;
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


    /**
     * Найти счет пользователя привязанный к AMT
     *
     * @param  int $userId
     * @return int
     */
    static public function findBoundWithAmt($userId)
    {
        $sql = "
            SELECT a.account_id
            FROM accounts AS a
                INNER JOIN Acc_Values AS v ON (v.account_id = a.account_id)
            WHERE
                    a.deleted_at IS NULL
                AND a.user_id=?
                AND a.account_type_id=?
                AND v.field_id=?
            LIMIT 1
        ";
        return (int) Core::getInstance()->db->selectCell($sql,
            $userId,
            Account_Collection::ACCOUNT_TYPE_DEBETCARD,
            Account::FIELD_BINDING);
    }


    /**
     * Получает информацию по счёту (для ПДА)
     *
     * @param int $accountId
     * @return array
     */
    public function getAccountPdaInformation($accountId) {
        $sql = "SELECT * FROM accounts WHERE account_id=?";
        $account = $this->db->selectRow($sql, $accountId);

        $sql = "SELECT money FROM operation WHERE account_id = ? AND `type` = 3 LIMIT 1";
        $money = $this->db->selectCell($sql, $accountId);

        return array(
            'name' => $account['account_name'],
            'type' => $account['account_type_id'],
            'description' => $account['account_description'],
            'currency' => $account['account_currency_id'],
            'money' => $money,
            'id' => $accountId,
        );
    }

}
