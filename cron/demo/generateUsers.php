#!/usr/local/bin/php
<?php
define('INDEX', true);
// Подключаем конфиг демы
include( dirname(dirname(dirname(__FILE__))) . '/include/config.php' );

$dug = new DemoUserGenerator();
$dug->parseOptions($argv);
$dug->run();

class DemoUserGenerator
{
    private $_help = "
Use: ./generateUsers.php -sdb user:passwd@source_host/database \
    -uid Source_user_Id -c Count_Users_To_Generate [--debug] [--no-truncate]
";
    private $_debug = false;
    private $_noTruncate = false;
    private $_force = false;
    private $_sourceUserId;
    private $_usersCount;
    private $_sourceDb;
    private $_targetDb;
    /**
     * Конфиг таблиц для выборки
     */
    private $_tablesTemplate = array(
        'users' => array(
            // поле по которому будет производится выборка, и которое будет
            //заменено на ключ (user_id)
            'userId' => 'id'
        ),
        'accounts' => array(
            'userId' => 'user_id',
            // к значениям каких полей добавлять значение ключа (user_id)
            'addUserId' => array('account_id'),
        ),
        'account_field_values' => array(
            // Условие выборки, если невозможно выбрать напрямую по userId
            'selectCase' => '`account_fieldsaccount_field_id` IN
                (select `account_id` from `accounts`
                    where `user_id`={$userId})',
            'addUserId' => array(
                'field_value_id',
                'account_fieldsaccount_field_id'
            ),
        ),
        'calendar_chains' => array(
            'userId' => 'user_id',
            'addUserId' => array('id')
        ),
        'category' => array(
            'userId' => 'user_id',
            'addUserId' => array('cat_id', 'cat_parent')
        ),
        'operation' => array(
            'userId' => 'user_id',
            'addUserId' => array(
                'id',
                'cat_id',
                'account_id',
                'transfer_account_id'
            )
        ),
        'tags' => array(
            'userId' => 'user_id',
            'addUserId' => array('oper_id'),
        ),
        'target' => array(
            'userId' => 'user_id',
            'addUserId' => array('id', 'target_account_id')
        ),
        'target_bill' => array(
            'userId' => 'user_id',
            'addUserId' => array('id', 'bill_id', 'target_id')
        ),
        'budget' => array(
            'userId' => 'user_id',
            'addUserId' => array('category')

        // из за странного составного ключа - похачен алгоритм !
        )
    );

    public function parseOptions($argv)
    {
        foreach ($argv as $k => $a) {
            if ($a == '--debug') {
                $this->_debug = true;
            } elseif ($a == '--no-truncate') {
                $this->_noTruncate = true;
            } elseif ($a == '--force') {
                $this->_force = true;
            } elseif ($a == '-uid' && isset($argv[$k + 1])) {
                $this->_sourceUserId = $argv[$k + 1];
            } elseif (
                $a == '-c' && isset($argv[$k + 1]) && is_numeric($argv[$k + 1])
            ) {
                $this->_usersCount = $argv[$k + 1];
            } elseif ($a == '-sdb' && isset($argv[$k + 1])) {
                if (
                    !preg_match(
                        '/([^:]+):([^@]+)@([^\/]+)\/(.*)/',
                        $argv[$k + 1],
                        $matches
                    )
                ) {
                    die($help);
                }

                $this->_sourceDb = array(
                    'host' => $matches[3],
                    'user' => $matches[1],
                    'pass' => $matches[2],
                    'dbname' => $matches[4]
                );
            }
        }

        if(
            !isset($this->_sourceUserId)
            || !isset($this->_usersCount)
            || !isset($this->_sourceDb)
        )
            die($this->_help);
    }

