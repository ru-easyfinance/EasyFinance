<?php
/**
 * Модель событий в календаре
 *
 * @author ukko <max.kamashev@easyfinance.ru>
 */
class Calendar_Model extends _Core_Abstract_Model
{
    /**
     * Храним ид овнера для всяческих операций.
     *
     * @var integer
     */
    public function __construct(array $row, oldUser $owner)
    {
            $this->ownerId = $owner->getId();
            $this->fields = $row;
    }


    /**
     * Выбрать все операции из календаря.
     * Базовый запрос для выборки.
     *
     * @param  oldUser $user
     * @param  array   $where       - массив дополнительных условий выборки
     * @param  array   $bindValues  - массив значений для подстановки в дополнительные условия
     *
     * @return array
     */
    static protected function _find(oldUser $user, array $where = array(), array $bindValues = array())
    {
        $where = implode(' AND ', $where);
        if ($where) {
            $where = ' AND ' . $where;
        }

        $sql = "SELECT
                    o.id AS ARRAY_KEY,
                    o.id,
                    o.chain_id AS chain,
                    o.type,
                    o.money AS amount,
                    o.comment,
                    o.cat_id AS category,
                    o.account_id AS account,
                    o.tags,
                    DATE_FORMAT( o.date, '%d.%m.%Y' ) AS date,
                    o.time,
                    DATE_FORMAT(c.start, '%d.%m.%Y' ) AS start,
                    DATE_FORMAT(c.last, '%d.%m.%Y' ) AS last,
                    c.every,
                    c.repeat,
                    c.week,
                    o.accepted,
                    o.transfer_account_id,
                    o.source_id AS source
                FROM operation o
                    LEFT JOIN calendar_chains c ON (c.id=o.chain_id)
                WHERE
                    o.user_id = ?
                    AND o.deleted_at IS NULL
                    {$where}
        ";

        $args = array_merge(array($sql, $user->getId()), $bindValues);
        return call_user_func_array(array(Core::getInstance()->db, 'select'), $args);
    }


    /**
     * Загрузка всех событий календаря для пользователя
     *
     * @param integer   $userId
     * @param mysqldate $start
     * @param mysqldate $end
     *
     * @return array Calendar_Model
     */
    public static function loadAll(oldUser $user, $start, $end)
    {
        $where = array(
            'o.`date` BETWEEN ? AND ?',
            '(o.accepted=0 OR o.chain_id > 0)',
        );
        $rows = self::_find($user, $where, array($start, $end));


        return self::_prepareModelsArray($user, $rows);
    }


    /**
     * Загрузка всех неподтверждённых событий для указанного пользователя
     *
     * @param oldUser $user
     *
     * @return array Calendar_Model
     */
    public static function loadOverdue(oldUser $user)
    {
        $where = array(
            'o.`date` <= CURRENT_DATE()',
            'o.accepted = 0',
        );
        $rows = self::_find($user, $where);


        return self::_prepareModelsArray($user, $rows);
    }


    /**
     * Загрузка всех напоминалок на неделю вперёд
     *
     * @param oldUser $user
     *
     * @return array Calendar_Model
     */
    public static function loadReminder(oldUser $user)
    {
        $where = array(
            'o.`date` BETWEEN ADDDATE(CURRENT_DATE(), INTERVAL 1 DAY) AND ADDDATE(CURRENT_DATE(), INTERVAL 8 DAY)',
            'o.accepted = 0',
        );
        $rows = self::_find($user, $where);

        return self::_prepareModelsArray($user, $rows);
    }


    /**
     * Подготовить массив моделей из raw-массива выборки
     * Подмешать значения уведомлений
     *
     * @param  oldUser $user
     * @param  array   $rows
     *
     * @return array Calendar_Model
     */
    static protected function _prepareModelsArray(oldUser $user, array $rows)
    {
        $modelsArray = array();

        if ($rows) {
            self::_addNotificationInfo($user, $rows);

            foreach ($rows as $row) {
                $model = new Calendar_Model($row, $user);
                $modelsArray[$row['id']] = $model;
            }
        }

        return $modelsArray;
    }


