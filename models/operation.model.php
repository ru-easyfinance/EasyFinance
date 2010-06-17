<?php if (!defined('INDEX')) trigger_error("Index required!", E_USER_WARNING);

/**
 * Класс модели для управления операциями
 *
 * @category operation
 * @author Max Kamashev (ukko) <max.kamashev@gmail.com>
 * @copyright http://easyfinance.ru/
 * @version SVN $Id$
 */
class Operation_Model {

    /**
     * Ссылка на экземпляр класса базы данных
     * @var DbSimple_Mysql
     */
    private $db = null;

    /**
     * Ссылка на экземпляр класса пользователя
     * @var User
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
    function __construct(oldUser $user = null) {
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
    function load() {
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
    function save() {
        $_SESSION['user_money'] = $this->user_money;
        $_SESSION['account_money'] = $this->account_money;
        $_SESSION['total_sum'] = $this->total_sum;
    }

    /**
     * Проверяет валидность введённых данных
     * @param array $params
     * @return array
     */
    public function checkData(array $operation = array()) {
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
            $validated['tags'] = null;
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
     * @param float $money
     * @param date $date
     * @param int $category
     * @param int $drain
     * @param string $comment
     * @param int $account
     * @return array
     */
    function checkExistance($money = 0, $date = '', $category = 0, $drain = 0, $comment = '', $account = 0) {
        $last = $this->db->select("
                SELECT id
                FROM operation
                WHERE user_id=? AND money=? AND date=?
                    AND cat_id=? AND drain=? AND comment=? AND account_id=?
                    AND created_at BETWEEN ADDDATE(NOW(), INTERVAL -2 SECOND) AND NOW()",
                        $this->_user->getId(), $money, $date, $category, $drain, $comment, $account);
        return $last;
    }

    /**
     * Регистрирует новую транзакцию
     * @param float  $money      Сумма транзакции
     * @param string $date       Дата транзакции в формате Y.m.d
     * @param int    $drain      Доход или расход. Устаревшее, но на всякий случай указывать надо 0 - расход, 1 - доход
     * @param string $comment    Комментарий транзакции
     * @param int    $account_id Ид счета
     *
     * @return int $id Если
     */
    function add($money = 0, $date = '', $category = 0, $drain = 0, $comment = '', $account = 0, $tags = null) {


        // Если операция новая, и отправлена не случайно, то продолжаем, иначе возвраты
        $check = $this->checkExistance($money, $date, $category, $drain, $comment, $account);
        if ($check) {
            return $check;
        }

        if (is_null($tags)) {
            $tags = array();
        }

        $sql = 'INSERT INTO `operation` (`user_id`, `money`, `date`, `cat_id`, `account_id`, `drain`, `type`,
            `comment`, `tags`, `created_at`, `updated_at`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())';

        $this->db->query($sql, $this->_user->getId(), $money, $date, $category, $account, $drain, !$drain,
                $comment, implode(', ', $tags));

        $operationId = mysql_insert_id();

        // Если есть теги, то добавляем и их тоже
        if ($tags) {
            $sql = "";
            foreach ($tags as $tag) {
                if (!empty($sql)) {
                    $sql .= ',';
                }
                $sql .= "(" . $this->_user->getId() . "," . (int) $operationId . ",'" . addslashes($tag) . "')";
            }

            $this->db->query('INSERT INTO `tags` (`user_id`, `oper_id`, `name`) VALUES ' . $sql);
        }

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
    function addSome($operations_array = array()) {
        $sql = '';
        foreach ($operations_array as $operation) {
            if (!empty($sql))
                $sql .= ',';
            $sql .= "('"
                . $this->_user->getId() . "','"
                . ( ( $operation['type'] == 0 ) ?
                    ( -1 * abs($operation['amount']) )
                    : $operation['amount'] ) . "','"
                . $operation['date'] . "','"
                . $operation['category'] . "','"
                . $operation['account'] . "','"
                . $operation['drain'] . "','"
                . !$operation['drain'] . "','"
                . $operation['comment'] . "','"
                . $operation['tags'] . "','"
                . $operation['accepted'] . "', '"
                . $operation['chain'] . "', NOW(), NOW())";
        }

        if (!empty($sql)) {

            $sql = 'INSERT INTO `operation` (`user_id`, `money`, `date`, `cat_id`, `account_id`,
                `drain`, `type`, `comment`, `tags`, `accepted`, `chain_id`, `created_at`, `updated_at`) VALUES ' . $sql;

            $this->db->query($sql);
        } else {
            return false;
        }

        // Обновляем данные о счетах пользователя
        $this->_user->initUserAccounts();
        $this->_user->save();

        $this->save();
        return count($operations_array);
    }

