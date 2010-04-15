<?php
/**
 * Модель событий в календаре
 *
 * @author ukko <max.kamashev@easyfinance.ru>
 */
class Calendar_Model extends _Core_Abstract_Model {

    /**
     * Храним ид овнера для всяческих операций.
     *
     * @var integer
     */
    public function __construct( array $row, User $owner )
    {
            $this->ownerId = $owner->getId();

            $this->fields = $row;
    }

    /**
     * Загрузка всех событий календаря для пользователя
     *
     * @param integer $userId
     * @param mysqldate $start
     * @param mysqldate $end
     * @param bool $delay Если - тру, то отдаёт напоминалки
     *
     * @return array Массив моделей событий
     */
    public static function loadAll( User $user, $start, $end )
    {
        $modelsArray = array();

//        $cache = _Core_Cache::getInstance();
//        $cacheId = 'calendarUser' . $user->getId();
//
//        // Проверка наличия в кеше идентификаторов сообщений пользователя
//        $messageIds = $cache->get( $cacheId );
//        // Если есть - запрашиваем их все из кеша
//        if ( $messageIds && is_array($messageIds) )
//        {
//        	$modelsArray = $cache->getMulti( $messageIds );
//        }

        // Запрос данных для полного календаря

        $sql = 'SELECT o.id, o.chain_id AS chain, o.type,
            o.money AS amount, o.comment, o.cat_id AS category, o.account_id AS account, o.tags,
            DATE_FORMAT( o.date, "%d.%m.%Y" ) AS date, o.time,
            DATE_FORMAT(c.start, "%d.%m.%Y" ) AS start, DATE_FORMAT(c.last, "%d.%m.%Y" ) AS last,
            c.every, c.repeat, c.week, o.accepted, o.tr_id, o.transfer, o.source_id AS source
            FROM operation o
            LEFT JOIN calendar_chains c ON c.id=o.chain_id
            WHERE o.user_id = ?';

        $rows = Core::getInstance()->db->select( $sql, $user->getId(), $start, $end );

        foreach ( $rows as $row )
        {
            // Пропускаем повторы переводов
            if ( ( ( int ) $row['type'] == 2 ) && ( ( int ) $row['tr_id'] == 0 ) ) { continue; }
            $model = new Calendar_Model( $row, $user );

            $modelsArray[$row['id']] = $model;
        }

        // Cохранение моделей в кеш
        //$cache->set( $cacheId, $modelsArray );

        return $modelsArray;
    }

    /**
     * Создаёт цепочку операций
     * @param User $user Пользователь
     * @param Calendar_Event $event Событие
     * @param array $array_days Массив с днями, повторениями
     * @return bool
     */
    public static function create ( User $user, Calendar_Event $event, $array_days )
    {

        // Создаём само событие
        $sql = "INSERT INTO calendar_chains (`user_id`,`start`,`last`,`every`, `repeat`, `week`)
            VALUES (?, ?, ?, ?, ?, ?);";

        // Создаём событие в календаре
        $cal_id = Core::getInstance()->db->query($sql,
            $user->getId(),
            $event->getDate(),
            $event->getLast(),
            $event->getEvery(),
            $event->getRepeat(),
            $event->getWeek() );

        return self::createOperations($user, $event, $cal_id, $array_days);
    }

    /**
     * Обновляет события
     * @param User $user
     * @param Calendar_Event $event
     * @param array $array
     * @return bool
     */
    public static function update ( User $user, Calendar_Event $event, $array )
    {

        // Создаём само событие
        $sql = "UPDATE calendar_chains c
            SET `last` = ?, `every` = ?, `repeat` = ?, `week` = ?
            WHERE `user_id` = ? AND id = ? ;";

        // Создаём событие в календаре
        Core::getInstance()->db->query($sql,
            $event->getLast(),
            $event->getEvery(),
            $event->getRepeat(),
            $event->getWeek(),
            $user->getId(),
            $event->getChain()
        );

        // Возвращает даты подтверждённых в этой серии
        $accepted = self::loadAcceptedByChain( $user, $event->getChain() );

        // Создаём повторы события
        $array_days = array();
        foreach ($array as $value) {
            if ( in_array($value, $accepted)) continue;
            $array_days[] = $value;
        }

        return self::createOperations($user, $event, $event->getChain(), $array_days);
    }

