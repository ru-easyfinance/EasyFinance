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
        return $this->db->select("SELECT * FROM account_types order by account_type_name");
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
				return sprintf("<input type=\"text\" name=\"%s\" id=\"%s\" class=\"%s\" style=\"%s\" value=\"%s\">", 
							   $field['field_name'], $field['field_name'], $class, $style, $field['field_default_value']);
			} else {
				return sprintf("<input type=\"hidden\" name=\"%s\" id=\"%s\" class=\"%s\" style=\"%s\" value=\"%s\">", 
							   $field['field_name'], $field['field_name'], $class, $style, $field['field_default_value']);
			}
		break;
		case "text":
		case "html":
			return sprintf("<textarea name=\"%s\" id=\"%s\" class=\"%s\" style=\"%s\">%s</textarea>", 
						   $field['field_name'], $field['field_name'], $class, $style, $field['field_default_value']);
		break;
		case "enum":
			$result = sprintf("<select name=\"%s\" id=\"%s\" class=\"%s\" style=\"%s\">\n\r", $field['field_name'], $field['field_name'], $class, $style);
			foreach ($field['field_type'] as $index => $option) {
				$result .= "<option value=\"".$index."\"".($option == $field['field_default_value'] ? " selected" : "").">".$option."</option>\n\r";
			}
			$result .= "</select>\n\r";
			return $result;
		break;
		case "set":
			$result = sprintf("<select name=\"%s\" id=\"%s\" class=\"%s\" style=\"%s\" multiple>\n\r", $field['field_name'], $field['field_name'], $class, $style);
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
		$fields = $this->db->select("SELECT af.account_field_id, afd.field_name, afd.field_type FROM account_fields af 
							LEFT JOIN account_field_descriptions afd 
							    ON afd.field_description_id = af.field_descriptionsfield_description_id");
		foreach($data as $key=>$value)
		{			
			list($field_key, $field_value) = explode("=", $value);
			if ($field_key == 'type_id') $type_id = $field_value;
			if ($field_key == 'currency_id') $currency_id = $field_value;
			
			foreach($fields as $values)
			{
				if ($values['field_name'] == $field_key)
				{
					$account[$field_key]['value'] = $field_value;
					$account[$field_key]['account_field_id'] = $values['account_field_id'];
					$account[$field_key]['field_type'] = $values['field_type'];
				}
			}			
		}

		$sql = "INSERT INTO accounts (`account_id`, `account_name`, `account_type_id`, `account_description`,
									  `account_currency_id`, `user_id`)
                    VALUES (?,?,?,?,?,?);";
        if (!$this->db->query($sql, '', $account['name']['value'], $type_id, $account['description']['value'], 
                            $currency_id, $this->user_id))
		{
			return false;
		}
		
		$next_id = mysql_insert_id();

		foreach($account as $value)
		{
			switch ($value['field_type'])
			{
				case "numeric":
				    $type = "int_value";
				break;
				case "percent":
				    $type = "int_value";
				break;
				case "date":
				    $type = "date_value";
				break;
				default:
				    $type = "string_value";
				break;
			}
			$sql = "INSERT INTO account_field_values (`field_value_id`, `account_fieldsaccount_field_id`, 
													  `".$type."`, `accountsaccount_id`)
                    VALUES (?,?,?,?);";
			if (!$this->db->query($sql, '', $next_id, $value['value'],$value['account_field_id']))
			{
				return false;
			}
		}
		return true;
    }
	
	/**
     * Удаляет указанный счет
     * @param $id int Ид счета
     * @return bool
     */
    public function deleteAccount($id)
    {
        $sql = "DELETE FROM accounts WHERE `account_id` = ? and `user_id` = ?";
        if (!$this->db->query($sql, $id, $this->user_id)) {
            return false;
        }
		$sql = "DELETE FROM account_field_values WHERE `accountsaccount_id` = ?";
        if (!$this->db->query($sql, $id)) {
            return false;
        }
        return true;
    }
}