    /**
     * Добавляет трансфер с одного на другой счёт
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
    function editTransfer($id=0, $money = 0, $convert = 0, $date = '', $account = 0, $toAccount=0, $comment = '', $tags = null, $accepted = null) {

        if ($tags) {

            $this->db->query('DELETE FROM tags WHERE oper_id=? AND user_id=?', $id, $this->_user->getId());

            if ($accepted) {
                $sql = "UPDATE operation
                    SET money=?, date=?, account_id=?, transfer=?, comment=?, tags=?, accepted=1
                    WHERE user_id = ? AND id = ?";
            } else {
                $sql = "UPDATE operation SET money=?, date=?, account_id=?, transfer=?, comment=?, tags=?
                    WHERE user_id = ? AND id = ?";
            }

            $this->db->query($sql, $money, $date, $account, $toAccount, $comment,
                    implode(', ', $tags), $this->_user->getId(), $id);

            $sql = "";
            foreach ($tags as $tag) {
                if (!empty($sql)) {
                    $sql .= ',';
                }
                $sql .= "(" . $this->_user->getId() . "," . $id . ",'" . addslashes($tag) . "')";
            }
            $this->db->query("INSERT INTO `tags` (`user_id`, `oper_id`, `name`) VALUES " . $sql);
        } else {

            if ($accepted) {
                $sql = "UPDATE operation
                    SET money=?, date=?, account_id=?, transfer=?, comment=?, tags=?, imp_id=?, accepted=1
                    WHERE user_id = ? AND id = ?";
            } else {
                $sql = "UPDATE operation
                    SET money=?, date=?, account_id=?, transfer=?, comment=?, tags=?, imp_id=?
                    WHERE user_id = ? AND id = ?";
            }

            //если есть смежная запись, т.е. редактируем перевод
            $next = $this->db->query("SELECT id FROM operation WHERE tr_id=?", $id);
            if ($next) {
                if (!empty($tags)) {

                }
                //Перевод "С"
                $this->db->query($sql, ( ABS($money) * -1), $date, $account, $toAccount, $comment,
                        '', NULL, $this->_user->getId(), $id);

                // Если менялась валюта при переводе
                if ($convert) {
                    //перевод на
                    $this->db->query($sql, $convert, $date, $toAccount, $account, $comment,
                            '', $money, $this->_user->getId(), $next[0]['id']);
                } else {
                    //перевод на
                    $this->db->query($sql, $money, $date, $toAccount, $account, $comment,
                            '', $money, $this->_user->getId(), $next[0]['id']);
                }
            } else {// иначе делаем перевод из доходной/расходной операции
                if ($accepted) {
                    $sql = "UPDATE operation
                        SET money=?, date=?, account_id=?, transfer=?, comment=?, tags=?, imp_id=?,
                        cat_id=0, tr_id=0, accepted=1
                        WHERE user_id = ? AND id = ?";
                } else {
                    $sql = "UPDATE operation
                        SET money=?, date=?, account_id=?, transfer=?, comment=?, tags=?, imp_id=?, cat_id=0, tr_id=0
                        WHERE user_id = ? AND id = ?";
                }


                //перевод с
                $this->db->query($sql, -$money, $date, $account, $toAccount, $comment, '',
                        NULL, $this->_user->getId(), $id);
                if ($accepted) {
                    $sql = "INSERT INTO operation
                                (user_id, money, date, cat_id, account_id, tr_id, comment, transfer, type, imp_id,
                                    accepted, created_at, updated_at)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 2, ?, 1, NOW(), NOW())";
                } else {
                    $sql = "INSERT INTO operation
                                (user_id, money, date, cat_id, account_id, tr_id, comment, transfer, type, imp_id,
                                    created_at, updated_at)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 2, ?, NOW(), NOW())";
                }
                $this->db->query($sql, $this->_user->getId(), $money, $date, -1, $toAccount, $id,
                        $comment, $account, NULL);
            }
        }
        $this->save();
        return '[]';
    }


    /**
     * Добавляет несколько однообразных переводов между счетами
     * @param array $operations_array Calendar_Event $event
     * @return int
     */
    function addSomeTransfer($operations_array) {

        $accounts = $this->_user->getUserAccounts();
        $currensys = $this->_user->getUserCurrency();

        // @FIXME Предупреждаю честно, код далее может несколько загнуть сервер. Нужно его оптимизировать
        foreach ($operations_array as $value) {

            $curFromId = $accounts[$value['account']]['account_currency_id'];
            $curTargetId = $accounts[$value['toAccount']]['account_currency_id'];

            // Если перевод мультивалютный
            if ($curFromId != $curTargetId) {
                // Если нет сконвертированной суммы (так бывает в пда) производим вычисления через рубль
                if ((float) $value['convert'] === 0) {

                    // приводим сумму к пром. валюте
                    $value['convert'] = $value['amount'] / $currensys[$curTargetId]['value'];
                    // .. и к валюте целевого счёта
                    $value['convert'] = $value['convert'] * $currensys[$curFromId]['value'];
                }

                // Создаём операцию откуда переводим
                $sql = "INSERT INTO operation
                    (user_id, money, date, cat_id, account_id, tr_id, comment, transfer, drain, type,
                    exchange_rate, chain_id, accepted, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, 2, ?, ?, 0, NOW(), NOW())";
                $this->db->query($sql,
                        $this->_user->getId(),
                        -$value['amount'],
                        $value['date'],
                        -1,
                        $value['account'],
                        0,
                        $value['comment'],
                        $value['toAccount'],
                        $value['currency'],
                        $value['chain']);

                $last_id = mysql_insert_id();

                // Создаём операцию куда переводим
                $sql = "INSERT INTO operation
                (user_id, money, date, cat_id, account_id, tr_id, comment, transfer, drain, type,
                imp_id, exchange_rate, chain_id, accepted, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, 2, ?, ?, ?, 0, NOW(), NOW())";

                $this->db->query($sql,
                        $this->_user->getId(),
                        $value['convert'],
                        $value['date'],
                        -1,
                        $value['toAccount'],
                        $last_id,
                        $value['comment'],
                        $value['account'],
                        $value['amount'],
                        $value['currency'],
                        $value['chain']);

                // Если перевод в разрезе одной валюты
            } else {

                $sql = "INSERT INTO operation
                    (user_id, money, date, cat_id, account_id, tr_id, comment, transfer, type,
                    chain_id, accepted, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 2, ?, 0, NOW(), NOW())";

                $this->db->query($sql,
                        $this->_user->getId(),
                        -$value['amount'],
                        $value['date'],
                        -1,
                        $value['account'],
                        0,
                        $value['comment'],
                        $value['toAccount'],
                        $value['chain']);

                $last_id = mysql_insert_id();

                $sql = "INSERT INTO operation
                    (user_id, money, date, cat_id, account_id, tr_id, comment, transfer, type,
                    imp_id, chain_id, accepted, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 2, ?, ?, 0, NOW(), NOW())";

                $this->db->query($sql, $this->_user->getId(),
                        $value['amount'],
                        $value['date'],
                        -1,
                        $value['toAccount'],
                        $last_id,
                        $value['comment'],
                        $value['account'],
                        $value['amount'],
                        $value['chain']);
            }
        }

        $this->_user->initUserAccounts();
        $this->_user->save();

        return count($operations_array);
    }