    /**
     * Вынимает данные пользователя из БД
     * @param mixed $sourceUserId ИД пользователя или его логин
     */
    public function pickUserData($sourceUserId)
    {
        // Подключаем базу - источник
        $this->_debugMessage("Connecting to source database ...");

        $dsn  = "mysql:dbname={$this->_sourceDb['dbname']};";
        $dsn .= "host={$this->_sourceDb['host']}";

        $db = new PDO($dsn, $this->_sourceDb['user'], $this->_sourceDb['pass']);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $result = $db->query(
            "SELECT id FROM users
            WHERE id = '$sourceUserId' OR user_login = '$sourceUserId' LIMIT 1"
        );

        $sourceUserId = $result->fetchColumn();

        $tablesSelected = array();

        // Делаем выборку таблиц и их данных из массива
        $this->_debugMessage("Selecting tables ...");

        foreach ($this->_tablesTemplate as $table => $params) {
            if (!isset($params['userId']) && !isset($params['selectCase'])) {
                die('Can\'t select table without key or `where` statement!');
            }

            $sql = 'select * from `' . $table . '` where ';

            $sql .= isset($params['selectCase']) ?
                str_ireplace('{$userId}', $sourceUserId, $params['selectCase'])
                :
                " `{$params['userId']}`={$sourceUserId};";

            $result = $db->query($sql);

            $tablesSelected[$table] = $result->fetchAll(PDO::FETCH_ASSOC);

            $this->_debugMessage("`$table` selected;");
        }

        return $tablesSelected;
    }

    /**
     * Генерирует демо пользователей
     * @param array $tablesSelected массив данных пользователя
     * @param int $usersCount число копий
     * @return array массив демо пользователей для записи в файл
     */
    public function cloneUsers($tablesSelected, $usersCount)
    {
        // Удостоверяемся что запущены в демо режиме, дабы не потереть лишнего
        if(!IS_DEMO && !$this->_force)
            die("Must be runned only for demo !\n");

        $this->_connectTargetDb();
        $this->_truncateTables();

        $userTemplate = '';
        $users = array();

        // Шаг перехода
        $idInterval = 1000;

        // формируем запросы с заменой значений указанных полей
        $this->_debugMessage("Processing $usersCount users:");

        $startUser = $this->_noTruncate ?
            $this->_targetDb->query("SELECT MAX(id) FROM users;")->fetchColumn()
            : 0;

        for (
            $user = $startUser + 1;
            $user < $startUser + $usersCount * $idInterval;
            $user += $idInterval
       ) {
            $users['demo_' . $user] = sha1(microtime());

            $this->_targetDb->beginTransaction();

            try {
                foreach ($tablesSelected as $table => $rows) {
                    $rowCount = sizeof($rows);

                    // Если в селекте из таблицы не было данных - игнорим
                    if (!$rowCount)
                        continue;

                    // Если строк > периода ($idInterval) - обрезаем
                    // дабы не получить конфликт идентификаторов
                    array_splice($rows, $idInterval);

                    $sql = "insert ignore into `$table` (" . implode(
                        ', ',
                        array_map('quoteKey', array_keys($rows[0]))
                    );

                    $sql .= ') values ';

                    foreach ($rows as $id => $row) {
                        // Подменяем указанные поля на нужные нам значения
                        // UserId
                        if (isset($this->_tablesTemplate[$table]['userId'])) {
                            $row[$this->_tablesTemplate[$table]['userId']]
                                = $user;
                        }

                        // Прибавление
                        if (
                            isset($this->_tablesTemplate[$table]['addUserId'])
                        ) {
                            foreach (
                                $this->_tablesTemplate[$table]['addUserId']
                                as $key
                            ) {
                                // Если значение нулевое - оно специальное
                                // пропускаем
                                if($row[$key] == 0)
                                    continue;

                                $row[$key] += $user;
                            }
                        }

                        // Хак для таблицы users
                        if ($table == 'users' && $usersCount > 1) {
                            $row['user_login'] = 'demo_' . $user;
                            $row['user_pass'] = $users[$row['user_login']];
                        }

                        //Хак для таблицы операций
                        if ($table == 'operation' && $row['cat_id'] == 0)
                            $row['cat_id'] = null;

                        //Хак для таблицы бюджета
                        if ($table == 'budget') {
                            $row['key'] = implode(
                                '-',
                                array(
                                    $user,
                                    $row['category'],
                                    $row['drain'],
                                    $row['date_start']
                                )
                            );
                        }

                        $rowData = implode(',', array_map('quoteSql', $row));
                        $sql .= "\n($rowData)";
                        $sql .= (( $id < $rowCount - 1 ) ? ',' : '');
                    }

                    $sql .= ";\n";

                    $this->_targetDb->exec($sql);
                }

                $this->_targetDb->commit();

                $this->_debugMessage("user #$user done;");
            } catch (PDOException $e) {
                $this->_targetDb->rollBack();
                echo
                    "\n\n"
                    . $e->getMessage()
                    . "\n\n"
                    . $e->getTraceAsString()
                    . "\n\n"
                    . $sql;
                exit(2);
            }
        }

        return $users;
    }

