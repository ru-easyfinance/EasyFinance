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
     * Массив, содержащий список значений (полей) для расчёта (красный, зелёный, жёлтые)
     * @var array mixed
     * @example
     * 'profit' => array(<br/>
     *      'red' => array(<br/>
     *          1 => 0,<br/>
     *          2 => 1,<br/>
     *          3 => 0<br/>
     *      ),<br/>
     *      'yellow' => array(<br/>
     *          1 => 2,<br/>
     *          2 => 2,<br/>
     *          3 => 1<br/>
     *      ),<br/>
     *      'green' => array(<br/>
     *          1 => 5,<br/>
     *          2 => 3,<br/>
     *          3 => 2<br/>
     *      ),<br/>
     *      'weight' => 35
     *  ),
     */
    private $values = array(
        'profit' => array(
            'red' => array(
                1 => 0,
                2 => 1,
                3 => 0,
                'text' => 'Запас денег на нуле. Будьте осторожны в своих расходах.'
            ),
            'yellow' => array(
                1 => 2,
                2 => 2,
                3 => 1,
                'text' => 'Достаточная финансовая обеспеченность и есть пути для повышения.'
            ),
            'green' => array(
                1 => 5,
                2 => 3,
                3 => 2,
                'text' => 'У Вас солидный запас денег. Вам стоит задуматься об инвестициях.'
            ),
            'weight' => 35,
            'min' => 0,
            'max' => 100//6
        ),
        'budget' => array(
            'red' => array(
                1 => 97,
                2 => 1,
                3 => 0,
                'text' => 'Деньги исчезают в неизвестном направлении. Планируйте расходы.'
            ),
            'yellow' => array(
                1 => 85,
                2 => 2,
                3 => 1,
                'text' => 'Вы неплохо управляете расходами. Но постарайтесь быть экономней.'
            ),
            'green' => array(
                1 => 0,
                2 => 3,
                3 => 2,
                'text' => 'Вы умеете планировать и экономить. Так держать!'
            ),
            'weight' => 20,
            'min' => 0,
            'max' => 105//20
        ),
        'loans'  => array(
            'red' => array(
                1 => 70,
                2 => 1,
                3 => 0,
                'text' => 'Вы утратили финансовую независимость. Сократите Ваши кредиты.'
            ),
            'yellow' => array(
                1 => 40,
                2 => 2,
                3 => 1,
                'text' => 'Ваша долговая нагрузка в норме. Не злоупотребляйте кредитованием.'
            ),
            'green' => array(
                1 => 0,
                2 => 3,
                3 => 2,
                'text' => 'У Вас высокий уровень финансовой независимости.'
            ),
            'weight' => 15,
            'min' => 0,  //@TODO Узнать минимальную границу кредита
            'max' => 100 //@TODO Узнать максимальную границу кредита

        ),
        'drain'  => array(
            'red' => array(
                1 => 0,
                2 => 1,
                3 => 0,
                'text' => 'Вам следует либо больше зарабатывать, либо меньше тратить!'
            ),
            'yellow' => array(
                1 => 5,
                2 => 2,
                3 => 1,
                'text' => 'Хорошее соотношение, но есть резерв для улучшений!'
            ),
            'green' => array(
                1 => 10,
                2 => 3,
                3 => 2,
                'text' => 'Вы близки к достижению финансовой свободы!'
            ),
            'weight' => 30,
            'min' =>0,
            'max' =>200//100
        ),
        'result' => array(
            'min' => 0,
            'max' => 100,//300,
            'red' => array(
                'text' => 'Вам грозит банкротство. Срочно измените свой подход к финансовым вопросам!'
            ),
            'green' => array(
                'text' => 'У вас все хорошо, но не останавливайтесь на достигнутом. Ваше финансовое состояние можно существенно улучшить!'
            ),
            'yellow' => array(
                'text' => 'Неплохо. но если изменить  подход к ведению дел, финансовое состояние можно существенно улучшить.'
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
        $this->load();
        return $this->result();
    }

    /**
     * Выводит результат расчёта в виде массива с пояснениями
     */
    private function result()
    {
        if ( $this->input['profit'] == 0 )
            if ( $this->input['drain'] == 0 ){
                $this->output[6]['result'] = 0;
                $this->output[6]['profit'] = 0;
                $this->output[6]['budget'] = 0;
                $this->output[6]['drain'] = 0;

            }
        $des1 = $this->values['result']['red']['text'];
        if ( $this->output[6]['result'] >= 33) $des1 = $this->values['result']['yellow']['text'];
        if ( $this->output[6]['result'] >= 50) $des1 = $this->values['result']['green']['text'];
        $des2 = $this->values['profit']['red']['text'];
        if ( $this->output[6]['profit'] >= 2*10) $des2 = $this->values['profit']['yellow']['text'];
        if ( $this->output[6]['profit'] >= 5*10) $des2 = $this->values['profit']['green']['text'];
        $des3 = $this->values['budget']['red']['text'];
        if ( $this->output[6]['budget'] <= 97*100/105) $des3 = $this->values['budget']['yellow']['text'];
        if ( $this->output[6]['budget'] <= 85*100/105) $des3 = $this->values['budget']['green']['text'];
        $des5 = $this->values['drain']['red']['text'];
        if ( $this->output[6]['drain'] >= 5*5) $des5 = $this->values['drain']['yellow']['text'];
        if ( $this->output[6]['drain'] >= 10*5) $des5 = $this->values['drain']['green']['text'];
    return array(
            /*'values' => array(
                  round(@$this->output[6]['result'])    //Финансовое состояние
                , round(@$this->output[6]['profit'])    //Деньги
                , round(@$this->output[6]['budget'])    //Бюджет
                , 0 //round(@$this->output[6]['loans'])     //Кредиты
                , round(@$this->output[6]['drain'])     //Управление расходами
            ),
            'info' => array(*/
                array(
                    //'min'=>0,
                    //'color'=>'',
                    'value'=>round(@$this->output[6]['result']),
                    'description'=>$des1,
                    'title'=>'Итоговая оценка финансового состояния'
                ),
                array(
                    //'min'=>0,
                    //'color'=>'',
                    'value'=>round(@$this->output[6]['profit']),
                    'description'=>$des2,
                    'title'=>'Уровень денежных остатков (в кратности к 1 среднему месяцу расходов за предыдущие 3 месяца) за минусом долгов'
                ),
                array(
                    //'min'=>0,
                    //'color'=>'',
                    'value'=>round(@$this->output[6]['budget']),
                    'description'=>$des3,
                    'title'=>'Использование бюджета расходов (% использованного бюджета)'
                ),
                array(
                    //'min'=>0,
                    //'color'=>'',
                    'value'=>0,
                    'description'=>'',
                    'title'=>'Уровень выплат по кредитам (% от доходов)'
                ),
                array(
                    //'min'=>0,
                    //'color'=>'',
                    'value'=>round(@$this->output[6]['drain']),
                    'description'=>$des5,
                    'title'=>'Показатель превышения доходов над расходами (средний показ за 3 месяца)'
                ),
            //)
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
        
        $this->step0();
        $this->step1();
        $this->step2();
        $this->step3();
        $this->step4();
        $this->step5();
        $this->step6();
    }

    /**
     * Инициализирует данные
     * @return void
     */
    public function init() {
        // Доходы за прошедший месяц
        $sql = "SELECT SUM(o.money) FROM accounts a
            LEFT JOIN operation o ON a.account_id = o.account_id
            WHERE o.user_id = ? AND o.drain = 0 AND o.transfer = 0
            AND o.`date` BETWEEN ADDDATE(NOW(), INTERVAL -1 MONTH) AND NOW()";
        $this->input['profit']   = (float)$this->db->selectCell($sql, Core::getInstance()->user->getId());

        // Расходы за прошедший месяц
        $sql = "SELECT ABS(SUM(o.money)) FROM accounts a
            LEFT JOIN operation o ON a.account_id = o.account_id
            WHERE o.user_id = ? AND o.drain = 1 AND o.transfer = 0
            AND o.`date` BETWEEN ADDDATE(NOW(), INTERVAL -1 MONTH) AND NOW()";
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

//        $this->input['drain']    = 200000;
//        $this->input['profit']   = 240000;
//        $this->input['budget']   = 210000;
//        $this->input['loans']    = 21000;
//        $this->input['balance']  = 130000;
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
     * Корректируем расчёты
     */
    private function step0() {
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
        //@FIXME Кажется, тут может быть ошибка
        if ($this->input['drain'] == 0 && $this->input['profit'] > 0) {
            $this->output[1]['budget'] = 10;
        } else if ($this->input['profit'] == 0) {
            $this->output[1]['budget'] = 0;
        }
        /*if ($this->input['drain'] != 0 && $this->input['budget'] == 0){
            $this->output[1]['budget'] = 100;
        } else if ($this->input['budget'] != 0){
            $this->output[1]['budget'] = $this->input['budget'];
        } else {
            $this->output[1]['budget'] = 0;
        }*/

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
     * Расчёт тахометров, Факт значение (Расчет 1)
     */
    private function step1 ()
    {
        // Деньги
        if (!isset ($this->output[1]['profit'])) {
            if ($this->input['drain'] != 0) {
                $this->output[1]['profit'] = $this->input['balance'] / $this->input['drain'];
            } else {
                $this->output[1]['profit'] = 0;
            }
        }

        // Кредиты
        if (!isset ($this->output[1]['loans'])) {
            if ($this->input['profit'] != 0) {
                $this->output[1]['loans'] = $this->input['loans'] / $this->input['profit'] * 100;
            } else {
                $this->output[1]['loans'] = 0;
            }
        }

        // Бюджет / Доходы vs Расходы
        if (!isset ($this->output[1]['budget'])) {
            if ($this->input['budget'] != 0) {
                $this->output[1]['budget'] = $this->input['drain'] / $this->input['budget'] * 100;
            } else {
                $this->output[1]['budget'] = 0;
            }
        }

        // Расходы
        if (!isset ($this->output[1]['drain'])) {
            if ($this->input['drain'] != 0) {
                $this->output[1]['drain'] = $this->input['profit'] / $this->input['drain'];
            } else {
                $this->output[1]['drain'] = 0;
            }
        }
    }

    /**
     * Расчёт тахометров, Значение грубое (Расчет 2)
     */
    private function step2 ()
    {
        // Деньги
//        =IF((C10>E10);
//            IF((C10>F10);
//                I10
//            ;
//                H10
//            )
//        ;
//            G10
//        )
        if ($this->output[1]['profit'] > $this->values['profit']['yellow'][1]) {
            if ($this->output[1]['profit'] > $this->values['profit']['green'][1]) {
                $this->output[2]['profit'] = $this->values['profit']['green'][2];
            } else {
                $this->output[2]['profit'] = $this->values['profit']['yellow'][2];
            }
        } else {
            $this->output[2]['profit'] = $this->values['profit']['red'][2];
        }

        // Кредиты
//        =IF((C11<D11);
//            IF((C11<E11);
//                I11
//            ;
//                H11
//            )
//        ;
//            G11
//        )
        if ($this->output[1]['loans'] < $this->values['loans']['red'][1]) {
            if ($this->output[1]['loans'] < $this->values['loans']['yellow'][1]) {
                $this->output[2]['loans'] = $this->values['loans']['green'][2];
            } else {
                $this->output[2]['loans'] = $this->values['loans']['yellow'][2];
            }
        } else {
            $this->output[2]['loans'] = $this->values['loans']['red'][2];
        }

        // Расходы
//        =IF((C13>E13);
//            IF((C13>F13);
//                I13
//            ;
//                H13
//            )
//        ;
//            G13
//        )
        if ($this->output[1]['drain'] > $this->values['drain']['yellow'][1]) {
            if ($this->output[1]['drain'] > $this->values['drain']['green'][1]) {
                $this->output[2]['drain'] = $this->values['drain']['green'][2];
            } else {
                $this->output[2]['drain'] = $this->values['drain']['yellow'][2];
            }
        } else {
            $this->output[2]['drain'] = $this->values['drain']['red'][2];
        }

        // Бюджет
//        =IF((C12<D12);
//            IF((C12<E12);
//                I12
//            ;
//                H12
//            )
//        ;
//            G12
//        )
        if ($this->output[1]['budget'] < $this->values['budget']['red'][1]) {
            if ($this->output[1]['budget'] < $this->values['budget']['yellow'][1]) {
                $this->output[2]['budget'] = $this->values['budget']['green'][2];
            } else {
                $this->output[2]['budget'] = $this->values['budget']['yellow'][2];
            }
        } else {
            $this->output[2]['budget'] = $this->values['budget']['green'][2];
        }
    }

    /**
     * Расчёт тахометров, Точное значение без повышений (Расчет 3)
     */
    private function step3 ()
    {

//    =IF((N10=3);
//        (((C10-F10)/(6-F10)))
//    ;
//        IF((N10=2);
//            (((C10-E10)/(F10-E10)))
//        ;
//            (C10/E10)
//        )
//    )
        // Деньги
        if ($this->output[2]['profit'] == 3) {
            if ((6 - $this->values['profit']['green'][1]) != 0) {
                $this->output[3]['profit'] = ($this->output[1]['profit']
                    - $this->values['profit']['green'][1]) / (6 - $this->values['profit']['green'][1]);
            } else {
                $this->output[3]['profit'] = 0;
            }
        } elseif ($this->output[2]['profit'] == 2) {
            if (($this->values['profit']['green'][1] - $this->values['profit']['yellow'][1]) != 0) {
                $this->output[3]['profit'] = ($this->output[1]['profit'] - $this->values['profit']['yellow'][1])
                    /  ($this->values['profit']['green'][1] - $this->values['profit']['yellow'][1]);
            } else {
                $this->output[3]['profit'] = 0;
            }
        } else {
            if ($this->values['profit']['yellow'][1] != 0) {
                $this->output[3]['profit'] = $this->output[1]['profit']
                    / $this->values['profit']['yellow'][1];
            } else {
                $this->output[3]['profit'] = 0;
            }
        }
        
//    =IF((N11=1);
//        ((100-C11)/(100-D11))
//    ;
//        IF((N11=2);
//            (((D11-C11)/(D11-E11)))
//        ;
//            (((E11-C11)/E11))
//        )
//    )

        // Кредиты
        if ($this->output[2]['loans'] == 1) {
            if ((100 - $this->values['loans']['red'][1]) != 0) {
                $this->output[3]['loans'] = (100 - $this->output[1]['loans'])
                    / (100 - $this->values['loans']['red'][1]);
            } else {
                $this->output[3]['loans'] = 0;
            }
        } elseif ($this->output[2]['loans'] == 2) {
            if (($this->values['loans']['red'][1] - $this->values['loans']['yellow'][1]) != 0) {
                $this->output[3]['loans'] = ($this->values['loans']['red'][1] - $this->output[1]['loans'])
                    / ($this->values['loans']['red'][1] - $this->values['loans']['yellow'][1]);
            } else {
                $this->output[3]['loans'] = 0;
            }
        } else {
            if ($this->values['loans']['yellow'][1] != 0) {
                $this->output[3]['loans'] = ($this->values['loans']['yellow'][1]
                    - $this->output[1]['loans']) / $this->values['loans']['yellow'][1];
            } else {
                $this->output[3]['loans'] = 0;
            }
        }

//    =IF((N12=1);
//        ((100-C12)/(100-D12))
//    ;
//        IF((N12=2);
//            (((D12-C12)/(D12-E12)))
//        ;
//            (((E12-C12)/E12))
//        )
//    )

        // Бюджет
        if ($this->output[2]['budget'] == 1) {
            if ((100 - $this->values['budget']['red'][1]) != 0) {
                $this->output[3]['budget'] = (100 - $this->output[1]['budget'])
                    / (100 - $this->values['budget']['red'][1]);
            }
        } else {
            if ($this->output[2]['budget'] == 2) {
                if (($this->values['budget']['red'][1] - $this->values['budget']['yellow'][1]) != 0) {
                    $this->output[3]['budget'] = ($this->values['budget']['red'][1] - $this->output[1]['budget'])
                        / ($this->values['budget']['red'][1] - $this->values['budget']['yellow'][1]);
                } else {
                    $this->output[3]['budget'] = 0;
                }
            } else {
                if ($this->values['budget']['yellow'][1] != 0) {
                    $this->output[3]['budget'] = ($this->values['budget']['yellow'][1] - $this->output[1]['budget'])
                        / $this->values['budget']['yellow'][1];
                } else {
                    $this->output[3]['budget'] = 0;
                }
            }
        }

//    =IF((N13=3);
//        (((C13-F13)/(20-F13)))
//    ;
//        IF((N13=2);
//            (((C13-E13)/(F13-E13)))
//        ;
//            (C13/E13)
//        )
//    )
        // Расход против Доход
        if ($this->output[2]['drain'] == 3) {
            if ((20 - $this->values['drain']['green'][1]) != 0) {
                $this->output[3]['drain'] = ($this->output[1]['drain'] - $this->values['drain']['green'][1])
                    / (20 - $this->values['drain']['green'][1]);
            } else {
                $this->output[3]['drain'] = 0;
            }
        } else {
            if ($this->output[2]['drain'] == 2) {
                if (($this->values['drain']['green'][1] - $this->values['drain']['yellow'][1]) != 0) {
                    $this->output[3]['drain'] = ($this->output[1]['drain'] - $this->values['drain']['yellow'][1])
                        / ($this->values['drain']['green'][1] - $this->values['drain']['yellow'][1]);
                } else {
                    $this->output[3]['drain'] = 0;
                }
            } else {
                if ($this->values['drain']['yellow'][1] != 0) {
                    $this->output[3]['drain'] = $this->output[1]['drain'] / $this->values['drain']['yellow'][1];
                } else {
                    $this->output[3]['drain'] = 0;
                }
            }
        }
    }

    /**
     * Расчёт тахометров, Точное значение (Расчет 4)
     */
    private function step4 ()
    {
//    =IF((N10=3);
//        (O10+L10)
//    ;
//        IF((N10=2);
//            (O10+K10)
//        ;
//            O10
//        )
//    )
        // Деньги
        if ($this->output[2]['profit'] == 3) {
            $this->output[4]['profit'] = $this->output[3]['profit'] + $this->values['budget']['green'][3];
        } elseif ($this->output[2]['profit'] == 2) {
            $this->output[4]['profit'] = $this->output[3]['profit'] + $this->values['budget']['yellow'][3];
        } else {
            $this->output[4]['profit'] = $this->output[3]['profit'];
        }

//    =IF((N11=1);
//        O11
//    ;
//        IF((N11=2);
//            O11+K11
//        ;
//            (O11+L11)
//
//        )
//    )
        // Кредиты
        if ($this->output[2]['loans'] == 1) {
            $this->output[4]['loans'] = $this->output[3]['loans'];
        } elseif ($this->output[2]['profit'] == 2) {
            $this->output[4]['loans'] = $this->output[3]['loans'] + $this->values['loans']['yellow'][3];
        } else {
            $this->output[4]['loans'] = $this->output[3]['loans'] + $this->values['loans']['green'][3];
        }

//    =IF((N12=1);
//        O12
//    ;
//        IF((N12=2);
//            O12+K12
//        ;
//            O12+L12
//        )
//    )
        // Расходы
        if ($this->output[2]['drain'] == 1) {
            $this->output[4]['drain'] = $this->output[3]['drain'];
        } elseif ($this->output[2]['drain'] == 2) {
            $this->output[4]['drain'] = $this->output[3]['drain'] + $this->values['drain']['yellow'][3];
        } else {
            $this->output[4]['drain'] = $this->output[3]['drain'] + $this->values['drain']['green'][3];
        }

//    =IF((N13=3);
//        O13+L13
//    ;
//        IF((N13=2);
//            O13+K13
//        ;
//            O13
//        )
//    )
        // Бюджет
        if ($this->output[2]['budget'] == 3) {
            $this->output[4]['budget'] = $this->output[3]['budget'] + $this->values['drain']['green'][3];
        } elseif ($this->output[2]['budget'] == 2) {
            $this->output[4]['budget'] = $this->output[3]['budget'] + $this->values['drain']['yellow'][3];
        } else {
            $this->output[4]['budget'] = $this->output[3]['budget'];
        }
    }

    /**
     * Расчёт тахометров, Взвешенное значение (Расчет 5)
     */
    private function step5 ()
    {
//    =IF(((M10*P10))<0;
//        0
//    ;
//        ((M10*P10))
//    )
        // Деньги
        if (($this->output[4]['profit'] * $this->values['profit']['weight']) < 0) {
            $this->output[5]['profit'] = 0;
        } else {
            $this->output[5]['profit'] = ($this->output[4]['profit'] * $this->values['profit']['weight']);
        }

//    =IF(((M11*P11))<0;
//        0
//    ;
//        ((M11*P11))
//    )
        // Кредиты
        if (($this->output[4]['loans'] * $this->values['loans']['weight']) < 0) {
            $this->output[5]['loans'] = 0;
        } else {
            $this->output[5]['loans'] = $this->output[4]['loans'] * $this->values['loans']['weight'];
        }

//    =IF(((M12*P12))<0;
//        0
//    ;
//        ((M12*P12))
//
//    )
        // Бюджет
        if (($this->output[4]['budget'] * $this->values['budget']['weight']) < 0) {
            //$this->output[5]['budget'] = 0;
        } else {
            $this->output[5]['budget'] = $this->output[4]['budget'] * $this->values['budget']['weight'];
        }

//    =IF(((M13*P13))<0;
//        0
//    ;
//        ((M13*P13))
//    )
        // Расход vs Доход
        if (($this->output[4]['drain'] * $this->values['drain']['weight']) < 0) {
            $this->output[5]['drain'] = 0;
        } else {
            $this->output[5]['drain'] = $this->output[4]['drain'] * $this->values['drain']['weight'];
        }

        $this->output[5]['result'] = $this->output[5]['profit'] + $this->output[5]['drain'] +
            $this->output[5]['loans'] + $this->output[5]['budget'];
    }


    /**
     * Корректируем данные, если они выходят за границы
     */
    private function step6 ()
    {
        // Деньги (Доходы)
        if ($this->output[5]['profit'] > $this->values['profit']['max']) {
            $this->output[6]['profit'] = $this->values['profit']['max'];
        } elseif ($this->output[5]['profit'] < $this->values['profit']['min']) {
            $this->output[6]['profit'] = $this->values['profit']['min'];
        } else {
            $this->output[6]['profit'] = $this->output[5]['profit'];
        }
        // Высчитываем проценты
        $this->output[6]['profit'] = ($this->output[6]['profit'] / $this->values['profit']['max']) * 100;

        // Расходы
        if ($this->output[5]['drain'] > $this->values['drain']['max']) {
            $this->output[6]['drain'] = $this->values['drain']['max'];
        } elseif ($this->output[5]['drain'] < $this->values['drain']['min']) {
            $this->output[6]['drain'] = $this->values['drain']['min'];
        } else {
            $this->output[6]['drain'] = $this->output[5]['drain'];
        }
        $this->output[6]['drain'] = ($this->output[6]['drain'] / $this->values['drain']['max']) * 100;

        // Кредиты
        if ($this->output[5]['loans'] > $this->values['loans']['max']) {
            $this->output[6]['loans'] = $this->values['loans']['max'];
        } elseif ($this->output[5]['loans'] < $this->values['loans']['min']) {
            $this->output[6]['loans'] = $this->values['loans']['min'];
        } else {
            $this->output[6]['loans'] = $this->output[5]['loans'];
        }
        $this->output[6]['loans'] = ($this->output[6]['loans'] / $this->values['loans']['max']) * 100;

        // Бюджет
        if ($this->output[5]['budget'] > $this->values['budget']['max']) {
            $this->output[6]['budget'] = $this->values['budget']['max'];
        } elseif ($this->output[5]['budget'] < $this->values['budget']['min']) {
            $this->output[6]['budget'] = $this->values['budget']['min'];
        } else {
            $this->output[6]['budget'] = $this->output[5]['budget'];
        }
        $this->output[6]['budget'] = ($this->output[6]['budget'] / $this->values['budget']['max']) * 100;

        // Фин. Состояние
        if ($this->output[5]['result'] > $this->values['result']['max']) {
            $this->output[6]['result'] = $this->values['result']['max'];
        } elseif ($this->output[5]['result'] < $this->values['result']['min']) {
            $this->output[6]['result'] = $this->values['result']['min'];
        } else {
            $this->output[6]['result'] = $this->output[5]['result'];
        }
        $this->output[6]['result'] = ($this->output[6]['result'] / $this->values['result']['max']) * 100;
    }
}