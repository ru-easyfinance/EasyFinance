<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс модели для управления счетами пользователя
 * @copyright http://home-money.ru/
 * SVN $Id$
 */
class Accounts_Model
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
     * Список полей для счета
     * @var array
     */
	private $fields = Array();

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
     * Получаем список типов счетов
     * @return array
     */
    public function getTypeAccounts() {
        return $this->db->select("SELECT * FROM account_types");
    }
	
	/**
     * Получаем поля для счета
     * @return array
     */
	public function newEmptyBill($type) {
	    $fields = $this->db->select("SELECT * FROM account_fields af 
				      LEFT JOIN account_field_descriptions afd 
					      ON af.field_descriptionsfield_description_id = afd.field_description_id 
				      WHERE af.account_typesaccount_type_id = ?d", $type);
        if (count($fields)) {
			foreach ($fields as $field) {
				$this->fields[$field["field_name"]] = $this->setAccountField($field);
			}
		}	
	}
	
	/**
     * Проверяем тип поля
     * @return array
     */
	private function setAccountField($data) {
		if ($data['field_type'] == "enum" || $data['field_type'] == "set") {		
			return $this->loadEnumList($data['field_regexp']);			
		}
		return $data;
	}
	
	/**
     * Получаем данные для типа поля enum или set
     * @return array
     */
	private function loadEnumList($table) {
		$list = $this->db->select("SELECT * FROM ".$table);
		foreach ($list as $element) {
			$enum[($element['name']!=""? $element['name'] : $element['id'])] = $element['value'];
		}
		return $enum;
	}
	
	/**
     * Формируем форму
     * @return string
     */
	public function formField($field = "", $class="", $style="") {
		switch ($field['field_type']) {
		case "string":
		case "numeric":
			if ($field['field_permissions'] != "hidden") {
				return sprintf("<input type=\"text\" name=\"%s\" id=\"%s\" class=\"%s\" style=\"%s\" value=\"%s\">", $field['field_name'], $field['field_id'], $class, $style, $field['field_default_value']);
			} else {
				return sprintf("<input type=\"hidden\" name=\"%s\" id=\"%s\" class=\"%s\" style=\"%s\" value=\"%s\">", $field['field_name'], $field['field_id'], $class, $style, $field['field_default_value']);
			}
		break;
		case "text":
		case "html":
			return sprintf("<textarea name=\"%s\" id=\"%s\" class=\"%s\" style=\"%s\">%s</textarea>", $field['field_name'], $field['field_id'], $class, $style, $field['field_default_value']);
		break;
		case "enum":
			$result = sprintf("<select name=\"%s\" id=\"%s\" class=\"%s\" style=\"%s\">\n\r", $field['field_name'], $field['field_id'], $class, $style);
			foreach ($field['field_type'] as $index => $option) {
				$result .= "<option value=\"".$index."\"".($option == $field['field_default_value'] ? " selected" : "").">".$option."</option>\n\r";
			}
			$result .= "</select>\n\r";
			return $result;
		break;
		case "set":
			$result = sprintf("<select name=\"%s\" id=\"%s\" class=\"%s\" style=\"%s\" multiple>\n\r", $field['field_name'], $field['field_id'], $class, $style);
			foreach ($field['field_type'] as $index => $option) {
				$result .= "<option value=\"".$index."\"".($option == $field['field_default_value'] ? " selected" : "").">".$option."</option>\n\r";
			}
			$result .= "</select>\n\r";
			return $result;
		break;
		}
	}

	/**
     * Возвращаем сформированый список полей
     * @return array
     */
	public function fields() {
		return $this->fields;
	}
	
	/**
     * Возвращаем сформированый список полей
     * @return array
     */
	public function formatFields() {
		$i=0;
		foreach($this->fields as $field=>$key)
		{
			$data[$i]['display'] = $key['field_visual_name'];
			$data[$i]['value'] = $this->formField($key, "", "");
			$i++;
		}
		return $data;
	}

    /**
     * Добавляет новый счёт
     * @return bool
     */
    function add($data) {
		$fields = $this->db->select("SELECT af.account_field_id, afd.field_name FROM account_fields af 
							LEFT JOIN account_field_descriptions afd 
							    ON afd.field_description_id = af.field_descriptionsfield_description_id");

		foreach($data as $key=>$value)
		{			
			list($field_key, $field_value) = explode("=", $value);
			if ($field_key == 'type_id') $type_id = $field_value;
			foreach($fields as $values)
			{
				if ($values['field_name'] == $field_key)
				{
					$sql[$field_key]['value'] = $field_value;
					$sql[$field_key]['account_field_id'] = $values['account_field_id'];
				}
			}			
		}
		
		/*$sql = "INSERT INTO accounts (user_id, money, `date`, cat_id, bill_id, drain, comment)
                    VALUES (?,?,?,?,?,?,?);";
                $this->db->query($sql, $this->user->getId(), $val['money'], $val['date'],
                    $val['cat_id'],$val['bill_id'], $val['drain']);
		pre($sql);*/
    }
}