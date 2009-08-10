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
        
        switch ($i)
        {
            case 1:
                $this->xml();
                break;
            case 2:
                die(json_encode('server talk...'));
                $targets = new Targets_Model();
                die(json_encode($targets->getLastList(0, $type)));
                break;
            case 3:
                die(json_encode(array(rand(-10,10),rand(-10,10))));
                $sql = "SELECT value FROM info_user WHERE uid=? AND type=? AND date=?;";
                $row = $this->db->selectRow($sql,$this->user_id, $type, $date);
                $value = $row['value'];//day
                $sql = "SELECT name, end FROM info_panels WHERE type=?;";//end == year
                $row = $this->db->selectRow($sql, $type);
                $name = $row['name'];
                $end = $row['end'];
                die($value+':'+$end+':'+$name);
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
        die("server $date $type");

	$sql = "SELECT value FROM info_user WHERE uid=? AND type=? AND date=?;";
        $row = $this->db->selectRow($sql,$this->user_id, $type, $date);
        $value = $row['value'];
        $sql = "SELECT desc FROM info_panels WHERE type=? AND (start<?) AND (end>?);";
        $row =$this->db->selectRow($sql, $type, $value, $value);
        $desc = $row['desc'];
        die($desc);
    }
    
    public function xml($type, $date)
    {
        //$sql = "SELECT value FROM info_user WHERE uid=? AND type=? AND date=?;";
        //$row = $this->db->selectRow($sql,$this->$user_id, $type, $date);
        $value = rand(0,100);//$row['value'];
        //$sql = "SELECT name, start, end FROM info_panels WHERE type=?;";
        //$row = $this->db->selectRow($sql, $type);
        $arr=array('Фин состояние','Деньги','Бюджет','Расходы','Кредиты');
        $name = $arr[$type]; //$row['name'];
	$start = 0; //$row['start'] ;
	$end = 100;//$row['end'];
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
                            <position placement_mode='ByPoint' x='50' y='60'/>
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