    /**
     * Добавляем перевод со счёта на счёт
     * @param float  $money
     * @param        $convert
     * @param float  $curr
     * @param date   $date
     * @param int    $from_account
     * @param int    $to_account
     * @param string $comment
     * @param string $tags
     * @return <type>
     */
    function addTransfer($money, $convert, $curr, $date, $from_account, $to_account, $comment, $tags) {

        $accounts = $this->_user->getUserAccounts();
        $curFromId = $accounts[$from_account]['account_currency_id'];
        $curTargetId = $accounts[$to_account]['account_currency_id'];

        // Если перевод мультивалютный
        if ($curFromId != $curTargetId) {
            // Если пришла сконвертированная сумма
            if ($convert != 0) {

                // Если нет - производим вычисления через рубль
            } else {
                $currensys = $this->_user->getUserCurrency();

                // приводим сумму к пром. валюте
                $convert = $money / $currensys[$curTargetId]['value'];
                // .. и к валюте целевого счёта
                $convert = $convert * $currensys[$curFromId]['value'];
            }

            $drain_money = $money * -1;

            // Создаём операцию откуда переводим
            $sql = "INSERT INTO operation
                (user_id, money, date, cat_id, account_id, tr_id, comment, transfer, drain, type,
                exchange_rate, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, 2, ?, NOW(), NOW())";
            $this->db->query($sql, $this->_user->getId(), -$money, $date, null, $from_account, 0,
                    $comment, $to_account, $curr);

            $last_id = mysql_insert_id();

            // Создаём операцию куда переводим
            $sql = "INSERT INTO operation
            (user_id, money, date, cat_id, account_id, tr_id, comment, transfer, drain, type,
            imp_id, exchange_rate, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, 2, ?, ?, NOW(), NOW())";
            $this->db->query($sql, $this->_user->getId(), $convert, $date, null, $to_account, $last_id,
                    $comment, $from_account, $money, $curr, $curTargetId);
        } else {

        $sql = "INSERT INTO operation
            (user_id, money, date, cat_id, account_id, tr_id, comment, transfer, type, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 2, NOW(), NOW())";

            $last_id = $this->db->query($sql, $this->_user->getId(), -$money, $date, null, $from_account, 0,
                            $comment, $to_account);

            $last_id = mysql_insert_id();

        $sql = "INSERT INTO operation
            (user_id, money, date, cat_id, account_id, tr_id, comment, transfer, type, imp_id, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 2, ?, NOW(), NOW())";
        $this->db->query($sql, $this->_user->getId(), $money, $date, null, $to_account, $last_id,
            $comment, $from_account, $money);
            $last_id2 = mysql_insert_id();
        }
        $this->_user->initUserAccounts();
        $this->_user->save();
        return $last_id;
    }

