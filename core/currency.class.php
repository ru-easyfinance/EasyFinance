<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс для управления валютами, реализует SPL-интерфейс IteratorAggregate
 * @category currency
 * @author Max Kamashev (ukko) <max.kamashev@gmail.com>
 * @copyright http://home-money.ru/
 * @version SVN $Id$
 */
class Currency implements IteratorAggregate
{
     /**
     * Ссылка на экземпляр DBSimple
     * @var <DbSimple_Mysql>
     */
    private $db;

    /**
     * Массив с системными валютами
     * @var <array> mixed
     */
    protected $sys_list_currency;

    /**
     * Конструктор класса
     * @return void
     */
    function __construct()
    {
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
        if (!include_once (dirname(dirname(__FILE__)).'/include/daily_currency.php')) {
            $daily = null;
        }
        if ($daily == null) {
            $sql = "SELECT cur_name_value AS name, cur_char_code AS `charCode`,
                    cur_name AS abbr, currency_sum AS value, direction
                FROM currency
                LEFT JOIN daily_currency ON cur_id=currency_id
                WHERE currency_date=CURRENT_DATE OR cur_id=1";
            $daily = $this->db->select($sql);
            foreach ($daily as $val) {
                $this->sys_list_currency[$val['currency_id']] = array(
                   'name'      => $daily['name'],
                   'abbr'      => $daily['abbr'],
                   'charCode'  => $daily['charCode'],
                   'value'     => $daily['value'],
                   'direction' => $daily['direction'],
                );
            }
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
}