    /**
     * Обновляем даты операций, бюджета
     */
    public function hackDates()
    {
        // подгоняем даты операций к текущему времени:
        // последняя подтвержденная операция должна стать сегодняшней,
        // поэтому каждую операцию сдвигаем на разницу между текущей датой и
        // последней операцией демо-пользователя
        // дату последней операции демо-пользователя
        // считаем известной - 21 мая 2010
        $this->_targetDb->exec(
            "UPDATE operation op SET
            DATE = DATE_ADD(
                DATE,
                INTERVAL (DATEDIFF(CURDATE(), '2010-05-21')) DAY
            )"
        );

        // подтягиваем бюджет к текущему месяцу:
        // т.к. эталонной пользователь был создан в мае 2010, и на май же был
        // запланирован бюджет, сдвинем бюджет
        // на столько месяцев, сколько прошло с мая 2010, чтобы бюджет вновь
        // стал актуален для текущего месяца
        $this->_targetDb->exec(
            "UPDATE budget bu SET
            bu.date_start = DATE_ADD(
                    bu.date_start,
                    INTERVAL (
                        PERIOD_DIFF(
                            DATE_FORMAT(CURDATE(), '%Y%m'), '201005'
                        )
                    ) MONTH
            )"
        );

        // дату конца поставим последней датой месяца начала - для этого от
        // первого числа следующего месяца отнимем 1,
        // а следующий месяц получим аналогично дате начала - на месяц больше

        $this->_targetDb->exec(
            "UPDATE budget bu SET
            bu.date_end = DATE_ADD(
                DATE_ADD(bu.date_start, INTERVAL 1 MONTH),INTERVAL -1 DAY
            )"
        );

        //обновляем ключи в бюджете
        $this->_targetDb->exec(
            "UPDATE budget b SET
            `key` = CONCAT(user_id, '-', category, '-', drain, '-', date_start)
            ORDER BY date_start DESC"
        );
    }

    public function run()
    {
        $count = microtime(true);
        $tablesSelected = $this->pickUserData($this->_sourceUserId);
        $users = $this->cloneUsers($tablesSelected, $this->_usersCount);
        $this->saveUsersToFile($users);
        if (!$this->_noTruncate)
            $this->hackDates();
        $this->_debugMessage('Done for ' . (microtime(true) - $count) . ' ms.');
    }

    public function saveUsersToFile($users)
    {
        // Файл для хранения массива сгенерённых пользователей
        $usersFile = DIR_SHARED . 'generatedUsers.php';
        $users = var_export($users, true);
        file_put_contents($usersFile, "<?php $users = $users;");
        chmod($usersFile, 0777);
    }

    private function _connectTargetDb()
    {
        if ($this->_targetDb)
            return true;

        // Подключаем базу - приёмник
        $this->_debugMessage('Connecting to target database ...');

        $dsn = 'mysql:dbname=' . SYS_DB_BASE . ';host=' . SYS_DB_HOST;

        $this->_targetDb = new PDO($dsn, SYS_DB_USER, SYS_DB_PASS);
        $this->_targetDb
            ->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    private function _truncateTables()
    {
        if ($this->_noTruncate)
            return false;

        // Очищаем таблицы
        $this->_debugMessage('Cleanup tables ...');
        $tables = array_keys($this->_tablesTemplate);

        foreach (array_reverse($tables) as $table) {
            $query = "truncate table `$table`";
            $this->_targetDb->exec($query);
        }
    }

    private function _debugMessage($message)
    {
        if ($this->_debug)
            echo "$message\n";
    }
}

function quoteSql($value)
{
    if (is_numeric($value)) {
        return $value;
    } elseif (null !== $value && $value !== '') {
        $value = addslashes($value);
        return "'$value'";
    } else {
        return 'null';
    }

}

function quoteKey($key)
{
    return "`$key`";

}
