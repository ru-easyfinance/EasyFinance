<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс модели для управления счетами пользователя
 * @copyright http://easyfinance.ru/
 * @author rewle Александр Ильичёв
 * SVN $Id: $
 */
class Info_Model
{
    /**
     * Ссылка на экземпляр DBSimple
     * @var DbSimple_Mysql
     */
    private $db = NULL;

    /**
     * Уровень денежных остатков (в кратности к 1 ср. месяцу расходов за пред 3 мес)
     * @var float
     */
    private $money = 0;

    /**
     * Массив с входными данными пользователя для рассчёта
     * @var array mixed
     * @example
     * profit - Доход за последний месяц<br/>
     * drain - Расход за последний месяц<br/>
     * loans - Выплаты по кредитам за прошедший месяц<br/>
     * budget - Бюджет расходов за прошедший месяц<br/>
     * balance - Положительный баланс всех денег на счетах<br/>
     */
    private $input = array();

    /**
     * Массив с рассчитанными данными
     * @var array mixed
     * @example
     * result - Финансовое состояние<br/>
     * profit - Доход (Деньги)<br/>
     * budget - Бюджет <br/>
     * loans  - Кредиты<br/>
     * drain  - Расход<br/>
     */
    private $output = array();

    /**
     * Сохраняет рассчёты по шагам, структура аналогична $output
     * @var array
     */
    private $steps = array(1=>array(),2=>array(),3=>array());

    /**
     *
     * @var array mixed
     * @example
     * 'profit' => array(
     *      'red' => array(
                1 => 0,
                2 => 1,
                3 => 0
            ),
            'yellow' => array(
                1 => 2,
                2 => 2,
                3 => 1
            ),
            'green' => array(
                1 => 5,
                2 => 3,
                3 => 2
            )
        ),
     */
    private $values = array(
        'profit' => array(
            'red' => array(
                1 => 0,
                2 => 1,
                3 => 0
            ),
            'yellow' => array(
                1 => 2,
                2 => 2,
                3 => 1
            ),
            'green' => array(
                1 => 5,
                2 => 3,
                3 => 2
            )
        ),
        'budget' => array(
            'red' => array(
                1 => 0,
                2 => 1,
                3 => 0
            ),
            'yellow' => array(
                1 => 5,
                2 => 2,
                3 => 1
            ),
            'green' => array(
                1 => 10,
                2 => 3,
                3 => 2
            )
        ),
        'loans'  => array(
            'red' => array(
                1 => 70,
                2 => 1,
                3 => 0
            ),
            'yellow' => array(
                1 => 40,
                2 => 2,
                3 => 1
            ),
            'green' => array(
                1 => 0,
                2 => 3,
                3 => 2
            )
        ),
        'drain'  => array(
            'red' => array(
                1 => 97,
                2 => 1,
                3 => 0
            ),
            'yellow' => array(
                1 => 85,
                2 => 2,
                3 => 1
            ),
            'green' => array(
                1 => 0,
                2 => 3,
                3 => 2
            )
        )
    );

    /**
     * Конструктор
     * @return void
     */
    public function __construct()
    {
        $this->db = Core::getInstance()->db;
    }

    /**
     * Возвращает информацию для тахометров
     * @return array mixed
     */
    public function get_data()
    {
        //$this->load();
        
/*
        [
            [20,0,0,0,20],
            [
                {
                  "min":"0",
                  "color":"0",
                  "description":"0",
                  "title":"\u0414\u0435\u043d\u044c\u0433\u0438"
                },{
                  "min":"0",
                  "color":null,
                  "description":null,
                  "title":"\u0411\u044e\u0434\u0436\u0435\u0442"
                },{
                  "min":"97",
                  "color":null,
                  "description":null,
                  "title":"\u041a\u0440\u0435\u0434\u0438\u0442\u044b"
                },{
                  "min":"0",
                  "color":null,
                  "description":null,
                  "title":"\u0420\u0430\u0441\u0445\u043e\u0434\u044b"
                },{
                    "min":"0",
                    "color":null,
                    "description":null,
                    "title":"\u0424\u0438\u043d.\u0441\u043e\u0441\u0442\u043e\u044f\u043d"
                }
            ]
        ]
*/
        return array(
            array(
                  83    //Финансовое состояние
                , 11    //Деньги
                , 7    //Бюджет
                , 0    //Кредиты
                , 23    //Управление расходами
            ),
            array(
                array(
                    'min'=>0, 'color'=>'', 'description'=>'','title'=>''
                ),
                array(
                    'min'=>0, 'color'=>'', 'description'=>'','title'=>''
                ),
                array(
                    'min'=>0, 'color'=>'', 'description'=>'','title'=>''
                ),
                array(
                    'min'=>0, 'color'=>'', 'description'=>'','title'=>''
                ),
                array(
                    'min'=>0, 'color'=>'', 'description'=>'','title'=>''
                ),
            )
        );
    }