    /**
     * Редактирует транзакцию
     * @param int    $id         Ид транзакции
     * @param float  $money      Сумма транзакции
     * @param string $date       Дата транзакции в формате Y.m.d
     * @param int    $drain      Доход или расход. Устаревшее, но на всякий случай указывать надо 0 - расход, 1 - доход
     * @param string $comment    Комментарий транзакции
     * @param int    $account_id Ид счета
     * @param int    $accepted
     *
     * @return bool true - Регистрация прошла успешно
     */
    function edit($id=0, $money = 0, $date = '', $category = null, $drain = 0, $comment = '', $account = 0, $tags = null, $accepted=null) {
        if (!$category) {
            $category = null;
        }

        // Если есть теги, то добавляем и их тоже
        if ($tags) {
            $this->db->query('DELETE FROM tags WHERE oper_id=? AND user_id=?', $id, $this->_user->getId());
            if ($accepted) {
                $sql = "UPDATE operation SET
                    money=?, date=?, cat_id=?, account_id=?, drain=?, comment=?, tags=?, accpeted=?
                    WHERE user_id = ? AND id = ?";
                $this->db->query($sql, $money, $date, $category, $account, $drain, $comment,
                        implode(', ', $tags), $accepted, $this->_user->getId(), $id);
            } else {
                $sql = "UPDATE operation SET
                    money=?, date=?, cat_id=?, account_id=?, drain=?, comment=?, tags=?
                    WHERE user_id = ? AND id = ?";
                $this->db->query($sql, $money, $date, $category, $account, $drain, $comment,
                        implode(', ', $tags), $this->_user->getId(), $id);
            }

            $sql = "";
            foreach ($tags as $tag) {
                if (!empty($sql)) {
                    $sql .= ',';
                }
                $sql .= "(" . $this->_user->getId() . "," . $id . ",'" . addslashes($tag) . "')";
            }
            $this->db->query("INSERT INTO `tags` (`user_id`, `oper_id`, `name`) VALUES " . $sql);
        } else {
            if ($accepted) {
                $sql = "UPDATE operation SET
                    money=?, date=?, cat_id=?, account_id=?, drain=?, comment=?, accepted=?
                    WHERE user_id = ? AND id = ?";
                $this->db->query($sql, $money, $date, $category, $account, $drain, $comment,
                        $accepted, $this->_user->getId(), $id);
            } else {
                $sql = "UPDATE operation SET money=?, date=?, cat_id=?, account_id=?, drain=?, comment=?
                    WHERE user_id = ? AND id = ?";
                $this->db->query($sql, $money, $date, $category, $account, $drain, $comment, $this->_user->getId(), $id);
            }
        }
        // Обновляем данные о счетах пользователя
        $this->_user->initUserAccounts();
        //$this->selectMoney($user_id);
        $this->save();
        return '[]';
    }

