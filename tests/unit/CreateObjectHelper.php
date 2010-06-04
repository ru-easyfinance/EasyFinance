<?php
/**
 * Статичный класс для создания объектов в базе данных
 */
class CreateObjectHelper {

    private static function _wrapKey($props)
    {
        $keys = array_keys($props);
        return sprintf('`%s`', implode('`, `', $keys));
    }

    private static function _wrapVal($props)
    {
        return sprintf("'%s'", implode("', '", $props));
    }


    /**
     * Создаёт пользователя в базе, возвращает id
     *
     * @param array $options
     * @return int Возвращает id созданного пользователя или false
     */
    public static function makeUser(array $options = array())
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
     * @param  array $options
     * @return array
     */
    public static function makeAccount(array $props = array())
    {
        $default = array(
            'account_currency_id' => efMoney::RUR,
            'account_type_id'     => Account_Collection::ACCOUNT_TYPE_CASH,
            'updated_at'          => '2010-05-26 16:31:04',
            'created_at'          => '2010-05-26 16:31:04',
            'account_name'        => 'Название счёта по-русски',
            'account_description' => 'Описание счета',
        );
        $props = array_merge($default, $props);

        if (!isset($props['user_id'])) {
            $props['user_id'] = self::makeUser();
        }

        $sql = "INSERT INTO accounts (".self::_wrapKey($props).") VALUES (".self::_wrapVal($props).")";
        $props['account_id'] = Core::getInstance()->db->query($sql);

        return $props;
    }


    /**
     * Создаёт операцию в БД, возвращает ид
     *
     * @param array $option
     * @return int | false
     */
    public static function makeOperation($props)
    {
        if (!isset($props['user_id'])) {
            throw new Exception('Expected option user_id');
        }

        $default = array(
            'money'      => 1000,
            'date'       => '2010-01-01',
            'created_at' => '2010-01-01 00:00:00',
            'updated_at' => '2010-01-01 00:00:00',
            'chain_id'   => 0,
            'time'       => '12:00:00',
            'accepted'   => 1,
        );

        $props = array_merge($default, $props);

        $sql = "INSERT INTO operation (".self::_wrapKey($props).") VALUES (".self::_wrapVal($props).")";
        return Core::getInstance()->db->query($sql);
    }


    /**
     * Создать балансовую операцию
     *
     * @param  array $accountProps
     * @param  floan $amount
     * @return int
     */
    static public function makeBalanceOperation($accountProps, $amount)
    {
        $op = array(
            'account_id'  => $accountProps['account_id'],
            'user_id'     => $accountProps['user_id'],
          //'cat_id'      => null,
            'money'       => $amount,
            'date'        => '0000-00-00',
            'time'        => '00:00:00',
            'drain'       => 0,
            'type'        => 1,
            'comment'     => 'Начальный остаток',
            'accepted'    => 1,
        );
        return self::makeOperation($op);
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