    /**
     * Добавляет в запись информацию о напоминаниях
     *
     * @param oldUser $user
     * @param array $row
     * @return array
     */
    private static function _addNotificationInfo(oldUser $user, array &$rows)
    {
        // Предполагаем, что в ключах массивов лежат ID записей
        // см. self::_find()
        assert(!isset($rows[0]));
        $opIds = array_keys($rows);

        // Выбираем только те напомининания, которые еще не отработали
        $sql = "SELECT * FROM operation_notifications
                WHERE operation_id IN (?a) AND is_done=0";
        $notifications = Core::getInstance()->db->select($sql, $opIds);


        // Напоминания об операциях
        if ($notifications) {

            foreach ($notifications as $notrow) {
                $row =& $rows[$notrow['operation_id']];

                $notificationDate = new DateTime($notrow['schedule']);
                $notificationDate->setTimezone(new DateTimeZone($user->getUserProps('time_zone')));

                // дата операции
                // TODO: !!! Дата записана в БД без учета часового пояса пользователя
                $operationDate = new DateTime($row['date']);
                $notificationDate->setTimezone(new DateTimeZone($user->getUserProps('time_zone')));

                $opTime  = strtotime($operationDate->format('Y-m-d'));
                $notTime = strtotime($notificationDate->format('Y-m-d'));

                $daysBefore = floor(($opTime - $notTime) / (3600 * 24));

                // SMS
                if ($notrow['type'] == 0) {
                    $row['smsEnabled'] = 1;
                    $row['smsDaysBefore'] = $daysBefore;
                    $row['smsHour'] = $notificationDate->format('H');
                    $row['smsMinutes'] = $notificationDate->format('i');
                }

                // Email
                if ($notrow['type'] == 1) {
                    $row['mailEnabled'] = 1;
                    $row['mailDaysBefore'] = $daysBefore;
                    $row['mailHour'] = $notificationDate->format('H');
                    $row['mailMinutes'] = $notificationDate->format('i');
                }
            }
        }
    }


    /**
     * Создаёт цепочку операций
     * @param oldUser $user Пользователь
     * @param Calendar_Event $event Событие
     * @param array $arrayDays Массив с днями, повторениями
     * @return bool
     */
    public static function create(oldUser $user, Calendar_Event $event, $arrayDays)
    {

        // Создаём само событие
        $sql = "INSERT INTO calendar_chains (`user_id`,`start`,`last`,`every`, `repeat`, `week`)
            VALUES (?, ?, ?, ?, ?, ?);";

        // Создаём событие в календаре
        $calId = Core::getInstance()->db->query(
            $sql,
            $user->getId(),
            $event->getDate(),
            $event->getLast(),
            $event->getEvery(),
            $event->getRepeat(),
            $event->getWeek()
        );

        return self::createOperations($user, $event, $calId, $arrayDays);
    }


    /**
     * Обновляет события
     * @param oldUser $user
     * @param Calendar_Event $event
     * @param array $array
     * @return bool
     */
    public static function update(oldUser $user, Calendar_Event $event, $array)
    {
        // Создаём само событие
        $sql = "UPDATE calendar_chains c
            SET `last` = ?, `every` = ?, `repeat` = ?, `week` = ?
            WHERE `user_id` = ? AND id = ? ;";

        // Создаём событие в календаре
        Core::getInstance()->db->query(
            $sql,
            $event->getLast(),
            $event->getEvery(),
            $event->getRepeat(),
            $event->getWeek(),
            $user->getId(),
            $event->getChain()
        );

        // Возвращает даты подтверждённых в этой серии
        $accepted = self::loadAcceptedByChain($user, $event->getChain());

        // Создаём повторы события
        $arrayDays = array();
        foreach ($array as $value) {
            if (in_array($value, $accepted)) {
                continue;
            }
            $arrayDays[] = $value;
        }

        return self::createOperations($user, $event, $event->getChain(), $arrayDays);
    }


