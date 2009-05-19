<?
class hmBills {

    const AlreadyExists = -1;
    const NameError = -2;
    const NoSuchCategory = -3;
    const DatabaseError = -4;
    const ReadOnly = -5;

    private $user_id;
    private $bills = Array();

    static function CreateNewBill($user_id, $type, $data, $db = null) {

        if (!$db) {
            $db = DbSimple_Generic::connect("mysql://" . SYS_DB_USER . ":" . SYS_DB_PASS .
                "@" . SYS_DB_HOST . "/" . SYS_DB_BASE);
            $db->query("SET character_set_client = 'utf8', character_set_connection = 'utf8', character_set_results = 'utf8'");
        }
        $table = $db->selectCell("SELECT btype_table FROM hm_bills_types WHERE btype_id = ?d", $type);
		if ($table) {
            $row = Array(
				"user_id" => $user_id,
				"btype_id" => $type,
				);
			$lid = $db->query("INSERT INTO hm_bills (?#) VALUES (?a)", array_keys($row), array_values($row));
			$bill = new hmBill;
 			$bill->newEmptyBill($type);
 			return $bill->createNew($lid, $table, $_GET);
        }
    }

	static function getBillTypes($db = null) {
		if (!$db) {
            $db = DbSimple_Generic::connect("mysql://" . SYS_DB_USER . ":" . SYS_DB_PASS .
                "@" . SYS_DB_HOST . "/" . SYS_DB_BASE);
            $db->query("SET character_set_client = 'utf8', character_set_connection = 'utf8', character_set_results = 'utf8'");
        }
        return $db->select("SELECT * FROM hm_bills_types");
        
	}

    function __construct($user_id, &$db) {

        if (!$db) {
            $this->db = DbSimple_Generic::connect("mysql://" . SYS_DB_USER . ":" .
                SYS_DB_PASS . "@" . SYS_DB_HOST . "/" . SYS_DB_BASE);
            $this->db->query("SET character_set_client = 'utf8', character_set_connection = 'utf8', character_set_results = 'utf8'");
        } else {
            $this->db = $db;
        }

		$this->user_id = $user_id;
    }
    
    function loadBills() {

		$bills = $this->db->select("SELECT bl.*, bt.* FROM hm_bills AS bl INNER JOIN hm_bills_types AS bt ON bl.btype_id = bt.btype_id WHERE bl.user_id = ?", $this->user_id); 
		if (count($bills)) {
			foreach ($bills as $bill) {
				$this->bills[$bill["bill_id"]] = new hmBill($this->db);
				$this->bills[$bill["bill_id"]]->newEmptyBill($bill['btype_id']);
				$this->bills[$bill["bill_id"]]->loadData($bill['bill_id'], $bill['btype_table']);
				$this->bills[$bill["bill_id"]]->user_id = $bill['user_id'];
				$this->bills[$bill["bill_id"]]->table = $bill['btype_table'];
				$this->bills[$bill["bill_id"]]->type_name = $bill['btype_name'];
			}
		}
		return count($bills);
	}

	function bills() {
		return $this->bills;
	}

}

class hmBill {

	public $id;
	public $user_id;
	public $created;
	public $table;
	public $type_name;
    private $fields = Array();
	private $properties = Array();

	function __construct(&$db=null) {
		if (!$db) {
            $this->db = DbSimple_Generic::connect("mysql://" . SYS_DB_USER . ":" .
                SYS_DB_PASS . "@" . SYS_DB_HOST . "/" . SYS_DB_BASE);
            $this->db->query("SET character_set_client = 'utf8', character_set_connection = 'utf8', character_set_results = 'utf8'");
        } else {
            $this->db = $db;
        }
	}

    function newEmptyBill($type) {
        $fields = $this->db->select("SELECT * FROM hm_billsFields WHERE btype_id = ?d", $type);
        if (count($fields)) {
			foreach ($fields as $field) {
				$this->fields[$field["fieldName"]] = new hmBillField($field, $this->db);
			}
		}
    }
    
    function fillEmptyBill($user_id, $data) {
		$this->user_id = $user_id;
		$this->table = $data['btype_table'];
		$this->type_name = $data['btype_name'];
		if (count($this->fields)) {
			foreach ($this->fields as $name => $field) {
				if (array_key_exists($name, $data)) {
					if ($field->validate($data[$name])) {
						$this->properties[$name] = $data[$name];
					}
				}
			}
		}
	}
	
	function loadData($id, $table) {
		$row = $this->db->selectRow("SELECT * FROM ".$table." WHERE bill_id = ?d", $id);
		if (count($row)) {
			$this->id = $id;
			foreach ($this->fields as $name => $field) {
				if (array_key_exists($name, $row)) {
					if ($field->type == "enum" || $field->type == "set") {
						$this->properties[$name] = $field->enum[$row[$name]];
					} else {
						$this->properties[$name] = $row[$name];
					}
				}
			}
		}
	}
	
	function __get($name) {
		if (isset($this->properties[$name])) {
			return $this->properties[$name];
		} else {
			return Null;
		}
	}
	
	function properties() {
		return $this->properties;
	}
	function fields() {
		return $this->fields;
	}
	
