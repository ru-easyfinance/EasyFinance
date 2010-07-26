<?php

/**
 * Класс модели для управления операциями
 *
 * @category operation
 * @author Max Kamashev (ukko) <max.kamashev@gmail.com>
 * @copyright http://easyfinance.ru/
 */
class Operation_Model
{
    /**
     * Ссылка на экземпляр класса базы данных
     * @var DbSimple_Mysql
     */
    private $db = null;

    /**
     * Ссылка на экземпляр класса пользователя
     * @var oldUser
     */
    private $_user = null;

    /**
     *
     * @var
     */
    private $account_money = 0;

    /**
     *
     * @var type
     */
    private $user_money = Array();

    /**
     *
     * @var type
     */
    private $total_sum = 0;

    /**
     * Массив со списком ошибок, появляющимися при добавлении, удалении или редактировании (если есть)
     * @var array mixed
     */
    public $errorData = array();

    /**
     * Конструктор
     *
     * @param oldUser $user
     * @return bool
     */
    function __construct(oldUser $user = null)
    {
        if ($user) {
            $this->_user = $user;
        } else {
            $this->_user = Core::getInstance()->user;
        }

        $this->db = Core::getInstance()->db;
        $this->load();
        return true;

    }

    /**
     * Загрузка данных из сессии
     * @return void
     */
    function load()
    {
        if (isset($_SESSION['user_money'])) {
            $this->user_money = $_SESSION['user_money'];
        } else {
            $this->user_money = array();
        }
        $this->account_money = (int) @$_SESSION['account_money'];
        $this->total_sum = (int) @$_SESSION['total_sum'];
    }

    /**
     * Сохранение данных в сессии
     * @return void
     */
    function save()
    {
        $_SESSION['user_money'] = $this->user_money;
        $_SESSION['account_money'] = $this->account_money;
        $_SESSION['total_sum'] = $this->total_sum;
    }

    /**
     * Проверяет валидность введённых данных
     * @param array $params
     * @return array
     */
    public function checkData(array $operation = array())
    {
        $validated = array();
        $this->errorData = array();

        // Проверяем ID
        if (array_key_exists('id', $operation)) {
            $validated['id'] = (int) $operation['id'];
            if ($validated['id'] === 0) {
                $this->errorData['id'] = 'Не указан id события';
            }
        }

        // Проверяем тип операции
        if (array_key_exists('type', $operation)) {
            $validated['type'] = (int) $operation['type'];
        }

        // Проверяем счёт
        if (array_key_exists('account', $operation)) {
            $validated['account'] = (int) $operation['account'];
            if ((int) $operation['accepted'] == 1 && $validated['account'] === 0) {
                $this->errorData['account'] = 'Не выбран счёт';
            }

            $accounts = $this->_user->getUserAccounts();

            if (!isset($accounts[$validated['account']])) {
                $this->errorData['account'] = 'Указанного счёта не существует';
            }
        }

        // Проверяем сумму
        if (array_key_exists('amount', $operation)) {
            if ((int) $operation['accepted'] == 1 && empty($operation['amount'])) {
                $this->errorData['amount'] = 'Сумма не должна быть равной нулю.';
            }

            $validated['amount'] = $operation['amount'];
        }

        // Проверяем категорию
        if (array_key_exists('category', $operation)) {
            $validated['category'] = (int) $operation['category'];
            if (empty($validated['category'])) {
                $validated['target'] = (int) $operation['target'];
                $validated['toAccount'] = (int) $operation['toAccount'];

                if (empty($validated['target']) && empty($validated['toAccount'])) {
                    $this->errorData['category'] = 'Нужно указать ';

                    switch ($operation['type']) {
                        case Operation::TYPE_TRANSFER:
                            $this->errorData['category'] .= 'целевой счёт.';
                            break;
                        case Operation::TYPE_TARGET:
                            $this->errorData['category'] .= 'цель.';
                            break;
                        case Operation::TYPE_PROFIT || Operation::TYPE_WASTE:
                        default:
                            $this->errorData['category'] .= 'категорию.';
                    }
                }
            } elseif ((int) $operation['accepted'] == 1 && ( ($validated['type'] == 0) || ($validated['type'] == 1) )) {
                $cat = $this->db->query("SELECT count(*) as co FROM category WHERE cat_id=? AND deleted_at IS NULL", $validated['category']);

                if ($cat[0]['co'] != 1) {
                    $this->errorData['category'] = 'Выбранной категории не существует!';
                }
            }
        }

        // Проверяем дату
        if (array_key_exists('date', $operation)) {
            $validated['date'] = Helper_Date::RusDate2Mysql($operation['date']);
            //$validated['date'] = Helper_Date::getMysqlFromString( $operation['date'] );

            if ($validated['date'] == '0000-00-00' || empty($validated['date'])) {
                $this->errorData['date'] = 'Неверно указана дата.';
            }
        }

        $validated['comment'] = trim($operation['comment']);

        // Проверяем теги
        if (!empty($operation['tags'])) {
            $validated['tags'] = array();
            $tags = explode(',', trim($operation['tags']));
            foreach ($tags as $tag) {
                $tag = trim($tag);

                if (!empty($tag)) {
                    if (!in_array($tag, $validated['tags'])) {
                        $validated['tags'][] = trim($tag);
                    }
                }
            }
        } else {
            $validated['tags'] = array();
        }

        // Проверяем тип операцииe
        // - Перевод со счёта на счёт
        if ($validated['type'] == 2) {
            $validated['currency'] = (float) $operation['currency'];

            if ((float) $operation['currency'] != 0) {
                $validated['convert'] = round($validated['amount'] * (float) $operation['currency'], 2);
            } else {
                $validated['convert'] = 0;
            }

            $validated['toAccount'] = (int) $operation['toAccount'];
        }
        // - Финансовая цель
        elseif ($validated['type'] == 4) {
            $validated['target'] = $operation['target'];

            if (($operation['close']) == 1) {
                $validated['close'] = 1;
            } else {
                $validated['close'] = 0;
            }
        }

        return $validated;

    }

