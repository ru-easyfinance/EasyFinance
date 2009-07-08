<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс модели для управления административной частью
 * @copyright http://home-money.ru/
 * SVN $Id: accounts.model.php 83 2009-07-07 14:33:54Z korogen $
 */
class Admin_Model
{
	/**
     * Ссылка на экземпляр DBSimple
     * @var DbSimple_Mysql
     */
    private $db = NULL;
	
	/**
     * Конструктор
     * @return void
     */
    public function __construct()
    {
        $this->db = Core::getInstance()->db;
    }
	
	public function getTypeList()
	{
		return $this->db->select("SELECT * FROM account_types");
	}
	
	public function saveTypeAccount($name, $id=null)
	{
		if (!empty($id))
		{
			
		}else{
			$sql = "INSERT INTO account_types (`account_type_id`, `account_type_name`) VALUES (?,?);";
			if (!$this->db->query($sql, '', $name))
			{
				return false;
			}
		}
	}
}