    /**
     * Возвращает даты подтверждённых событий в серии
     * @param oldUser $user
     * @param int $chain
     * @return array
     */
    public static function loadAcceptedByChain(oldUser $user, $chain)
    {
        $sql = 'SELECT `date` FROM operation c
                WHERE user_id=? AND chain_id=? AND accepted=1 AND deleted_at IS NULL';
        return Core::getInstance()->db->selectCol($sql, $user->getId(), $chain);
    }


    /**
     * Добавляет рег. операции
     * @param oldUser $user
     * @param Calendar_Event $event
     * @param int $chain
     * @param array $arrayDays
     */
    private function createOperations (oldUser $user, Calendar_Event $event, $chain, $arrayDays )
    {
        // Создаём повторы события в виде неподтверждённых операций
        $operationsArray = array();

        foreach ($arrayDays as $value) {

            // @TODO Посмотреть, как можно адаптировать $event->__getArray()
            $operationsArray[] = array (
                'type'       => $event->getType(),
                'account'    => $event->getAccount(),
                'amount'     => $event->getAmount(),
                'category'   => $event->getCategory(),
                'date'       => $value,
                'comment'    => $event->getComment(),
                'tags'       => $event->getTags(),
                'convert'    => $event->getConvert(),
                'close'      => $event->getClose(),
                'currency'   => $event->getCurrency(),
                'toAccount'  => $event->getToAccount(),
                'target'     => $event->getTarget(),

                // Дополнения для планирования в календарь
                'last'       => $event->getLast(),
                'time'       => $event->getTime(),
                'every'      => $event->getEvery(),
                'repeat'     => $event->getRepeat(),
                'week'       => $event->getWeek(),
                'accepted'   => 0,
                'chain'      => $chain,

                // Напоминания
                'mailEnabled'       => $event->getMailEnabled(),
                'mailDaysBefore'    => $event->getMailDaysBefore(),
                'mailHour'          => $event->getMailHour(),
                'mailMinutes'       => $event->getMailMinutes(),

                'smsEnabled'        => $event->getSmsEnabled(),
                'smsDaysBefore'     => $event->getSmsDaysBefore(),
                'smsHour'           => $event->getSmsHour(),
                'smsMinutes'        => $event->getSmsMinutes(),
            );
        }

        $operation = new Operation_Model();

        // Расход и доход
        if ($event->getType() <= 1) {
            return $operation->addSome($operationsArray);
        } elseif ($event->getType() == 2) {
            return $operation->addSomeTransfer($operationsArray);
        }
    }


    /**
     * Получает операцию в виде массива
     * @param oldUser $user
     * @param int $chain
     * @return array
     */
    public static function getByChain(oldUser $user, $chain)
    {
        $sql = 'SELECT * FROM calendar_chains c WHERE user_id=? AND id=?';
        return Core::getInstance()->db->selectRow($sql, $user->getId(), $chain);
    }


    /**
     * Удаляет события
     * @param oldUser $user
     * @param int  $chain
     * @return bool
     */
    public static function deleteEvents(oldUser $user, $chain)
    {
        $sql = "DELETE FROM operation
                WHERE user_id=? AND chain_id=? AND accepted=0";

        if (Core::getInstance()->db->query($sql, $user->getId(), $chain)) {
            return true;
        }

        return false;
    }


    /**
     * Отмечает события как выполненные
     * @param oldUser $user
     * @param array $ids array(int, int, int, ..)
     * @return bool
     */
    public static function acceptEvents(oldUser $user, $ids)
    {
        $operationList = array();
        foreach ($ids as $value) {
            $operationList[] = array(
                'accepted' => '1',
                'id'       => $value,
            );
        }

        if ($operationList) {
            $operation = new Operation_Model($user);
            return (bool) $operation->editMultiple($operationList);
        }
    }


    /**
     * Редактирует дату операции
     * @param oldUser $user
     * @param int $id
     * @param mysql date $date
     * @return bool
     */
    public static function editDate(oldUser $user, $id, $date)
    {
        $sql = "UPDATE operation SET `date`= ? WHERE id =? AND user_id=?;";

        return Core::getInstance()->db->query($sql, $date, $id, $user->getId());
    }


    /**
     *
     */
    function load ()
    {
    }

    function save()
    {
    }

    public function delete ()
    {
    }

}
