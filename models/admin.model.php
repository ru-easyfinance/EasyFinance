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
	
	/**
     * Получаем список типов счетов
     * @return array
     */
	public function getAccountsTypeList()
	{
		return $this->db->select("SELECT * FROM account_types");
	}
	
	/**
     * Получаем список полей для счетов
     * @return array
     */
	public function getAccountsFieldsList()
	{
		return $this->db->select("SELECT * FROM account_field_descriptions");
	}
	
	/**
     * Получаем список полей в счетах
     * @return array
     */
	public function getAccountsTypeFieldsList()
	{
		return $this->db->select("SELECT af.*, at.account_type_name, afd.field_visual_name FROM account_fields af
								 LEFT JOIN account_types at
								     ON at.account_type_id = af.account_typesaccount_type_id
								 LEFT JOIN account_field_descriptions afd
								     ON afd.field_description_id = af.field_descriptionsfield_description_id
								 ORDER BY at.account_type_id");
	}	
	
	/**
     * Сохраняем тип счета
     * @return void
     */
	public function saveTypeAccount($name, $id=null)
	{
		if (!empty($id))
		{
			
		}else{
			$sql = "INSERT INTO account_types (`account_type_id`, `account_type_name`) VALUES (?,?);";
			$this->db->query($sql, '', $name);
		}
	}
	
	/**
     * Сохраняем поле для счета
     * @return void
     */
	public function saveFieldAccount($fields)
	{
		if (!empty($fields['field_description_id']))
		{
			
		}else{
			$fields['field_description_id'] = "";
			$this->db->query('INSERT INTO account_field_descriptions(?#) VALUES(?a)', array_keys($fields), array_values($fields));
		}
	}
	
	/**
     * Сохраняем объединение поля с типом счета
     * @return void
     */
	public function saveTypeFieldAccount($id,$type,$field)
	{
		if (!empty($id))
		{
			
		}else{
			$sql = "INSERT INTO account_fields (`account_field_id`, `account_typesaccount_type_id`, 
												`field_descriptionsfield_description_id`) 
					VALUES (?,?,?);";
			$this->db->query($sql, '', $type, $field);
		}
	}
}