    /**
     * Проверяет, что это не дубликат введённых данных
     *
     * @param float     $money
     * @param date      $date
     * @param int       $category
     * @param string    $comment
     * @param int       $account
     * @return array
     */
    function checkExistance($money = 0, $date = '', $category = 0, $comment = '', $account = 0) {
        $last = $this->db->select("
                    SELECT id
                    FROM operation
                    WHERE user_id=? AND money=? AND date=?
                        AND cat_id=? AND comment=? AND account_id=?
                        AND created_at BETWEEN ADDDATE(NOW(), INTERVAL -2 SECOND) AND NOW()",
                $this->_user->getId(), $money, $date, $category, $comment, $account);

        return $last;
    }

    /**
     * Регистрирует новую транзакцию
     *
     * @param int       $type       Тип операции: 0 - расход, 1 - доход
     * @param float     $money      Сумма транзакции
     * @param string    $date       Дата транзакции в формате Y.m.d
     * @param int       $category   Ид категории
     * @param string    $comment    Комментарий транзакции
     * @param int       $account_id Ид счета
     * @param array     $tags       Массив с тегами
     *
     * @return int $id
     */
    function add($type, $money = 0, $date = '', $category = null, $comment = '',
        $account = 0, $tags = array(), $accepted = true
    )
    {
        // Если операция новая, и отправлена не случайно, то продолжаем, иначе возвраты
        $check = $this->checkExistance($money, $date, $category, $comment, $account);
        if($check) {
            return $check;
        }

        $values = array(
            'user_id'   => $this->_user->getId(),
            'money'     => ((!$type)? abs($money) * -1: abs($money)),
            'date'      => $date,
            'cat_id'    => ((int)$category <= 0) ? null : $category,
            'account_id'=> $account,
            'type'      => $type,
            'comment'   => $comment,
            'tags'      => implode(', ', (array)$tags),
            'accepted'  => $accepted ? 1 : 0
        );

        $operationId = $this->_addOperation($values);

        // Обновляем данные о счетах пользователя
        $this->_user->initUserAccounts();
        $this->_user->save();

        $this->save();
        return $operationId;

    }

    /**
     * Добавляет несколько операций одновременно
     * @param array $operations_array
     * @return int
     */
    function addSome($operations_array = array())
    {
        $this->db->query("BEGIN;");

        foreach($operations_array as $operation) {
            $values = array(
                'user_id'   => $this->_user->getId(),
                'money'     => (($operation['type'] == 0) ? (-1 * abs($operation['amount'])) : abs($operation['amount'])),
                'date'      => $operation['date'],
                'cat_id'    => ((int)$operation['category'] <= 0) ? null : $operation['category'],
                'account_id'=> $operation['account'],
                'type'      => $operation['type'],
                'comment'   => $operation['comment'],
                'tags'      => $operation['tags'],
                'accepted'  => $operation['accepted'],
                'chain_id'  => $operation['chain'],
            );

            $notifications = array(
                'mailEnabled'       => $operation['mailEnabled'],
                'mailDaysBefore'    => $operation['mailDaysBefore'],
                'mailHour'          => $operation['mailHour'],
                'mailMinutes'       => $operation['mailMinutes'],

                'smsEnabled'       => $operation['smsEnabled'],
                'smsDaysBefore'    => $operation['smsDaysBefore'],
                'smsHour'          => $operation['smsHour'],
                'smsMinutes'       => $operation['smsMinutes'],
            );

            $this->_addOperation($values, $notifications);
        }
        $this->db->query("COMMIT;");

        // Обновляем данные о счетах пользователя
        $this->_user->initUserAccounts();
        $this->_user->save();

        $this->save();
        return count($operations_array);
    }

    /**
     * Добавляет трансфер с одного на другой счёт
     *
     * @param $id           int       Ид операции
     * @param $money        float     Деньги
     * @param $convert      float     Конвертированные в нужную валюту деньги
     * @param $date         string    Дата, когда совершаем трансфер
     * @param $from_account int       Со счёта
     * @param $to_account   int       На счёт
     * @param $comment      string    Комментарий
     * @param $tags         array     Тег
     * @param $accepted     int
     * @return bool
     */
    function editTransfer($id=0, $money = 0, $convert = 0, $date = '', $account = 0, $toAccount=0, $comment = '', $tags = '', $accepted = null)
    {
        $values = array(
            'money'                 => abs($money) * -1,
            'date'                  => $date,
            'account_id'            => $account,
            'transfer_account_id'   => $toAccount,
            'comment'               => $comment,
            'transfer_amount'       => abs($convert),
            'tags' => ((empty($tags)) ? "" : implode(', ', $tags)),
        );

        if ($accepted) {
            $values['accepted'] = '1';
        }

        $this->_updateOperation($this->_user, $id, $values);

        // Обновляем данные о счетах пользователя
        $this->_user->initUserAccounts();
        $this->_user->save();

        $this->save();
        return '[]';
    }

