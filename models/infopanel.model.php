<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс модели для управления счетами пользователя
 * @copyright http://home-money.ru/
 * @author rewle Александр Ильичёв
 * SVN $Id: $
 */
class Infopanel_Model
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

    /**
     * Конструктор
     * @return void
     */
    public function __construct()
    {
        
        $this->db = Core::getInstance()->db;
        $this->user_id = Core::getInstance()->user->getId();
    }

    /**
     * возвращает контент()
     * @return void
     */
    public function content($i, $type, $date)
    {
        $title_list=array(
            'fcon'=>'Фин. состояние',
            'money'=>'Деньги',
            'budget'=>'Бюджет',
            'cost'=>'Затраты',
            'credit'=>'Кредиты',
            'akc'=>'Акции',
            'pif'=>'ПИФы',
            'ofbu'=>'ОФБУ',
            'oms'=>'ОМС',
            'estat'=>'Недвижимость');
        switch ($i)
        {
            case 1:
                $this->xml($type, $date);
                break;
            case 2:
                $targets = new Targets_Model();
                die(json_encode($targets->getLastList(0, (int)$_SESSION['targets_count'])));
                break;
            case 3:
                //correct value from date @todo correct
                  $key = array('fcon'=>0,
                    'money'=>1,
                    'budget'=>2,
                    'cost'=>3,
                    'credit'=>4,
                    'akc'=>5,
                    'pif'=>6,
                    'ofbu'=>7,
                    'oms'=>8,
                    'estat'=>9,
                    'akc_year'=>10,
                    'pif_year'=>11,
                    'ofbu_year'=>12,
                    'oms_year'=>13,
                    'estat_year'=>14
                );
                    if (!$_SESSION['infopanel'])
                    {
                        $sql = "SELECT `value`,`type` FROM infopanel_value WHERE uid=? ;";// date='' AND
                        $_SESSION['infopanel'] =$this->db->selectcol($sql, /*$date,*/ $this->user_id);
                    }

                $name = $title_list[$type];
                $value = array('day'=>$_SESSION['infopanel'][$key[$type]],
                    'year'=>$_SESSION['infopanel'][$key[$type.'_year']],
                    'name'=>$name);
                die(json_encode($value));
                break;
            default :
                die('');
                break;
        }
    }

    /**
     * возвращает значение для показателей в панели инвестиций
     * @return void
     */
    public function page($date, $type)
    {
        if (!$_SESSION['infopanel'])
        {
            $sql = "SELECT `value` FROM infopanel_value WHERE date=? AND uid=?;";
            $_SESSION['infopanel'] =$this->db->selectRow($sql, $date, $this->user_id);
        }
      $key = array('fcon'=>0,
	'money'=>1,
 	'budget'=>2,
 	'cost'=>3,
 	'credit'=>4,
 	'akc'=>5,
 	'pif'=>6,
 	'ofbu'=>7,
 	'oms'=>8,
 	'estat'=>9,
 	'akc_year'=>10,
 	'pif_year'=>11,
 	'ofbu_year'=>12,
 	'oms_year'=>13,
 	'estat_year'=>14
    );
        if (!$_SESSION['infopanel'])
        {
            $sql = "SELECT `value`,`type` FROM infopanel_value WHERE uid=? ;";// date='' AND
            $_SESSION['infopanel'] =$this->db->selectcol($sql, /*$date,*/ $this->user_id);
        }
        $i=$key[$type];
        $value = $_SESSION['infopanel'][$i];
        if (!$value)
            die('Ненайдено значение');
        $sql = "SELECT `desc` FROM infopanel_desc WHERE type=? AND (ISNULL(start) OR start<?) AND (ISNULL(end) OR end>?);";
        $row =$this->db->selectRow($sql, $type, $value, $value);
        die($row['desc']);
    }
    
    public function xml($type, $date)
    {
    $title_list=array(
            'fcon'=>'Фин. состояние',
            'money'=>'Деньги',
            'budget'=>'Бюджет',
            'cost'=>'Затраты',
            'credit'=>'Кредиты');

    $name = $title_list[$type];
  //correct value from date
      $key = array('fcon'=>0,
	'money'=>1,
 	'budget'=>2,
 	'cost'=>3,
 	'credit'=>4,
 	'akc'=>5,
 	'pif'=>6,
 	'ofbu'=>7,
 	'oms'=>8,
 	'estat'=>9,
 	'akc_year'=>10,
 	'pif_year'=>11,
 	'ofbu_year'=>12,
 	'oms_year'=>13,
 	'estat_year'=>14
    );
        if (!$_SESSION['infopanel'])
        {
            $sql = "SELECT `value`,`type` FROM infopanel_value WHERE uid=? ;";// date='' AND
            $_SESSION['infopanel'] =$this->db->selectcol($sql, /*$date,*/ $this->user_id);
        }
        $i=$key[$type];
        $value = $_SESSION['infopanel'][$i];
        if (!$value)
            die(print_r($_SESSION['infopanel']));
        $sql = "SELECT MAX(`start`),MAX(`end`) FROM infopanel_desc WHERE type=?;";
        $row = $this->db->selectRow($sql, $type);
	$start = ($row['start']) ? ($row['start']) : 0;
	$end = ($row['end']) ? ($row['end']) : 0;

        $xml = "
<anychart>
    <gauges>
	<gauge>
            <chart_settings>
		<title>
                    <text>$name</text>
		</title>
            </chart_settings>
            <circular>
		<axis radius='50' start_angle='85' sweep_angle='190' size='3'>
                    <labels enabled='false'>
                    </labels>
                    <scale_bar enabled='false'>
                    </scale_bar>
                    <major_tickmark enabled='false'/>
                    <minor_tickmark enabled='false'/>
                    <color_ranges>
                        <color_range start='$start' end='$end' align='Inside' start_size='15' end_size='15' padding='6'>
                            <fill type='Gradient'>
                                <gradient>
                                    <key color='Red'/>
                                    <key color='Yellow'/>
                                    <key color='Green'/>
				</gradient>
                            </fill>
                            <border enabled='true' color='#FFFFFF' opacity='0.4'/>
			</color_range>
                    </color_ranges>
		</axis>
		<frame enabled='false'>
		</frame>
		<pointers>
                    <pointer value='$value'><!-- my count-->
                        <label enabled='true' under_pointers='true'>
                            <position placement_mode='ByPoint' x='50' y='100'/>
                            <format>{%Value}</format>
                            <background enabled='false'/>
			</label>
			<needle_pointer_style thickness='7' point_thickness='5' point_radius='3'>
                            <fill color='Rgb(230,230,230)'/>
                            <border color='Black' opacity='0.7'/>
                            <effects enabled='false'>
                            </effects>
                            <cap enabled='false'>
                            </cap>
			</needle_pointer_style>
			<animation enabled='false'/>
                    </pointer>
		</pointers>
            </circular>
	</gauge>
    </gauges>
</anychart>";
return $xml;
}


/**
 * $this->db->select("SELECT * FROM account_types order by account_type_name");
 */
}
