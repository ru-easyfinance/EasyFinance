<?php
/**
 * Статичный класс для создания объектов в базе данных
 */
class CreateObjectHelper {

    private static function _wrapKey($options)
    {
        $keys = array_keys($options);

        $result = "";
        foreach($keys as $key) {
            if (!empty($result)) {
                $result .= ",";
            }
            $result .= "`".$key."`";
        }

        return $result;
    }
    
    private static function _wrapVal($options)
    {
        $result = "";
        foreach($options as $value) {
            if (!empty($result)) {
                $result .= ",";
            }
            $result .= "'".$value."'";
        }

        return $result;
    }


    /**
     * Создаёт пользователя в базе, возвращает id
     *
     * @param array $options
     * @return int Возвращает id созданного пользователя или false
     */
    public static function createUser(array $options = array())
    {
        $default = array(
            'user_pass'  => sha1('pass'),
            'user_login' => 'login'.sha1(microtime()),
        );

        $options = array_merge($default, $options);

        $sql = "INSERT INTO users(".self::_wrapKey($options).") VALUES (".self::_wrapVal($options).")";
        return Core::getInstance()->db->query($sql);
    }


    /**
     * Создаёт счёт пользователя в бд, возвращает id
     *
     * @param array $options
     * @return int | false
     */
    public static function createAccount(array $options)
    {
        if (!isset($options['user_id'])) {
            throw new Exception('Expected option user_id');
        }

        $default = array(
            'account_currency_id' => efMoney::RUR,
            'account_type_id'     => Account_Collection::ACCOUNT_TYPE_CASH,
            'updated_at'          => '2010-05-26 16:31:04',
            'created_at'          => '2010-05-26 16:31:04',
            'account_name'        => 'Название счёта по-русски',
        );

        $options = array_merge($default, $options);
        
        $sql = "INSERT INTO accounts (".self::_wrapKey($options).") VALUES (".self::_wrapVal($options).")";

        return Core::getInstance()->db->query($sql);
    }


    /**
     * Создаёт операцию в БД, возвращает ид
     *
     * @param array $option
     * @return int | false
     */
    public static function createOperation($options)
    {
        if (!isset($options['user_id'])) {
            throw new Exception('Expected option user_id');
        }

        $default = array(
            'money'      => 1000,
            'date'       => '2010-01-01',
            'created_at' => '2010-01-01 00:00:00',
            'updated_at' => '2010-01-01 00:00:00',
            'chain_id'   => 0,
            'time'       => '12:00:00',
        );

        $options = array_merge($default, $options);

        $sql = "INSERT INTO operation (".self::_wrapKey($options).") VALUES (".self::_wrapVal($options).")";
        return Core::getInstance()->db->query($sql);
    }


    /**
     * Создаёт операцию в БД, возвращает ИД
     *
     * @param array $options
     * @return int | false
     */
    public static function createCategory($options)
    {
        if (!isset($options['user_id'])) {
            throw new Exception('Expected option user_id');
        }

        $default = array(
            'custom'   => 1,
            'cat_name' => 'Название категории' . rand(0, 1000),
        );

        $options = array_merge($default, $options);

        $sql = "INSERT INTO category (".self::_wrapKey($options).") VALUES (".self::_wrapVal($options).")";
        return Core::getInstance()->db->query($sql);
    }


    /**
     * Создаёт запись бюджета в БД, возвращает ИД записи
     *
     * @param array $options
     * @return true | false
     */
    public static function createBudget($options)
    {
        if (!isset($options['user_id'])) {
            throw new Exception('Expected option user_id');
        }

        if (!isset($options['category'])) {
            throw new Exception('Expected option category');
        }


        $default = array(
            'dt_create'  => '2010-01-01 00:00:00',
            'date_end'   => date('Y-m-d', mktime(0, 0, 0, date('m')+1, 0)),
            'date_start' => date('Y-m-d', mktime(0, 0, 0, date('m'), 1)),
            'amount'     => 500,
            'currency'   => 1,
            'drain'      => 1,
        );

        $options = array_merge($default, $options);

        if (!isset($options['key'])) {
            $options['key'] = $options['user_id'] . '-'
                                . $options['category'] . '-'
                                . $options['drain'] . '-'
                                . $options['date_start'];
        }

        $sql = "INSERT INTO budget (".self::_wrapKey($options).") VALUES (".self::_wrapVal($options).")";
        return Core::getInstance()->db->query($sql);
    }
}