    /**
     * Добавляет несколько однообразных переводов между счетами
     * @param array $operationsArray Calendar_Event $event
     * @return int
     */
    function addSomeTransfer($operationsArray)
    {
        try {
            $this->db->query("START TRANSACTION");

            foreach ($operationsArray as $operation) {
                $values = array(
                    'user_id'               => $this->_user->getId(),
                    'money'                 => abs($operation['amount']) * -1,
                    'date'                  => $operation['date'],
                    'cat_id'                => null,
                    'type'                  => 2,
                    'account_id'            => $operation['account'],
                    'transfer_account_id'   => $operation['toAccount'],
                    'transfer_amount'       => $this->_convertAmount($operation['account'],
                                                                        $operation['toAccount'],
                                                                        $operation['amount'],
                                                                        $operation['convert']),
                    'comment'               => $operation['comment'],
                    'tags'                  => $operation['tags'],
                    // Операции из календаря
                    'chain_id'              => !empty($operation['chain'])    ? (int) $operation['chain'] : 0,
                    'accepted'              => !empty($operation['accepted']) ? (int) $operation['accepted'] : 0,
                );

                $lastId = $this->_addOperation($values);
            }

            $this->_user->initUserAccounts();
            $this->_user->save();
            $this->db->query("COMMIT");
        } catch (Exception $e) {
            $this->db->query("ROLLBACK");
            throw $e;
        }

        return count($operationsArray);
    }

    /**
     * Добавляем перевод со счёта на счёт
     *
     * @param float  $money
     * @param float  $convert
     * @param float  $exchangeRate
     * @param date   $date
     * @param int    $fromAccount
     * @param int    $toAccount
     * @param string $comment
     * @param array $tags
     * @return int
     */
    function addTransfer($money, $convert, $exchangeRate, $date, $fromAccount, $toAccount, $comment, array $tags = array())
    {
        $values = array(
            'user_id'               => $this->_user->getId(),
            'money'                 => abs($money) * -1,
            'date'                  => $date,
            'cat_id'                => null,
            'account_id'            => $fromAccount,
            'comment'               => $comment,
            'transfer_account_id'   => $toAccount,
            'type'                  => 2,
            'exchange_rate'         => $exchangeRate,
            'transfer_amount'       => $this->_convertAmount($fromAccount, $toAccount, $money, $convert),
            'tags'                  => implode(', ', $tags),
        );

        $lastId = $this->_addOperation($values);

        $this->_user->initUserAccounts();
        $this->_user->save();

        return $lastId;
    }

    /**
     * Редактирует транзакцию
     *
     * @param int       $type       Тип операции: 0 - расход, 1 - доход
     * @param int       $id         Ид транзакции
     * @param float     $money      Сумма транзакции
     * @param string    $date       Дата транзакции в формате Y.m.d
     * @param int       $category   Ид категории
     * @param string    $comment    Комментарий транзакции
     * @param int       $account_id Ид счета
     * @param array     $tags       Теги
     * @param int       $accepted   Подтверждена ли операция
     * @param array     $notifications
     *
     * @return bool true - Регистрация прошла успешно
     */
    function edit($type, $id, $money = 0, $date = '', $category = null, $comment = '', $account = 0, array $tags = array(), $accepted = null, $notifications = array())
    {
        $values = array(
            'money'         => $money,
            'date'          => $date,
            'cat_id'        => $category,
            'account_id'    => $account,
            'comment'       => $comment,
            'tags'          => implode(', ', $tags),
            'type'          => $type,
        );

        if ($accepted) {
            $values['accepted'] = '1';
        }

        $this->_addNotifications($id, $date, $notifications, $this->_user);
        $this->_updateOperation($this->_user, $id, $values);

        // Обновляем данные о счетах пользователя
        $this->_user->initUserAccounts();
        $this->_user->save();

        $this->save();
        return '[]';

    }


    /**
     * Редактирует массив операций
     */
    function editMultiple(array $operationsArray)
    {
        $this->db->query("BEGIN;");

        foreach($operationsArray as $operation) {
            $values = array(
                'accepted'  => $operation['accepted'],
            );

            $this->_updateOperation($this->_user, $operation['id'], $values);
        }
        $this->db->query("COMMIT");

        return count ($operationsArray);
    }

    /**
     * Удаляет указанную операцию
     *
     * @param int id Ид операции
     * @return bool true - в случае успеха, false - в случае ошибки
     */
    function deleteOperation($id = 0) {
        $sql = "UPDATE operation o SET deleted_at=NOW() WHERE user_id = ? AND id = ?";

        return (bool) $this->db->query($sql, $this->_user->getId(), $id);
    }


