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
        return $this->db->select("SELECT * FROM account_types ORDER BY account_type_name");
    }
    /**
     * Получаем поля для счета
     * @return array
     */
    public function newEmptyBill($type)
    {

        $fields = $this->db->select("SELECT * FROM account_fields af
                                        LEFT JOIN account_field_descriptions afd
                                        ON af.field_descriptionsfield_description_id = afd.field_description_id
                                        WHERE af.account_typesaccount_type_id = ?",
                                        $type);
        if (count($fields))
        {
            foreach ($fields as $field)
            {
                $this->fields[$field["field_name"]] = $this->setAccountField($field);
            }
	}


    }

	
    /**
     * Проверяем тип поля
     * @return array
     */
    private function setAccountField($data)
    {
        if ($data['field_type'] == "enum" || $data['field_type'] == "set")
        {
            return $this->loadEnumList($data['field_regexp']);
	}
            return $data;
    }
	
    /**TODO return notvalid
     * Получаем данные для типа поля enum или set
     * @return array
     */
    private function loadEnumList($table)
    {
        $list = $this->db->select("SELECT * FROM ".$table);
        $enum = array();
        foreach ($list as $element)
        {
            $enum[($element['name']!=""? $element['name'] : $element['id'])] = $element['value'];
        }
	return $enum;
    }

    /**
     * Формируем форму
     * @return string
     */
    public function formField($field = "", $class="", $style="")
    {
        switch ($field['field_type']) {
            case "string":
            case "numeric":
                if ($field['field_permissions'] != "hidden")
                {
                    return sprintf("<input type=\"text\" name=\"%s\" id=\"%s\" class=\"%s\" style=\"%s\" value=\"%s\">",
                        $field['field_name'],
                        $field['field_name'],
                        $class,
                        $style,
                        $field['field_default_value']);
		} else {
                    return sprintf("<input type=\"text\" DISABLED name=\"%s\" id=\"%s\" class=\"%s\" style=\"%s\" value=\"%s\">",
			$field['field_name'],
                        $field['field_name'],
                        $class,
                        $style,
                        $field['field_default_value']);
		}
		break;
            case "text":
            case "html":
                return sprintf("<textarea name=\"%s\" id=\"%s\" class=\"%s\" style=\"%s\">%s</textarea>",
                    $field['field_name'],
                    $field['field_name'],
                    $class,
                    $style,
                    $field['field_default_value']);
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
                $result = sprintf("<select name=\"%s\" id=\"%s\" class=\"%s\" style=\"%s\" multiple>\n\r", 
                    $field['field_name'],
                    $field['field_name'],
                    $class,
                    $style);
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
    public function fields()
    {
        return $this->fields;
    }
	
    /**
     * Возвращаем сформированый список полей
     * @return array
     */
    public function formatFields()
    {
    	$i=0;
        $data = array();
    	foreach($this->fields as $field => $key)
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
        $id = $data['id'];
        foreach($data as $key=>$value)
        {
            if (!$data[$key])
                $data[$key]='';
        }

        

	$fields = $this->db->select("SELECT af.account_field_id, afd.field_name, afd.field_type
                                    FROM account_fields af
                                    LEFT JOIN account_field_descriptions afd
                                    ON afd.field_description_id = af.field_descriptionsfield_description_id");
	foreach($data as $key=>$value)
	{			
            $field_key = $key;
            $field_value = $value;
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
    $this->db->query($sql,
                    $id,
                    $account['name']['value'],
                    $type_id, $account['description']['value'],
                    $currency_id,
                    $this->user_id);
	
	$next_id = mysql_insert_id();
        $data['id']=$next_id;
        
        if (!intval($id))
            $this->new_operation($data);
        else
            $this->update_operation($data);
        foreach($account as $value)
	{
            $type = "string_value";
            $sql = "INSERT INTO account_field_values (`field_value_id`, `account_fieldsaccount_field_id`,
                                                        `".$type."`, `accountsaccount_id`)
                    VALUES (?,?,?,?);";
            $this->db->query($sql, '0', $next_id, $value['value'],$value['account_field_id']);
	}
        Core::getInstance()->user->initUserAccounts();
        Core::getInstance()->user->save();
	return $next_id;
    }

    function new_operation($data)
    {
        //alert( date('d.m.Y'));
       $model = new Operation_Model();
       $model->add(str_replace(' ', '', $data['starter_balance']), date('Y.m.d'), 0,0,
           'Начальный остаток', $data['id']);
    }

    function update_operation($data)
    {
        $sql = "SElECT `id` FROM operation WHERE account_id=? AND user_id=? ORDER BY `dt_create`";
        $oid = $this->db->selectCell($sql,$data['id'],$this->user_id);
        $model = new Operation_Model();
       $model->edit($oid,str_replace(' ', '', $data['starter_balance']),date('Y.m.d'),0,0,'Начальный остаток', $data['id']);
       $model->save();
    }


    /**
     * Удаляет указанный счет
     * @param $id int Ид счета
     * @return bool
     */
    public function deleteAccount($id)
    {
        

	$sql = "DELETE FROM
                    account_field_values
                WHERE
                    account_fieldsaccount_field_id = ?;";
        $this->db->query($sql, $id);

        $sql = "DELETE FROM accounts WHERE `account_id` = ? and `user_id` = ?;";
        $this->db->query($sql, $id, $this->user_id);
        Core::getInstance()->user->initUserAccounts();
        Core::getInstance()->user->save();
        return true;
    }
    /**
     * Функция которая отсылает список счетов
     */
    public function accounts_list()
    {
        $sql='SELECT
                   account_id, accounts.account_type_id, cur_name, cur_id,account_type_name
                FROM
                    accounts
                LEFT JOIN
                    currency
                ON
                    (account_currency_id=cur_id)
                LEFT JOIN
                    account_types
                ON
                   (accounts.account_type_id=account_types.account_type_id)
                WHERE
                    user_id=?';
        $ret = $this->db->select($sql,$this->user_id);

        $id = array();
        $type = array();
        $cur = array();
        $cur_id = array();
        $type_name = array();
        foreach ($ret as $key=>$val)
        {
            $id[]=$val['account_id'];
            $type[]=$val['account_type_id'];
            $cur[]=$val['cur_name'];
            $cur_id[]=$val['cur_id'];
            $type_name[]=$val['account_type_name'];
        }
        $id_str = implode(',', $id);
        $type_str = implode(',', $type);
        $sql = "SELECT
                    `int_value`,
                    `date_value`,
                    `string_value`,
                    account_fieldsaccount_field_id,
                    field_name
                FROM
                    account_field_values
                    LEFT JOIN
                        account_fields
                      ON
                        account_fields.account_field_id = account_field_values.accountsaccount_id
                        LEFT JOIN
                            account_field_descriptions
                        ON
                            account_field_descriptions.field_description_id = account_fields.field_descriptionsfield_description_id
                WHERE
                    account_fieldsaccount_field_id
                IN ($id_str)";
        $values = $this->db->select($sql);
        $mod = new Operation_Model();
        foreach ($id as $key=>$val)
        {
            $res[$val]['type']=$type[$key];
            $res[$val]['cur']=$cur[$key];
            $res[$val]['id']=$id[$key];

            $res[$val]['fields']=array();

            foreach ($values as $k=>$v)
            {
                if ($values[$k]['account_fieldsaccount_field_id'] == $val)
                {
                    $res[$val]['fields'][$values[$k]['field_name']]=$values[$k]['int_value'] .
                    $values[$k]['date_value'] .
                    $values[$k]['string_value'];
                }//value
            }
            $res[$val]['cat'] = $type_name[$key];
            $total=(float)($mod->getTotalSum($val));
            $res[$val]['fields']['total_balance'] = $total;
            $res[$val]['def_cur'] =round(
                $res[$val]['fields']['total_balance']* Core::getInstance()->currency[$cur_id[$key]]['value'],
                2
                );



            $res[$val]['special'] = array(0,0,0);//todo tz

        }
        return $res;
    }

    public function get_fields($type,$id)
    {

        $this->newEmptyBill($type);
        $sql = "SELECT `int_value`, `date_value`, `string_value` FROM account_field_values WHERE account_fieldsaccount_field_id = ?";
        $res = $this->db->select($sql,$id);
        $cnt = count($this->fields());
        $field=$this->fields();
        $ret = array();
        $i=0;

        foreach($field as $key)
        {

           $f_name = $key['field_name'];
           if ($res[$i]['int_value'])
                $ret[$f_name] = $res[strval($i)]['int_value'];
           else
           {
               if ($res[$i]['string_value'])
                    $ret[$f_name] = $res[strval($i)]['string_value'];
               else
                    $ret[$f_name] = $res[strval($i)]['data_value'];
           }
           $i++;  
        }
        return $ret;
    }
     /**
     * Редактирует счёт
     * @return bool
     */
    function correct($data,$aid,$tid) {
	$fields = $this->db->select("SELECT af.account_field_id, afd.field_name, afd.field_type
                                    FROM account_fields af
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

	$sql = "UPDATE accounts SET `account_name`=?, `account_type_id`=?, `account_description`=?,
                                        `account_currency_id`=?, `user_id`=? WHERE `account_id`=?;";
        $this->db->query($sql,
                    $account['name']['value'],
                    $type_id,
                    $account['description']['value'],
                    $currency_id,
                    $this->user_id,
                    $aid);
        $sql = "SELECT `field_value_id` FROM account_field_values WHERE account_fieldsaccount_field_id = ?";
        $arr = $this->db->selectCol($sql,$aid);
        $i=0;
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
            $sql = "UPDATE account_field_values SET  
                                                        `$type`=?, `accountsaccount_id`=? WHERE `field_value_id`=?;";
            $this->db->query($sql , $value['value'],$value['account_field_id'], $arr[$i]);

            $i++;
	}
                Core::getInstance()->user->initUserAccounts();
        Core::getInstance()->user->save();
	return true;
    }
}