    /**
     * Загружает данные
     * @return void
     */
    public function load()
    {
        if (isset($_SESSION['info'])) {
            $this->input['balance']  = (float)$_SESSION['info']['balance'];
            $this->input['budget']   = (float)$_SESSION['info']['budget'];
            $this->input['drain']    = (float)$_SESSION['info']['drain'];
            $this->input['loans']    = (float)$_SESSION['info']['loans'];
            $this->input['money']    = (float)$_SESSION['info']['money'];
            $this->input['profit']   = (float)$_SESSION['info']['profit'];
        } else {
            $this->init();
            $this->save();
        }
        $this->init();
        
        $this->correct1();
        $this->step1();
        $this->step2();
        $this->step3();
        print '<pre>';
        print_r($this->input);
        die(print_r($this->output));
    }

    /**
     * Инициализирует данные
     * @return void
     */
    public function init() {
        // Доходы за прошедший месяц
        $sql = "SELECT SUM(money) FROM operation WHERE user_id = ? AND drain = 0 AND transfer = 0
            AND `date` BETWEEN ADDDATE(NOW(), INTERVAL -1 MONTH) AND NOW()";
        $this->input['profit']   = (float)$this->db->selectCell($sql, Core::getInstance()->user->getId());

        // Расходы за прошедший месяц
        $sql = "SELECT ABS(SUM(money)) FROM operation WHERE user_id = ? AND drain = 1 AND transfer = 0
            AND `date` BETWEEN ADDDATE(NOW(), INTERVAL -1 MONTH) AND NOW()";
        $this->input['drain']    = (float)$this->db->selectCell($sql, Core::getInstance()->user->getId());

        // Бюджет за прошедший месяц
        $sql = "SELECT SUM(amount) FROM budget WHERE user_id = ? AND drain=1
            AND date_start = CONCAT(DATE_FORMAT(LAST_DAY(NOW() - INTERVAL 1 MONTH),'%Y-%m-'),'01')";
        $this->input['budget']   = (float)$this->db->selectCell($sql, Core::getInstance()->user->getId());

//        // Выплаты по кредитам за прошедший месяц
//        $accounts = '';
//        foreach (Core::getInstance()->user->getUserAccounts() as $key => $value) {
//            if ($value['account_type_id'] == 8 || $value['account_type_id'] == 9) {
//                if (!empty($accounts)) { $accounts .= ','; }
//                $accounts .= $key;
//            }
//        }
//        $sql = "SELECT SUM(ABS(money)) FROM operation o WHERE account_id IN ({$accounts})";
//        $this->input['loans']    = (float)$this->db->selectCell($sql);
        $this->input['loans'] = 0;

        // Остатки денег на конец прошедшего месяца
        $this->input['balance']  = 0;
        foreach (Core::getInstance()->user->getUserAccounts() as $key => $value) {
            // @TODO Дополнить фильтры
            if ($value['account_type_id'] != 8 || $value['account_type_id'] != 9) {
                $this->input['balance']  += (float)$value['total_sum'];
            }
        }

        // DUMP TEST
        $this->input['profit']  = 240000.00;
        $this->input['drain']   = 200000.00;
        $this->input['loans']   = 21000.00;
        $this->input['budget']  = 210000.00;
        $this->input['balance'] = 130000.00;
    }
    
    /**
     * Сохраняет рассчитанные данные в кеше
     * @return void
     */
    public function save() {
        // Входные данные для расчёта
        $_SESSION['info']['input']['balance'] = (float)$this->balance;
        $_SESSION['info']['input']['budget']  = (float)$this->budget;
        $_SESSION['info']['input']['drain']   = (float)$this->drain;
        $_SESSION['info']['input']['loans']   = (float)$this->loans;
        $_SESSION['info']['input']['profit']  = (float)$this->profit;
    }