    /**
     * Удалить операцию по указанному счету
     */
    function deleteOperationsByAccountId($accountId)
    {
        $opIds = $this->db->selectCol("
            SELECT id FROM operation
            WHERE
                user_id = ?
                AND (account_id = ? OR transfer_account_id = ?)
                AND type <> " . Operation::TYPE_BALANCE . "
            ",
            // Балансовые операции не удаляем, поскольку это не совсем операции
            $this->_user->getId(), $accountId, $accountId);

        if ($opIds) {
            $this->db->query("UPDATE operation o SET deleted_at=NOW() WHERE id IN (?a)", $opIds);
        }
    }


    /**
     * Удаляет операцию перевода на финцель
     * @param int $id
     * @return bool
     */
    function deleteTargetOperation($id=0)
    {
        $tr_id = $this->db->select("SELECT target_id FROM target_bill WHERE id=?", $id);
        $this->db->query("DELETE FROM target_bill WHERE id=? AND user_id=?", $id, $this->_user->getId());
        $targ = new Targets_Model();
        $targ->staticTargetUpdate($tr_id[0]['target_id']);
        $this->_user->initUserTargets();
        $this->_user->save();
        return true;

    }

    /**
     * Получение списка транзакций в виде массива
     *
     * @param date      $dateFrom           Дата с которой показывать операции
     * @param date      $dateTo             Дата до какой показывать операции
     * @param int       $currentCategory    Ид категории по которой отобрать операции
     * @param int       $currentAccount     Ид счёта по которому отобрать операции
     * @param int       $type               Тип операции (0 - Расход, 1 - Доход, 2 - Перевод, 4 - Перевод на фин.цель)
     * @param float     $sumFrom            Минимальная сумма для показа операций
     * @param float     $sumTo              Максимальная сумма для показа операций
     * @param string    $searchField        Поле поиска по комментариям и меткам
     * @param bool      $stat               true - Вывести только сумму операций. default = false
     * @param bool      $accountInitial     Если true - включить в выборку баллансовую операцию. default = false
     * @return array mixed
     */
    function getOperationList($dateFrom, $dateTo, $currentCategory = null,
        $currentAccount = null, $type = null, $sumFrom = null, $sumTo = null,
        $searchField = '', $stat = false, $accountInitial = false
    )
    {
        // Подготавливаем фильтр по родительским категориям
        $listCategories = $this->_user->getUserCategory();
        $category = $this->_getRelatedCategories($currentCategory, $listCategories);

        // Подготавливаем фильтр по меткам и комментариям
        $searchSql = $this->_getSearchQuery($searchField);

        // Конвертация валют
        $actualCurrency = sfConfig::get('ex')->getRate($this->_user->getUserProps('user_currency_default'));
        if (!$stat) {
            // Выборка операций пользователя
            // money выбирается из transfer_amount для переводов
            $sql = "SELECT
                        o.id,
                        o.user_id,
                        (CASE 
                        	WHEN o.account_id = a.account_id THEN o.money
                        	WHEN o.transfer_amount = 0 THEN ABS(o.money) 
                        	ELSE o.transfer_amount END) AS money,
                        DATE_FORMAT(o.date,'%d.%m.%Y') as `date`,
                        o.date AS dnat,
                        o.cat_id,
                        NULL as target_id,
                        o.account_id,
                        o.comment,
                        o.transfer_account_id AS transfer,
                        0 AS virt,
                        o.tags,
                        (
                            (CASE 
                            	WHEN o.account_id = a.account_id THEN o.money
                            	WHEN o.transfer_amount = 0 THEN ABS(o.money)  
                            	ELSE o.transfer_amount END)
                            *(CASE WHEN rate = 0 THEN 1 ELSE rate END)/$actualCurrency
                        ) as moneydef,                        
                        o.exchange_rate AS curs,
                        o.type,
                        o.created_at,
                        o.source_id AS source ";
        } else {

            //compute sign basing on account relation to fix transfers

            $sql = "SELECT
                        sum(mm) as total_money
                    FROM (
                    SELECT sum(
                        (CASE 
                        	WHEN o.account_id = a.account_id THEN o.money
                        	WHEN o.transfer_amount = 0 THEN ABS(o.money)  
                        	ELSE o.transfer_amount END)
                        *(CASE WHEN rate = 0 THEN 1 ELSE rate END)/$actualCurrency) as mm ";        
                    ;
        }
 
        // Грязный хак, который надо убить, но см баг 1652
        $accountJoinCondition = ' 1 ';

        if ($stat) // Для получения баланса отбираем все операции для взаимоуничтожения переводов
            $accountJoinCondition = '(o.account_id = a.account_id OR o.transfer_account_id = a.account_id)';
        elseif (!$stat && (int) $currentAccount <= 0) // для списка операций по всем счетам перевод включаем один раз
            $accountJoinCondition = '(o.account_id = a.account_id)';
        elseif (!$stat && (int) $currentAccount > 0) // для списка операций по одному счёту ищем все переводы
            $accountJoinCondition = '(o.account_id = a.account_id OR o.transfer_account_id = a.account_id)';
        // Конец хака

        $sql .= "
                    FROM accounts a, currency c, operation o
                    WHERE $accountJoinCondition AND a.account_currency_id  = c.cur_id AND
                          $searchSql
                          o.user_id = " . $this->_user->getId();

        // Добавляем фильтр для обязательного скрытия удалённых
        $sql .= " AND o.deleted_at IS NULL ";

        // Добавляем фильтр для обязательного скрытия операций для удалённых счетов
        $sql .= " AND a.deleted_at IS NULL ";

        //condition on user to speed up
        $sql .= " AND a.user_id = " . $this->_user->getId();


        // Если указан счёт (фильтруем по счёту)
        if ((int) $currentAccount > 0) {
            $sql .= " AND (a.account_id = '" . (int) $currentAccount . "')";
        }

        // Включить в выборку баллансовые операции
        if ($accountInitial) {
            $sql .= " AND `date`< '$dateFrom' ";
        } else {
            $sql .= ' AND (`date` BETWEEN "' . $dateFrom . '" AND "' . $dateTo . '") ';
        }

        // Если указана категория (фильтр по категории)
        if ($category) {
                $sql .= " AND o.cat_id IN ($category) ";
        }

        // Если указан тип (фильтр по типу)
        if (!is_null($type) && $type >= 0) {
            if ($type == Operation::TYPE_PROFIT) {//Доход
                $sql .= " AND o.`type` = 1 ";
            } elseif ($type == Operation::TYPE_WASTE) {// Расход
                $sql .= " AND o.`type` = 0 ";
            } elseif ($type == Operation::TYPE_TRANSFER) {// Перевод со счёт на счёт
                $sql .= " AND o.`type` = 2  ";
            } elseif ($type == Operation::TYPE_TARGET) {// Перевод на финансовую цель
                $sql .= " AND 0 = 1"; // Не выбираем эти операции
            }
        }

        // Если указан фильтр по сумме
        if(!is_null($sumFrom)) {
            $sql .= " AND ABS(o.money) >= " . $sumFrom;
        }

        if(!is_null($sumTo) && (int)$sumTo !== 0) {
            $sql .= " AND ABS(o.money) <= " . $sumTo;
        }

        // Получаем операции только подтверждённые
        $sql .= " AND accepted=1 ";

        //Присоединение переводов на фин цель
        $sql .= " UNION ";
        if (!$stat) {
            $sql .= "SELECT
                t.id,
                t.user_id,
                -(t.money * tc.rate / $actualCurrency) AS money,
                DATE_FORMAT(t.date,'%d.%m.%Y'),
                t.date AS dnat,
                tt.category_id,
                t.target_id,
                tt.target_account_id,
                t.comment,
                '',
                1 AS virt,
                t.tags,
                NULL,
                NULL,
                4 as type,
                dt_create AS created_at,
                '' ";
        } else {
            $sql .= "SELECT 0 as mm ";
        }
        $sql .= "
            FROM ((target_bill t
            LEFT JOIN target tt ON t.target_id=tt.id)
            LEFT JOIN accounts ta ON ta.account_id = tt.target_account_id)
            LEFT JOIN currency tc ON tc.cur_id = ta.account_currency_id
            WHERE t.user_id = " . $this->_user->getId()
                . " AND ".
                $this->_getSearchQuery($searchField, true)
                ."tt.done=0 AND (`date` >= '{$dateFrom}' AND `date` <= '{$dateTo}') ";

        if ((int) $currentAccount > 0) {
            $sql .= " AND t.bill_id = '{$currentAccount}' ";
        }
        if(!empty($currentCategory)) {
            $sql .= " AND 0 = 1 "; // Не выбираем эти операции, т.к. у финцелей свои категории
        }

        // Если фильтр по типу - и он не фин.цель
        if($type != -1 && $type != 4) {
            // Не выбираем эти операции
            $sql .= " AND 0 = 4";
        }

        if(!is_null($sumFrom)) {
            $sql .= " AND ABS(t.money) >= " . $sumFrom;
        }

        if(!is_null($sumTo)) {
            $sql .= " AND ABS(t.money) <= " . $sumTo;
        }

        if ($stat) {
            $sql .= ")a";
        } else {
            $sql .= " ORDER BY dnat DESC, created_at DESC ";
        }

        $accounts = $this->_user->getUserAccounts();

        $params = array($sql);
        array_push($params, $currentAccount, $this->_user->getId(),
                $dateFrom, $dateTo, $this->_user->getId(), $dateFrom, $dateTo, $currentAccount,
                $currentAccount, $this->_user->getId(), $dateFrom, $dateTo);

        $operations = call_user_func_array(array($this->db, "select"), $params);

        if (!$stat) {
            // Добавляем данные, которых не хватает
            foreach ($operations as $key => $operation) {
                if ($operation['type'] <= 1) {
                    // До использования типов - игнорим ошибки
                    $operation['cat_name']   = @$listCategories[$operation['cat_id']]['cat_name'];
                    $operation['cat_parent'] = @$listCategories[$operation['cat_id']]['cat_parent'];
                }

                //Если счёт операции существует
                if (array_key_exists($operation['account_id'], $accounts)) {
                    $operation['account_name']        = $accounts[$operation['account_id']]['account_name'];
                    $operation['account_currency_id'] = $accounts[$operation['account_id']]['account_currency_id'];
                }
                // Если нет - удаляем из вывода
                else {
                    unset($operations[$key]);
                    continue;
                }

                //если фин цель то перезаписываем тот null что записан.
                if (($operation['virt']) == 1) {
                    $operation['account_currency_id'] = $accounts[$operation['account_id']]['account_currency_id'];

                    if ($operation['cat_id'] == 1)
                        $operation['cat_name'] = "Квартира";
                    if ($operation['cat_id'] == 2)
                        $operation['cat_name'] = "Автомобиль";
                    if ($operation['cat_id'] == 3)
                        $operation['cat_name'] = "Отпуск";
                    if ($operation['cat_id'] == 4)
                        $operation['cat_name'] = "Фин. подушка";
                    if ($operation['cat_id'] == 5)
                        $operation['cat_name'] = "Прочее";
                    if ($operation['cat_id'] == 6)
                        $operation['cat_name'] = "Свадьба";
                    if ($operation['cat_id'] == 7)
                        $operation['cat_name'] = "Бытовая техника";
                    if (($operation['cat_id']) == 8)
                        $operation['cat_name'] = "Компьютер";
                }

                if ($operation['type'] == 2) {
                    $operation['cat_name'] = sprintf("%s => %s", $operation['account_name'], $accounts[$operation['transfer']]['account_name']);
                }

                $operations[$key] = $operation;
            }

            $retoper = array();

            //возвращаемые операции. не возвращаем мусор связанный с удалением счетов
            foreach ($operations as $k => $v) {
                if (!($v['account_name'] == '')) {
                    $retoper[$k] = $v;
                }
            }
        } else {
            $total_money = $operations;
            if ($total_money[0]['total_money'] === null) {
                $retoper = 0;
            } else {
                $retoper = $total_money[0]['total_money'];
            }


        }
        return $retoper;
    }

    /**
     * Получает список последних операций
     * @param int $count
     * @return array
     */
    function getLastOperations($count = 10)
    {
        $operations = array();

        $sql = "SELECT
                    o.id,
                    o.user_id,
                    o.money,
                    DATE_FORMAT(o.date,'%d.%m.%Y') as `date`,
                    o.date AS dnat,
                    o.cat_id,
                    NULL as target_id,
                    o.account_id,
                    o.comment,
                    o.transfer_account_id AS transfer,
                    0 AS virt,
                    o.tags,
                    o.transfer_amount AS moneydef,
                    o.exchange_rate AS curs,
                    o.type,
                    created_at,
                    updated_at
                FROM operation o
                WHERE
                    o.user_id = " . $this->_user->getId() . "
                AND
                    o.date > 0
                AND
                    accepted=1
                AND
                    deleted_at IS NULL
            UNION
                SELECT
                    t.id,
                    t.user_id,
                    -t.money,
                    DATE_FORMAT(t.date,'%d.%m.%Y'),
                    t.date AS dnat,
                    tt.category_id,
                    t.target_id,
                    tt.target_account_id,
                    t.comment,
                    '',
                    1 AS virt,
                    t.tags,
                    NULL,
                    NULL,
                    4 as type,
                    dt_create AS created_at,
                    dt_update AS updated_at
                FROM target_bill t
                LEFT JOIN target tt
                ON
                    t.target_id=tt.id
                WHERE
                    t.user_id = " . $this->_user->getId() . "
                AND
                    tt.done=0
                ORDER BY
                    updated_at DESC
                LIMIT " . (int) $count;

        $operations = $this->db->select($sql);

        return $operations;
    }


    /**
     * @deprecated
     * @FIXME Дописать комментарии к функции
     */
    function getCurrency()
    {
        $SourceId = (int) $_POST['SourceId']; // Ид счёта на который нужно перевести
        $TargetId = (int) $_POST['TargetId']; // Ид текущего счёта
        $aTarget = $aSource = array();
        $curr = Core::getInstance()->currency;
        foreach($this->_user->getUserAccounts() as $val) {
            if($val['account_id'] == $SourceId) {
                $aTarget[$val['account_currency_id']] = $curr[$val['account_currency_id']]['name'];
            }
            if($val['account_id'] == $TargetId) {
                $aSource[$val['account_currency_id']] = $curr[$val['account_currency_id']]['name'];
            }
        }

        if(key($aSource) != key($aTarget)) {
            // Если у нас простое сравнение с рублём
            if (key($aTarget) == 1 || key($aSource) == 1) {
                $course = round($curr[key($aSource)]['value'] / $curr[key($aTarget)]['value'], 4);
            } else {
                //@FIXME Придумать алгоритм для конвертации между различными валютами
                $course = 0;
            }
            return $course;
        }

    }


    public function getOperation($userId, $operationId)
    {
        // Запрос выбирает из операций и переводов на финцели
        $sql = "
            SELECT
                o.id,
                o.user_id,
                o.money as amount,
                DATE_FORMAT(o.date,'%d.%m.%Y') as `date`,
                o.date AS dnat,
                o.cat_id as category,
                NULL as target,
                o.account_id,
                o.comment,
                o.transfer_account_id AS transfer,
                0 AS virt,
                o.account_id as account,
                o.tags,
                o.transfer_amount AS moneydef,
                o.exchange_rate AS curs,
                o.type,
                created_at
            FROM operation o
            WHERE
                o.user_id = ?
            AND
                o.id = ?
            UNION
            SELECT
                t.id,
                t.user_id,
                t.money as amount,
                DATE_FORMAT(t.date,'%d.%m.%Y'),
                t.date AS dnat,
                tt.category_id as category,
                t.target_id as target,
                tt.target_account_id,
                t.comment,
                '',
                1 AS virt,
                t.bill_id as account,
                t.tags,
                NULL,
                NULL,
                4 as type,
                dt_create AS created_at
            FROM target_bill t
            LEFT JOIN target tt
            ON
                t.target_id=tt.id
            WHERE
                t.user_id = ?
            AND
                t.id = ?";

        $operation = $this->db->selectRow($sql, (int) $userId, (int) $operationId, (int) $userId, (int) $operationId);

        return $operation;

    }

    /**
     * Возвращает количество операций по выбранному счёту
     * @FIXME Переписать на получение количества операций по всем счетам, что бы не дёргать по каждому счёту отдельно
     *
     * @param   int $accountId Ид счёта
     * @return  int
     */
    public function getNumOfOperationOnAccount($accountId)
    {
        $sql = "
            SELECT count(*) as op_count FROM operation
            WHERE account_id=?
                AND type <> ?
                AND deleted_at IS NULL
                AND updated_at BETWEEN ADDDATE(NOW(), INTERVAL -1 MONTH) AND NOW()
            ";
        $count = $this->db->selectRow($sql, (int) $accountId, Operation::TYPE_BALANCE);

        return $count['op_count'];
    }

    /**
     * Обновляет операцию в БД
     *
     * @param oldUser   $user
     * @param int       $id Ид операции
     * @param array     $values
     * @return bool     true - в случае успеха, false - если была ошибка
     */
    private function _updateOperation(oldUser $user, $id, array $values = array())
    {

        $default = array(
            'updated_at' => date('Y-m-d H:i:s'),
        );

        if (empty($values['tags'])) {
            $tags = array();
        } else if (!is_array($values['tags'])) {
            $tags = explode(', ', $values['tags']);
        } else {
            $tags = $values['tags'];
        }
        $this->_updateTags($id, $tags);

        $values = array_merge($default, $values);
        $values['tags'] = implode(', ', $tags);
        $sets = "";

        foreach ($values as $k => $v) {
            if ($sets) {
                $sets .= ", ";
            }

            $sets .= sprintf('`%s` = ', $k);

            if ($v === null) {
                $sets .= "null";
            } else {
                $sets .= sprintf("'%s'", addslashes($v));
            }
        }

        $sql = "UPDATE `operation` SET {$sets} WHERE user_id=? AND id=?";

        return (bool)$this->db->query($sql, $user->getId(), $id);
    }

    /**
     * Добавляет операцию в БД
     *
     * @param array $values
     * @return int Возвращает id добавленной операции
     */
    private function _addOperation(array $values = array(), array $notifications = array())
    {

        $default = array(
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        );

        $values = array_merge($default, $values);

        // Теги, пля
        $tags = array();
        if (isset($values['tags'])) {
            if (!is_array($values['tags'])) {
                $tags = explode(', ', $values['tags']);
            } else {
                $tags = $values['tags'];
                $values['tags'] = implode(', ', $tags);
            }
        }

        $sql = "INSERT INTO `operation` (" . self::_wrapKey($values) . ") VALUES (" . self::_wrapVal($values) . ")";

        $operationId = $this->db->query($sql);

        // Если есть теги, то добавляем и их тоже
        if ($tags) {
            $this->_updateTags($operationId, $tags);
        }

        // Если нужно напоминание, добавляем напоминание:
        if ($notifications) {
            $this->_addNotifications($operationId, $values['date'], $notifications, $this->_user);
        }

        return $operationId;
    }

    /**
     * ???
     */
    private static function _wrapKey($props)
    {
        $keys = array_keys($props);
        return sprintf('`%s`', implode('`, `', $keys));
    }


    /**
     * Добавление уведомлений
     *
     * @param array $notifications массив с настройками уведомлений
     */
    private function _addNotifications($operationId, $date, $notifications, oldUser $user)
    {
        $this->_deleteNotifications($operationId);

        if ($notifications) {
            // Смещение в секундах относительно серверного времени
            $offset = ($user->getUserProps('time_zone_offset') - round(date("O") / 100, 2)) * 3600;

            $operation_ts = strtotime($date);

            // Email уведомления
            if ($notifications['mailEnabled']) {
                $notify_dt = date("Y-m-d H:i:s", strtotime("-{$notifications['mailDaysBefore']} days", $operation_ts) + $notifications['mailHour'] * 3600 + $notifications['mailMinutes'] * 60 - $offset);
                $type = 1;
                $this->_addNotificationRow($operationId, $type, $notify_dt);
            }

            if ($notifications['smsEnabled']) { // SMS уведомления
                $notify_dt = date("Y-m-d H:i:s", strtotime("-{$notifications['smsDaysBefore']} days", $operation_ts) + $notifications['smsHour'] * 3600 + $notifications['smsMinutes'] * 60 - $offset);
                $type = 0;
                $this->_addNotificationRow($operationId, $type, $notify_dt);
            }
        }
    }


    /**
     * Добавить запись об уведомлении
     *
     * @param  int    $operationId
     * @param  int    $type
     * @param  string $startDateTime
     * @return void
     */
    private function _addNotificationRow($operationId, $type, $startDateTime)
    {
        $currentDT = date('Y-m-d H:i:s');
        $sql = "
            INSERT INTO operation_notifications
            SET
                operation_id = ?,
                type = ?,
                schedule = ?,
                is_sent = 0,
                is_done = 0,
                fail_counter = 0,
                created_at = ?,
                updated_at = ?
        ";
        $this->db->query($sql, (int) $operationId, (int) $type, $startDateTime, $currentDT, $currentDT);
    }


    /**
     * Удаление уведомлений, привязанных к операции
     *
     * @param int $operationId id операции
     */
    private function _deleteNotifications($operationIds)
    {
        $operationIds = (array) $operationIds;
        $sql = "DELETE FROM operation_notifications WHERE operation_id IN (?a) AND is_done = 0";
        $this->db->query($sql, $operationIds);
    }


    /**
     * ???
     */
    private static function _wrapVal($props)
    {
        $result = "";
        foreach($props as $value) {
            if (!empty($result)) {
                $result .= ", ";
            }
            if ($value === null) {
                $result .= "null";
            } else {
                $result .= sprintf("'%s'", $value);
            }
        }
        return $result;
    }


    /**
     * Обновляет теги для операции
     *
     * @param int $OperId
     * @param array $tags
     * @return bool
     */
    private function _updateTags($OperId, array $tags = array())
    {
        $this->db->query('DELETE FROM tags WHERE oper_id=? AND user_id=?', $OperId, $this->_user->getId());

        if ($tags) {
            $sql = "";
            foreach ($tags as $tag) {
                if (!empty($sql)) {
                    $sql .= ',';
                }
                $sql .= "(" . $this->_user->getId() . "," . $OperId . ",'" . addslashes($tag) . "')";
            }
            return (bool) $this->db->query("INSERT INTO `tags` (`user_id`, `oper_id`, `name`) VALUES " . $sql);
        }
    }


    /**
     * Получить все дочерние категории и родительскую
     *
     * @param int           $parentCategory Ид родительской категории
     * @param array mixed   $listCategories Массив с категориями
     * @return string|bool Возвращает строку с ид, разделённую запятыми или false
     */
    private function _getRelatedCategories($parentCategory, $listCategories)
    {
        if (!array_key_exists($parentCategory, $listCategories))
            return false;

        $relatedCategories = array();

        $relatedCategories[] = $parentCategory;

        foreach ($listCategories as $category) {
            if ($category['cat_parent'] == $parentCategory) {
                $relatedCategories[] = $category['cat_id'];
            }
        }

        return implode(',', $relatedCategories);
    }


    /**
     * Формирует строку запроса для поиска по тегам и комментариям
     *
     * @param string $searchField
     * @param bool
     * @return string
     */
    private function _getSearchQuery($searchField, $target = false)
    {
        $searchSql = "";
        if (!empty($searchField)) {
            $searchWords = explode(",", $searchField);
            foreach ($searchWords as $word) {
                if (!empty($searchSql)) {
                    $searchSql .= " OR ";
                }

                if ($target) {
                    $searchSql .= sprintf(" t.comment LIKE '%%%s%%' ", $word);
                } else {
                    $searchSql .= sprintf("o.comment LIKE '%%%s%%' OR o.tags LIKE '%%%s%%' ", $word, $word);
                }
            }
            if (!empty($searchSql)) {
                $searchSql  = "({$searchSql}) AND ";
            }
        }

        return $searchSql;
    }


    /**
     * Удаляет все операции по указанной категории
     *
     * @param oldUser $user
     * @param int $catId
     */
    function deleteOperationsByCategory(oldUser $user, $catId) {
        $sql = "SELECT id FROM operation WHERE user_id = ? AND cat_id = ?";
        $operations = $this->db->selectCol($sql, $user->getId(), $catId);

        if (count($operations)>0) {
            $this->db->query("BEGIN;");
            foreach($operations as $opId) {
                $this->deleteOperation($opId);
            }
            $this->db->query("COMMIT;");
        }
        return (int)count($operations);
    }


    /**
     * Конвертирует сумму операции
     *
     * @param   int   $fromAccount
     * @param   int   $toAccount
     * @param   float $amount
     * @param   float $convert
     * @return  float
     */
    function _convertAmount($fromAccount, $toAccount, $amount, $convert)
    {
        $accounts    = $this->_user->getUserAccounts();

        // Если не указана сконвертированная сумма (в ПДА такое может быть)
        if ($convert)
            return abs($convert);

        if (!isset($accounts[$fromAccount]) || !isset($accounts[$toAccount]))
            return false;

        $curFromId   = $accounts[$fromAccount]['account_currency_id'];
        $curTargetId = $accounts[$toAccount]['account_currency_id'];

        // Если перевод мультивалютный
        if ($curFromId != $curTargetId) {
            if ($convert == 0) {
                $currensys = $this->_user->getUserCurrency();

                // приводим сумму к пром. валюте
                $convert = $amount / $currensys[$curTargetId]['value'];
                // .. и к валюте целевого счёта
                $convert = $convert * $currensys[$curFromId]['value'];
            }
        }

        $convert = $convert ? $convert : $amount;

        return abs($convert);
    }

}
