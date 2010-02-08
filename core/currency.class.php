<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс для управления валютами, реализует SPL-интерфейс IteratorAggregate
 * @category currency
 * @author Max Kamashev (ukko) <max.kamashev@gmail.com>
 * @copyright http://easyfinance.ru/
 * @version SVN $Id$
 */
class Currency implements IteratorAggregate,  ArrayAccess
{
     /**
     * Ссылка на экземпляр DBSimple
     * @var DbSimple_Mysql
     */
    private $db;

    /**
     * Массив с системными валютами
     * @var array mixed
     */
    protected $sys_list_currency;

    /**
     * Конструктор класса
     * @return void
     */
    function __construct()
    {
        if (is_null(Core::getInstance()->db)) {
            Core::getInstance()->initDB();
        }
        $this->db = Core::getInstance()->db;
        
        // Загружаем список валют
        $this->loadCurrency();
    }

    /**
     * Загружает валюты. Сперва пробует загрузить их из файла, а потом, если не вышло, то из базы
     * @example Пример части загружаемого массива 
     * <code>array(
     * '2'=>array(
     *      'name'=>'Доллар США',
     *      'abbr'=>'$',
     *      'charCode'=>'USD',
     *      'value'=>'31.2424',
     *      'dirrection'=>'up'
     *  )),
     * </code>
     */
    function loadCurrency()
    {
        if (!include_once (dirname(dirname(__FILE__)).'/include/daily_currency.php'))
        {
            $daily = null;
        }
        
        $daily = null;
        
        if ($daily == null)
        {
            $currency = $this->db->select("SELECT * FROM currency c");
//         $daily = $this->db->select("SELECT currency_id, user_id, direction, currency_sum AS value,
//         MAX(currency_date) AS `date` FROM daily_currency GROUP BY currency_id, user_id");
            
            $daily = $this->db->select("SELECT currency_id, user_id, direction, currency_sum AS value, currency_date AS `date` FROM daily_currency WHERE
currency_from = 1 AND
currency_date = (SELECT MAX(currency_date) FROM daily_currency WHERE user_id=0)");
            foreach ($currency as $v) {
                $this->sys_list_currency[$v['cur_id']] = array(
                   'id'        => $v['cur_id'],
                   'name'      => $v['cur_name_value'],
                   'abbr'      => $v['cur_name'],
                   'charCode'  => $v['cur_char_code'],
                   'okv'       => $v['cur_okv_id'],
                   'country'   => $v['cur_country'],
                   'uses'      => $v['cur_uses']
                );
                foreach ($daily as $k => $val) {
                    if ($val['currency_id'] == $v['cur_id']) {
                        if ($val['user_id'] > 0) {
                            $this->sys_list_currency[$v['cur_id']]['value_user']  = $val['value'];
                            $this->sys_list_currency[$v['cur_id']]['date_user']   = $val['date'];
                            $this->sys_list_currency[$v['cur_id']]['direct_user'] = $val['direction'];
                        } else {
                            $this->sys_list_currency[$v['cur_id']]['value']       = $val['value'];
                            $this->sys_list_currency[$v['cur_id']]['date']        = $val['date'];
                            $this->sys_list_currency[$v['cur_id']]['direct']      = $val['direction'];
                        }
                    }
                }

                $this->sys_list_currency[1]['value']  = (float)1.0000;
                $this->sys_list_currency[1]['date']   = '';
                $this->sys_list_currency[1]['direct'] = 0;
            }

//            $sql = "SELECT cur_name_value AS name, cur_char_code AS `charCode`,
//                    cur_name AS abbr, currency_sum AS value, direction
//                FROM currency
//                LEFT JOIN daily_currency ON cur_id=currency_id
//                WHERE currency_date=CURRENT_DATE OR cur_id=1";
//            $daily = $this->db->select($sql);
//            foreach ($daily as $val) {
//                $this->sys_list_currency[$val['currency_id']] = array(
//                   'id'        => $val['currency_id'],
//                   'name'      => $daily['name'],
//                   'abbr'      => $daily['abbr'],
//                   'charCode'  => $daily['charCode'],
//                   'value'     => $daily['value'],
//                   'direction' => $daily['direction'],
//                );
//            }
        } else {
            $this->sys_list_currency = $daily;
        }
    }

    /**
     * Функция, часть реализации интерфейса IteratorAggregate
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->sys_list_currency);
    }

    /**
     * ArrayAccess implementation
     */
    public function offsetExists($offset)
    {
        return isset($this->sys_list_currency[$offset]);
    }

    public function offsetGet($offset )
    {
        return $this->sys_list_currency[$offset];
    }

    public function offsetSet($offset, $value )
    {
        $this->sys_list_currency[$offset] = $value;
    }

    public function offsetUnset($offset )
    {
        unset($this->sys_list_currency[$offset]);
    }
}