    /**
     * Удаляет указанную операцию
     *
     * @param int id
     * @return bool
     */
    function deleteOperation($id = 0) {
        $userId = $this->_user->getId();

        //Получаем ID смежной записи (в случае, если это перевод)
        $tr_id = $this->db->selectCell('SELECT * FROM operation WHERE tr_id = ? AND user_id = ?', $id, $userId);

        if ($tr_id === null) {
            $sql = "UPDATE operation o SET deleted_at=NOW() WHERE user_id = ? AND id = ?";
        } else {
            $sql = "UPDATE operation o SET deleted_at=NOW() WHERE user_id = ? AND (id = ? OR tr_id = ?)";
        }
        return (bool) $this->db->query($sql, $userId, $id, $tr_id);
    }

    /**
     * Удаляет операцию перевода на финцель
     * @param int $id
     * @return bool
     */
    function deleteTargetOperation($id=0) {
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
     * @param date $dateFrom        Дата с которой показывать операции
     * @param date $dateTo          Дата до какой показывать операции
     * @param int $currentCategory  Ид категории по которой отобрать операции
     * @param int $currentAccount   Ид счёта по которому отобрать операции
     * @param int $type             Тип операции (0 - Расход, 1 - Доход, 2 - Перевод, 4 - Перевод на фин.цель)
     * @param float $sumFrom        Минимальная сумма для показа операций
     * @param float $sumTo          Максимальная сумма для показа операций
     * @param string $searchField  Поле поиска по комментариям и меткам
     * @param bool $stat = false
     * @param bool $acc_initial
     * @return array mixed
     */
    function getOperationList($dateFrom, $dateTo, $currentCategory, $currentAccount, $type, $sumFrom, $sumTo, $searchField = '', $stat = false, $acc_initial = false) {
        if ($sumTo == 0) {
            $sumTo = null;
        }

        // Подготавливаем фильтр по родительским категориям
        $categorys = $this->_user->getUserCategory();
        $cat_in = '';

        foreach ($categorys as $category) {
            if ($category['cat_parent'] == $currentCategory) {
                if ($cat_in) {
                    $cat_in .= ',';
                }

                $cat_in .= $category['cat_id'];
            }

            if ($cat_in)
                $cat_in .= ',';
            $cat_in .= $currentCategory;
        }

        // Фильтр по меткам и комментариям
        $search_sql = "";
        if (!empty($searchField)) {
            $search_words = explode(" ", $searchField);
            $search_sql = '(';
            foreach ($search_words as $word) {
                $search_sql .= "o.comment LIKE ? OR o.tags LIKE ? OR ";
            }
            if ($search_sql != '(') {
                $search_sql = substr($search_sql, 0, strlen($search_sql) - 4) . ") AND ";
            }
        }

        // Конвертация валют
        $act_curr = sfConfig::get('ex')->getRate($this->_user->getUserProps('user_currency_default'));
        if (!$stat) {
            // Выборка операций пользователя
            $sql = "SELECT
                        o.id,
                        o.user_id,
                        o.money,
                        DATE_FORMAT(o.date,'%d.%m.%Y') as `date`,
                        o.date AS dnat,
                        o.cat_id,
                        NULL as target_id,
                        o.account_id,
                        o.drain,
                        o.comment,
                        o.transfer,
                        o.tr_id, 0 AS virt,
                        o.tags,
                        (o.money*(CASE WHEN rate = 0 THEN 1 ELSE rate END)/$act_curr) as moneydef,
                        o.exchange_rate AS curs,
                        o.type,
                        o.created_at,
                        o.source_id AS source ";
            //o.imp_id AS moneydef,
        } else {
            $sql = "SELECT
                        sum(mm) as total_money
                    FROM (SELECT sum(money*(CASE WHEN rate = 0 THEN 1 ELSE rate END)) as mm ";
        }
        $sql .= "FROM accounts a, currency c, operation o
                    WHERE o.account_id = a.account_id AND a.account_currency_id  = c.cur_id AND
                          $search_sql
                          o.user_id = " . $this->_user->getId();

        // Добавляем фильтр для обязательного скрытия удалённых
        $sql .= " AND o.deleted_at IS NULL ";

        // Если указан счёт (фильтруем по счёту)
        if ((int) $currentAccount > 0) {
            $sql .= " AND o.account_id = '" . (int) $currentAccount . "' ";
        }

        if (!$acc_initial)
            $sql .= ' AND (`date` BETWEEN "' . $dateFrom . '" AND "' . $dateTo . '") ';
        else
            $sql .= " AND `date`< '$dateFrom' ";

        // Если указана категория (фильтр по категории)
        if (!empty($currentCategory)) {

            if ($categorys[$currentCategory]['cat_parent'] == 0) {

                $sql .= ' AND o.cat_id IN (' . $cat_in . ') ';
            } else {

                $sql .= ' AND o.cat_id = "' . $currentCategory . '" ';
            }
        }

        // Если указан тип (фильтр по типу)
        if ($type >= 0) {
            if ($type == Operation::TYPE_PROFIT) {//Доход
                $sql .= " AND o.drain = 0 AND o.transfer = 0 ";
            } elseif ($type == Operation::TYPE_WASTE) {// Расход
                $sql .= " AND o.drain = 1 AND o.transfer = 0 ";
            } elseif ($type == Operation::TYPE_TRANSFER) {// Перевод со счёт на счёт
                $sql .= " AND o.transfer > 0 ";
            } elseif ($type == Operation::TYPE_TARGET) {// Перевод на финансовую цель
                $sql .= " AND 0 = 1"; // Не выбираем эти операции
            }
        }

        // Если указан фильтр по сумме
        if (!is_null($sumFrom)) {
            $sql .= " AND ABS(o.money) >= " . $sumFrom;
        }
        if (!is_null($sumTo)) {
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
                -t.money,
                DATE_FORMAT(t.date,'%d.%m.%Y'),
                t.date AS dnat,
                tt.category_id,
                t.target_id,
                tt.target_account_id,
                1,
                t.comment,
                '',
                '',
                1 AS virt,
                t.tags,
                NULL,
                NULL,
                4 as type,
                dt_create AS created_at,
                '' ";
        } else {
            $sql .= "SELECT sum(money) as mm ";
        }
        $sql .= "
            FROM target_bill t
            LEFT JOIN target tt ON t.target_id=tt.id
            WHERE t.user_id = " . $this->_user->getId()
                . " AND tt.done=0 AND (`date` >= '{$dateFrom}' AND `date` <= '{$dateTo}') ";

        if ((int) $currentAccount > 0) {
            $sql .= " AND t.bill_id = '{$currentAccount}' ";
        }
        if (!empty($currentCategory)) {
            $sql .= " AND 0 = 1 "; // Не выбираем эти операции, т.к. у финцелей свои категории
        }

        // Если фильтр по типу - и он не фин.цель
        if ($type != -1 && $type != 4) {
            // Не выбираем эти операции
            $sql .= " AND 0 = 4";
        }

        if (!is_null($sumFrom)) {
            $sql .= " AND ABS(t.money) >= " . $sumFrom;
        }

        if (!is_null($sumTo)) {
            $sql .= " AND ABS(t.money) <= " . $sumTo;
        }

        if ($stat) {
            $sql .= ")a";
        } else {
            $sql .= " ORDER BY dnat DESC, created_at DESC ";
        }

        $accounts = $this->_user->getUserAccounts();

        $params = array($sql);
        if (!empty($searchField)) {
            foreach ($search_words as $word) {
                array_push($params, "%$word%", "%$word%");
            }
        }

        array_push($params, $currentAccount, $this->_user->getId(),
                $dateFrom, $dateTo, $this->_user->getId(), $dateFrom, $dateTo, $currentAccount,
                $currentAccount, $this->_user->getId(), $dateFrom, $dateTo);

        $operations = call_user_func_array(array($this->db, "select"), $params);


        if (!$stat) {
            // Добавляем данные, которых не хватает
            foreach ($operations as $key => $operation) {
                if ($operation['type'] <= 1) {
                    // До использования типов - игнорим ошибки
                    $operation['cat_name'] = @$categorys[$operation['cat_id']]['cat_name'];
                    $operation['cat_parent'] = @$categorys[$operation['cat_id']]['cat_parent'];
                }

                //Если счёт операции существует
                if (array_key_exists($operation['account_id'], $accounts)) {
                    $operation['account_name'] = $accounts[$operation['account_id']]['account_name'];
                    $operation['account_currency_id'] = $accounts[$operation['account_id']]['account_currency_id'];
                }
                // Если нет - удаляем из вывода
                else {
                    unset($operations[$key]);
                    continue;
                }

                //хак для журнала операций. присылаю tr_id = null для не переводов
                if ((int) $operation['tr_id'] == 0 && (int) $operation['transfer'] == 0) {
                    $operation['tr_id'] = null;
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
                //@todo переписать запрос про финцель, сделать отже account_id и убрать эти строчки. +посмотреть весь код где это может использоваться

                if ($operation['transfer']) {
                    $operation['cat_name'] = "Отправлено на счёт '"
                            . $accounts[$operation['transfer']]['account_name'] . "'";

                    if ($operation['tr_id']) {
                        $operation['cat_name'] = "Отправлено со счёта '"
                                . $accounts[$operation['transfer']]['account_name'] . "'";
                    }
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
            $retoper = ($total_money[0]['total_money'] == null) ? 0 : $total_money[0]['total_money'] / sfConfig::get('ex')->getRate(Core::getInstance()->user->getUserProps('user_currency_default'));
//            $retoper = ($total_money[0]['total_money'] == null) ? 0 : $total_money[0]['total_money'];
        }
        return $retoper;
    }

    /**
     * Получает список последних операций
     * @param int $count
     * @return array
     */
    function getLastOperations($count = 10) {
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
                    o.drain,
                    o.comment,
                    o.transfer,
                    o.tr_id,
                    0 AS virt,
                    o.tags,
                    o.imp_id AS moneydef,
                    o.exchange_rate AS curs,
                    o.type,
                    created_at,
                    updated_at
                FROM
                    operation o
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
                    1,
                    t.comment,
                    '',
                    '',
                    1 AS virt,
                    t.tags,
                    NULL,
                    NULL,
                    4 as type,
                    dt_create AS created_at,
                    dt_update AS updated_at
                FROM
                    target_bill t
                LEFT JOIN
                    target tt
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
     * Функция возвращает первую операцию по счёту - начальный баланс
     * @param int $account_id
     * @return array
     */
    function getFirstOperation($account_id=0) {
        $sql = "SELECT money FROM operation WHERE user_id=? AND account_id=? AND comment='Начальный остаток'";
        $first = $this->db->query($sql, $this->_user->getId(), $account_id);
        return $first[0]['money'];
    }

    /**
     * @deprecated
     * @FIXME Дописать комментарии к функции
     */
    function getCurrency() {
        $SourceId = (int) $_POST['SourceId']; // Ид счёта на который нужно перевести
        $TargetId = (int) $_POST['TargetId']; // Ид текущего счёта
        $aTarget = $aSource = array();
        $curr = Core::getInstance()->currency;
        foreach ($this->_user->getUserAccounts() as $val) {
            if ($val['account_id'] == $SourceId) {
                $aTarget[$val['account_currency_id']] = $curr[$val['account_currency_id']]['name'];
            }
            if ($val['account_id'] == $TargetId) {
                $aSource[$val['account_currency_id']] = $curr[$val['account_currency_id']]['name'];
            }
        }

        if (key($aSource) != key($aTarget)) {
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

    /**
     * Возвращает тип операции по айди
     * @param integer $id
     * @return integer
     */
    function getTypeOfOperation($id=0) {
        $type = 0; //возвращаемый тип операции
        $sql = "SELECT drain, transfer, count(*) as c FROM operation WHERE id=? AND user_id=? GROUP BY id";
        $res1 = $this->db->query($sql, $id, $this->_user->getId());
        $sql = "SELECT count(*) AS c FROM target_bill WHERE id=? AND user_id=?";
        $res2 = $this->db->query($sql, $id, $this->_user->getId());
        if ($res1[0]['c'] != $res2[0]['c']) {
            if ($res1[0]['c'] == 1) {
                if ($res1[0]['drain'] == 1)
                    $type = 0;
                if ($res1[0]['drain'] == 0)
                    $type = 1;
                if ($res1[0]['transfer'] != 0)
                    $type = 2;
            }
            else
                $type = 4;
        }//определили тип, иначе
        else
            return null; //один случай на миллиард. а на деле врят ли произойдёт. случай если есть и операция и перевод на фин целт с одним айди
 return $type;
    }

    public function getOperation($userId, $operationId) {
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
                o.drain,
                o.comment,
                o.transfer,
                o.tr_id,
                0 AS virt,
                o.account_id as account,
                o.tags,
                o.imp_id AS moneydef,
                o.exchange_rate AS curs,
                o.type,
                created_at
            FROM
                operation o
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
                1,
                t.comment,
                '',
                '',
                1 AS virt,
                t.bill_id as account,
                t.tags,
                NULL,
                NULL,
                4 as type,
                dt_create AS created_at
            FROM
                target_bill t
            LEFT JOIN
                target tt
            ON
                t.target_id=tt.id
            WHERE
                t.user_id = ?
            AND
                t.id = ?";

        $operation = $this->db->selectRow($sql, (int) $userId, (int) $operationId, (int) $userId, (int) $operationId);

        if ($operation['type'] == 2 && $operation['tr_id'] == 0) {
            $operation['toAccount'] = $operation['transfer'];
        } elseif ($operation['type'] == 2 && $operation['tr_id'] > 0) {
            $operation['toAccount'] = $operation['account'];
            $operation['account'] = $operation['transfer'];
        }

        return $operation;
    }

    /**
     * Возвращает количество операций по выбранному счёту
     * @FIXME Переписать на получение количества операций по всем счетам, что бы не дёргать
     * по каждому счёту отдельно
     * @param int $acc_id
     * @return int
     */
    public function getNumOfOperetionOnAccount($acc_id) {
        $sql = "SELECT count(*) as op_count FROM operation
            WHERE account_id=?
            AND updated_at BETWEEN ADDDATE(NOW(), INTERVAL -1 MONTH) AND NOW() ";
        $count = $this->db->selectRow($sql, (int) $acc_id);
        return $count['op_count'];
    }

    /**
     * Удаляет все операции по указанной категории
     *
     * @param oldUser $user
     * @param int $catId
     * @return bool
     */
    public function deleteOperationsByCategory(oldUser $user, $catId) {
        $sql = "UPDATE operation SET updated_at=NOW(), deleted_at=NOW() WHERE cat_id IN (
            SELECT cat_id FROM category WHERE user_id=? AND (cat_id=? OR cat_parent=?))";

        return (bool) $this->db->query($sql, $user->getId(), $catId, $catId);
    }

}