    /**
     * Возвращает даты подтверждённых событий в серии
     * @param User $user
     * @param int $chain
     * @return array
     */
    public static function loadAcceptedByChain ( User $user, $chain )
    {
        $sql = 'SELECT `date` FROM operation c WHERE user_id=? AND chain_id=? AND accepted=1';
        return Core::getInstance()->db->selectCol( $sql, $user->getId(), $chain );
    }

    /**
     * Добавляет рег. операции
     * @param User $user
     * @param Calendar_Event $event
     * @param int $chain
     * @param array $array
     */
    private function createOperations ( User $user, Calendar_Event $event, $chain, $array_days )
    {
        // Создаём повторы события в виде неподтверждённых операций
        $operations_array = array();

        foreach ($array_days as $value) {

            // @TODO Посмотреть, как можно адаптировать $event->__getArray()
            $operations_array[] = array (

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
                'drain'      => ($event->getType()==1)?0:1,

                // Дополнения для планирования в календарь
                'last'       => $event->getLast(),
                'time'       => $event->getTime(),
                'every'      => $event->getEvery(),
                'repeat'     => $event->getRepeat(),
                'week'       => $event->getWeek(),
                'accepted'   => 0,
                'chain'      => $chain,

            );
        }

        $operation = new Operation_Model();

        // Расход и доход
        if ( $event->getType () <= 1 ) {
            return $operation->addSome( $operations_array );
        } elseif ( $event->getType () == 2 ) {
            return $operation->addSomeTransfer ( $operations_array );
        } elseif ( $event->getType () == 4 ) {
//            $target = new Targets_Model();
//            return $target->addSomeTargetOperation( $operations_array );
        }
    }

    /**
     * Получает операцию в виде массива
     * @param User $user
     * @param int $chain
     * @return array
     */
    public static function getByChain ( User $user, $chain )
    {
        $sql = 'SELECT * FROM calendar_chains c WHERE user_id=? AND id=?';
        return Core::getInstance()->db->selectRow( $sql, $user->getId(), $chain );
    }

    /**
     * Удаляет события
     * @param User $user
     * @param int  $chain
     * @return bool
     */
    public static function deleteEvents ( User $user, $chain )
    {
        $sql = "DELETE FROM operation WHERE user_id=? AND chain_id=? AND accepted=0";

        if ( Core::getInstance()->db->query($sql, $user->getId(), $chain) ) {

            return true;

        } else {

            return false;

        }
    }

    /**
     * Отмечает события как выполненные
     * @param User $user
     * @param array $ids array(int, int, int, ..)
     * @return bool
     */
    public static function acceptEvents ( User $user, $ids )
    {

         $string_ids = '';
        // Если получили массив, преобразуем его для выборки в мускуле
        if ( is_array($ids) ) {

            foreach ($ids as $v) {
                if ( (int) $v > 0 ) {
                    if ( !empty ($string_ids) ) $string_ids .= ',';
                    $string_ids .= $v;
                }
            }

        } elseif ( ( int ) $ids > 0 ) {
            $string_ids = ( int ) $ids;
        } else {
            return false;
        }

        $sql = "UPDATE operation SET accepted=1 WHERE id IN ( {$string_ids} ) AND user_id=?;";

        if ( Core::getInstance()->db->query( $sql, $user->getId() ) ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Получает список операций по ID
     * @param array $ids
     * @return array
     */
    public static function getByIDS ( $ids ) {
        $sql = "SELECT * FROM operation WHERE id IN (" . implode( ",", $ids ) . ");";
        $operations = Core::getInstance()->db->select( $sql );
        $return = array();
        foreach ( $ids as $value ) {
            $return[$value['id']] = $value;
        }
        return $return;
    }

    /**
     * Редактирует дату операции
     * @param User $user
     * @param int $id
     * @param mysql date $date
     * @return bool
     */
    public static function editDate ( User $user, $id, $date )
    {
        $sql = "UPDATE operation SET `date`= ? WHERE id =? AND user_id=?;";

        return Core::getInstance()->db->query( $sql, $date, $id, $user->getId() );

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