    /**
     * Корректируем расчёты, шаг 1
     */
    private function correct1() {
        // если значение 0 и при этом показатель "С" больше 0, то расчет 1 для тахометра "Кредиты" = 100
        if ($this->input['profit'] == 0 && $this->input['loans'] > 0) {
            $this->output[1]['loans'] = 100;
        }
        
        //если значение 0 и при этом показатель "F" больше 0, то расчет 1 для тахометра "Деньги" = 5,
        //если и там 0, то "Деньги" = 0; 
        //Также если значение 0 и при этом показатель "A" больше 0,
        //то расчет 1 для тахометра "Доходы vs Расходы" = 10, если и там 0, то "Доходы vs Расходы" = 0
        if ($this->input['drain'] == 0 && $this->input['balance'] > 0) {
            $this->output[1]['profit'] = 5;
        }
        if ($this->input['balance'] == 0) {
            $this->output[1]['profit'] = 0;
        }
        //@FIXME
        if ($this->input['drain'] == 0 && $this->input['profit'] > 0) {
            $this->output[1]['budget'] = 10;
        } else if ($this->input['profit'] == 0) {
            $this->output[1]['budget'] = 0;
        }

        //если 0 то расчет 1 для тахометра "Кредиты" 0
        if ($this->input['loans'] == 0) {
            $this->output[1]['loans'] = 0;
        }

        //если значение 0  тогда расчет 1 для тахометра "Бюджет" = 100
        if ($this->input['drain'] == 0) {
            $this->output[1]['budget'] = 100;
        }

        //если 0 то расчет 1 для тахометра "Деньги" = 0
        if ($this->input['balance'] == 0) {
            $this->output[1]['profit'] = 0;
        }
    }

    /**
     * Расчёт тахометров, первый шаг
     */
    private function step1 ()
    {
        // Деньги
        if (!isset ($this->output[1]['profit'])) {
            $this->output[1]['profit'] = $this->input['balance'] / $this->input['drain'];
        }

        // Кредиты
        if (!isset ($this->output[1]['loans'])) {
            $this->output[1]['loans'] = $this->input['loans'] / $this->input['profit'] * 100;
        }

        // Расходы
        if (!isset ($this->output[1]['drain'])) {
            $this->output[1]['drain'] = $this->input['drain'] / $this->input['budget'] * 100;
        }

        // Бюджет / Доходы vs Расходы
        if (!isset ($this->output[1]['budget'])) {
            $this->output[1]['budget'] = $this->input['profit'] / $this->input['drain'];
        }
    }

    /**
     * Расчёт тахометров, второй шаг
     */
    private function step2 ()
    {
        // Деньги
        // Если Расчет 1 меньше желтой границы, то 1
        // Если меньше зеленой границы то 2
        // Если больше зеленой то 3
        if ($this->output[1]['profit'] < $this->values['profit']['yellow'][1]) {
            $this->output[2]['profit'] = 1;
        } elseif ($this->output[1]['profit'] < $this->values['profit']['green'][1]) {
              $this->output[2]['profit'] = 2;
        } elseif ($this->output[1]['profit'] > $this->values['profit']['green'][1]) {
              $this->output[2]['profit'] = 3;
        }

        // Кредиты
        // Если Расчет 1 меньше желтой границы, то 3
        // Если между желтой и красной то 2
        // Если больше красной то 1
        if ($this->output[1]['loans'] < $this->values['loans']['yellow'][1]) {
            $this->output[2]['loans'] = 3;
        } elseif ($this->output[1]['loans'] > $this->values['loans']['yellow'][1]
            && $this->output[1]['loans'] < $this->values['loans']['red'][1]) {
               $this->output[2]['loans'] = 2;
        } elseif ($this->output[1]['loans'] > $this->values['loans']['red'][1]) {
            $this->output[2]['loans'] = 1;
        }

        // Расходы
        // Если Расчет 1 меньше желтой границы, то 3
        // Если между желтой и красной то 2
        // Если больше красной то 1
        if ($this->output[1]['drain'] < $this->values['drian']['yellow'][1]) {
            $this->output[2]['drain'] = 3;
        } elseif ($this->output[1]['drain'] > $this->values['drain']['yellow'][1]
            && $this->output[1]['drain'] < $this->values['drain']['red'][1]) {
                $this->output[2]['drain'] = 2;
        } elseif ($this->output[1]['drain'] > $this->values['drain']['red'][1]) {
            $this->output[2]['drain'] = 1;
        }

        // Бюджет
        // Если Расчет 1 меньше желтой границы, то 1
        // Если меньше зеленой границы то 2
        // Если больше зеленой то 3
        if ($this->output[1]['budget'] < $this->values['budget']['yellow'][1]) {
            $this->output[2]['budget'] = 1;
        } elseif ($this->output[1]['budget'] < $this->values['budget']['green'][1]) {
              $this->output[2]['budget'] = 2;
        } elseif ($this->output[1]['budget'] > $this->values['budget']['green'][1]) {
              $this->output[2]['budget'] = 3;
        }
    }