	function createNew($id, $table, $data) {
		$row = Array();
		$row["bill_id"] = $id;
		if (count($this->fields)) {
			foreach ($this->fields as $name => $field) {
				if (array_key_exists($name, $data)) {
					if ($field->validate($data[$name])) {
						$row[$name] = $data[$name];
					} else {
						$row[$name] = $field->defaultValue;
					}
				} else {
					$row[$name] = $field->defaultValue;
				}
			}
			
			$this->db->query("INSERT INTO `".$table."` (?#) VALUES (?a)", array_keys($row), array_values($row));
			return 1;
		}
	}
	
}

class hmBillField {

    public $name;
    public $display;
    public $description;
    public $type;
    public $enum = Array();
    public $defaultValue;
    public $RegExp;
    public $permissions;
    public $priority;
    public $value;
	
	function __construct($data, &$db) {
		
		
        if (!$db) {
            $this->db = DbSimple_Generic::connect("mysql://" . SYS_DB_USER . ":" .
                SYS_DB_PASS . "@" . SYS_DB_HOST . "/" . SYS_DB_BASE);
            $this->db->query("SET character_set_client = 'utf8', character_set_connection = 'utf8', character_set_results = 'utf8'");
        } else {
            $this->db = $db;
        }
		$this->name = $data['fieldName'];
		$this->display = $data['visualName'];
		$this->description = $data['description'];
		$this->type = $data['Type'];
		$this->defaultValue = $data['DefaultValue'];
		$this->RegExp = $data['RegExp'];
		$this->permission = $data['Permissions'];
		$this->priority = $data['Priority'];
		$this->value = $data['DefaultValue'];
		
		if ($this->type == "enum" || $this->type == "set") {
		
			$this->loadEnumList($this->RegExp);
			
		}
	}
	function formField($value = "", $id="", $class="", $style="") {
		switch ($this->type) {
		case "string":
		case "numeric":
			if ($this->permission != "hidden") {
				return sprintf("<input type=\"text\" name=\"%s\" id=\"%s\" class=\"%s\" style=\"%s\" value=\"%s\">", $this->name, $id, $class, $style, $value);
			} else {
				return sprintf("<input type=\"hidden\" name=\"%s\" id=\"%s\" class=\"%s\" style=\"%s\" value=\"%s\">", $this->name, $id, $class, $style, $value);
			}
		break;
		case "text":
		case "html":
			return sprintf("<textarea name=\"%s\" id=\"%s\" class=\"%s\" style=\"%s\">%s</textarea>", $this->name, $id, $class, $style, $value);
		break;
		case "enum":
			$result = sprintf("<select name=\"%s\" id=\"%s\" class=\"%s\" style=\"%s\">\n\r", $this->name, $id, $class, $style);
			foreach ($this->enum as $index => $option) {
				$result .= "<option value=\"".$index."\"".($option == $value? " selected" : "").">".$option."</option>\n\r";
			}
			$result .= "</select>\n\r";
			return $result;
		break;
		case "set":
			$result = sprintf("<select name=\"%s\" id=\"%s\" class=\"%s\" style=\"%s\" multiple>\n\r", $this->name, $id, $class, $style);
			foreach ($this->enum as $index => $option) {
				$result .= "<option value=\"".$index."\"".($option == $value? " selected" : "").">".$option."</option>\n\r";
			}
			$result .= "</select>\n\r";
			return $result;
		break;
		}
	}
	
	function loadEnumList($table) {
		
		//if ($this->db->select("SELECT 1 IF EXISTS Classificator_".$table)) {
			$list = $this->db->select("SELECT * FROM Classificator_".$table);
			foreach ($list as $element) {
				$this->enum[($element['name']!=""? $element['name'] : $element['id'])] = $element['value'];
			}
		//}
		
	}
	
	function validate($value) {
		if (!$this->validType($value)) {
				return false;
		}
		if (!$this->validRegExp($value)) {
			return false;
		}
		return true;
	}
	
	function validType($value) {
		switch ($this->type) {
		case "numeric":
			if (!is_numeric($value)) {
				return false;
			}
		break;
		case "string":
		case "text":
		case "html":
		case "enum":
			if (is_array($value) || is_object($value) || is_resource($value)) {
				return false;
			}
		break;
		case "set":
			if (!is_array($value)) {
				return false;
			}
		break;
		}
		return true;
	}
	
	function validRegExp($value) {
		if (empty($this->RegExp)) {
			return true;
		}
		if ($this->type == "set" || $this->type == "enum") {
			if (array_key_exists($value, $this->enum)) {
				return true;
			}
			if ($this->permission == "add") {
				$this->addValueToClassificator($value);
				return true;
			}
			return false;
		}
		if (preg_match("(".$this->RegExp.")", $value)) {
			return true;
		}
		return false;
	}
	
	function addValueToClassificator($value, $name = "") {
		
		$row = Array(
				"id" => "",
				"name" => "",
				"value" => $value,
				);
				
		$lid = $this->db->query("INSERT INTO Classificator_".$table." (?#) VALUES (?a)", array_keys($row), array_values($row));
	}
}
?>