<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс модели для управления счетами пользователя
 * @copyright http://home-money.ru/
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
     * Ид текущего пользователя
     * @var int
     */
    private $user_id = NULL;

    private $a=0;
    private $b=0;
    private $c=0;
    private $d=0;
    private $f=0;
    private $calc=array();//for color and desc
    private $accounts = null;
    private $table = null;
    /**
     * Ссылка на модель с операциями
     * @var Operation_Model
     */
    private $operation_model = NULL;

    /**
     * Ссылка на модель со счетами
     * @var Operation_Model
     */
    //private $account_model = NULL;
    /**
     * Конструктор
     * @return void
     */
    public function __construct()
    {
        $this->db = Core::getInstance()->db;
        $this->user_id = Core::getInstance()->user->getId();
        $this->operation_model = new Operation_Model();
        //$this->account_model = new Accounts_Model();
        $sql = "SELECT
                    account_type_id, account_currency_id, account_id
                FROM
                    accounts
                WHERE 
                    user_id=?";
        $this->accounts = $this->db->select($sql,$this->user_id);
    }

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
        $ret = $t*($this->table[$i]['weight']);
        if ($ret > 0)
            return $ret;
        return 0;

    }

    private function upper()//4
    {
        $i=3;
         $x=($this->a)/($this->b);
         $this->calc['upper']=$x;
        if ($x < $this->table[$i]['m_y'])
        {
            $y = $this->table[$i]['c_r'];
            $z = ($x-$y)/(20-$y);
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
        $ret = $t*($this->table[$i]['weight']);
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
        $ret = $t*($this->table[$i]['weight']);
        if ($ret > 0)
            return $ret;
        return 0;
    }

    private function expens ($def)//2
    {
        $i=2;
        $x= (int) $def;
        if (!$def)
            $x=(($this->b)/($this->d))*100;

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

    private function generate_value()
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
        $ret = array($money,$upper,$credit,$expens,$money+$upper+$credit+$expens);
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
}