    /**
     * Расчёт тахометров, третий шаг
     */
    private function step3 ()
    {
//        =IF((N10=3);
//            (((C10-F10)/(6-F10)));
//            IF((N10=2);
//                (((C10-E10)/(F10-E10)));
//                    (C10/E10)
//            )
//
//        )
    }

/*
    //расчётные функции//////////////////////////////////////////////////////
    //получение данныхы//////////////////////////////////////////////////////
    private function get_profit()//a
    {
        $money = array(1,2,5);//@todo включить агрегат в счета
        $total = 0;
        foreach($this->accounts as $key=>$val)
        {
            $summ = $this->operation_model->getTotalSum($val['account_id'],0);
            $ru_summ = $suum * Core::getInstance()->currency[$val['account_currency_id']]['value'];
            $total = $total + $ru_summ;
        }
        return (int)$total;
    }
    
    private function get_expense()//b
    {
        $total = 0;
        foreach($this->accounts as $key=>$val)
        {
            $summ = $this->operation_model->getTotalSum($val['account_id'],1);
            $ru_summ = $suum * Core::getInstance()->currency[$val['account_currency_id']]['value'];
            $total = $total + $ru_summ;
        }
        return (int)$total;
    }

    private function credit_expense()//c
    {
    return 10;///todo
    }

    private function expense_plan()//d
    {
        return ($this->b)*0.849;
    }

    //first supper function))
    private function get_money()//f
    {
        $money = array(1,2,5);//@todo включить агрегат в счета
        $total = 0;
        foreach($this->accounts as $key=>$val)
        {
            if (in_array($val['account_type_id'], $money))
            {
                $summ = $this->operation_model->getTotalSum($val['account_id']);
                $ru_summ = $suum * Core::getInstance()->currency[$val['account_currency_id']]['value'];
                $total = $total + $ru_summ;
            }
        }
        return (int)$total;
    }
    
    private function get_setting_table()
    {
        $sql = "SELECT * FROM info_calc";
        $this->table = $this->db->select($sql);
    }
    
    //расчёт даннх////////////////////////////////////////////////////////////////////
    private function money($def)//1
    {
        $i = 0;
        $x= (int) $def;
        if (!strval($def))
            $x=($this->f)/($this->b);

        $this->calc['money']=$x;

        if ($x < $this->table[$i]['m_y'])
        {
            $y = $this->table[$i]['c_r'];
            $z = ($x-$y)/(6-$y);
            $t = $z+$this->table[$i]['u_r'];
        }
        else if($x < $this->table[$i]['m_g'])
        {
            $y = $this->table[$i]['c_y'];
            $z= ($x-$y)/($this->table[$i]['m_y']-$y);
            $t = $z+$this->table[$i]['u_y'];
        }
        else
        {
            $y = $this->table[$i]['c_g'];
            $z= ($x)/($this->table[$i]['m_y']);
            $t = $z;
        }
        $ret = (int)$t*($this->table[$i]['weight']);
        if ($ret > 0)
            return $ret;
        return 0;
    }

    private function upper()//4
    {
        $i=3;

        //@FIXME Деление на 0
        if ($this->b != 0) {
            $x = $this->a / $this->b;
        } else {
            $x = 0;
        }

        $this->calc['upper']=$x;
        if ($x < $this->table[$i]['m_y'])
        {
            $y = $this->table[$i]['c_r'];
            $z = ($x-$y)/(20-$y);
            $t = $z+$this->table[$i]['u_r'];
        } else if($x < $this->table[$i]['m_g']) {
            $y = $this->table[$i]['c_y'];
            $z= ($x-$y)/($this->table[$i]['m_y']-$y);
            $t = $z+$this->table[$i]['u_y'];
        }
        else
        {
            $y = $this->table[$i]['c_g'];
            $z= ($x)/($this->table[$i]['m_y']);
            $t = $z;
        }
        (int)$ret = $t*($this->table[$i]['weight']);
        if ($ret > 0)
            return $ret;
        return 0;
    }

    private function credit ($def)//1
    {

        $i=1;
        $x= (int) $def;
        if (!$def)
            $x=(($this->c)/($this->a))*100;

            $this->calc['credit']=$x;

        if ($x < $this->table[$i]['m_y'])
        {
            $y = $this->table[$i]['c_g'];
            $z = ($this->table[$i]['c_y']-$x)/($this->table[$i]['c_y']);
            $t = $z+$this->table[$i]['u_r'];
        }
        else if($x < $this->table[$i]['m_y'])
        {
            $y = $this->table[$i]['c_y'];
            $z= ($this->table[$i]['c_r']-$x)/($this->table[$i]['c_r']-y);
            $t = $z+$this->table[$i]['u_y'];
        }
        else
        {
            $y = $this->table[$i]['c_r'];
            $z= (100-$x)/(100-$y);
            $t = $z;
        }
        (int)$ret = $t*($this->table[$i]['weight']);
        if ($ret > 0)
            return $ret;
        return 0;
    }

    private function expens ($def)//2
    {
        $i=2;

        $x= (int) $def;

        if (!$def) {
            //@FIXME Деление на 0
            if ((int)$this->d != 0) {
                $x = ($this->b / $this->d) * 100;
            } else {
                $x = 0;
            }
        }
        
        $this->calc['expens']=$x;

        if ($x < $this->table[$i]['m_y'])
        {
            $y = $this->table[$i]['c_g'];
            $z = ($this->table[$i]['c_y']-$x)/($this->table[$i]['c_y']);
            $t = $z+$this->table[$i]['u_r'];
        }
        else if($x < $this->table[$i]['m_y'])
        {
            $y = $this->table[$i]['c_y'];
            $z= ($this->table[$i]['c_r']-$x)/($this->table[$i]['c_r']-y);
            $t = $z+$this->table[$i]['u_y'];
        }
        else
        {
            $y = $this->table[$i]['c_r'];
            $z= (100-$x)/(100-$y);
            $t = $z;
        }
        $ret = $t*($this->table[$i]['weight']);
        if ($ret > 0)
            return $ret;
        return 0;
    }

    public function generate_value()
    {
        $this->a = $this->get_profit();
        $this->b = $this->get_expense();
        $this->c = $this->credit_expense();
        $this->d = $this->expense_plan();
        $this->f = $this->get_money();
        $this->get_setting_table();
        
        ////////////////////////////////////////////////////////////////////
        if (($this->a == 0) and ($this->c > 0))
            $d_credit = 100;
        if (($this->b == 0) and ($this->f > 0))
            $d_money = 5;
        ////////////////////////////////////!!!!
        if (($this->b == 0) and ($this->a > 0))
            $d_expens = 10;
        if (($this->a == 0) and ($this->b == 0))
            $expense = 0;
        if ($this->d == 0)
            $d_money = 0;
        if (($this->b == 0) and ($this->f == 0)){
            $money = 0;
            $this->calc['money']=0;
        }else
             $money = $this->money($d_money);
        //////////////////////////////////////////////////////////////////////
       
        $upper = $this->upper();
        $credit = $this->credit($d_credit);
        $expens = $this->expens($d_expens);
        $this->calc['fin_cond']=$money+$upper+$credit+$expens;
        $ret = array($money+$upper+$credit+$expens,$money,$upper,$credit,$expens);
        return $ret;

    }

    //собирательные функции/////////////////////////////////////////////////////////////
    public function tohometrs()
    {
                
        $values = $this->generate_value();
       
        $sql = "SELECT `min`,color,description,title FROM info_desc WHERE (`min`<=? and `type`=?) ORDER BY `min` DESC;";
        $desc = array();
        //die (print_r($this->calc));
        foreach ($this->calc as $key=>$val)
        {
            $desc[] = $this->db->selectRow($sql,$val,$key);
        }

        $ret = array($values,$desc);
        return $ret;
    }
*/
}
