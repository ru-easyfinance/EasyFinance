<?
/** Класс дерева категорий */
class hmCategoryTree {
	
	/** Сначала статичные свойства и методы */
	
	const AlreadyExists = -1;
	const NameError = -2;
	const NoSuchCategory = -3;
	const DatabaseError = -4;
	const ReadOnly = -5;
	
	/** Создание новой категории. 
		Нужен чтобы можно было создавать категории без генерации дерева
		категорий.
	*/
	static function CreateNewCategory($name, $user_id, $parent_id = 0, $db = null) {

		if (!$db) {
			$db = DbSimple_Generic::connect("mysql://".SYS_DB_USER.":".SYS_DB_PASS."@".SYS_DB_HOST."/".SYS_DB_BASE);
			$db->query("SET character_set_client = 'utf8', character_set_connection = 'utf8', character_set_results = 'utf8'");
		}
		if (!hmCategoryTree::ValidName($name)) {
			return hmCategoryTree::NameError;
		}
		if (hmCategoryTree::CheckExistsByName($name, $user_id, $parent_id, &$db)) {
			return hmCategoryTree::AlreadyExists;
		}
		if (!hmCategoryTree::CheckParentById($parent_id, &$db)) {
			return hmCategoryTree::NoSuchCategory;
		}
		$row = Array(
				"user_id" => $user_id,
				"parent_id" => $parent_id,
				"category_name" => $name
				);
		if ($cid = $db->query("INSERT INTO user_categories (?#) VALUES (?a)", array_keys($row), array_values($row))) {
			return $cid;
		} else {
			return hmCategoryTree::DatabaseError;
		}

	}
	/** Удаляет категорию  */
	
	static function DeleteCategory($id, $db = null) {

		if (!$db) {
			$db = DbSimple_Generic::connect("mysql://".SYS_DB_USER.":".SYS_DB_PASS."@".SYS_DB_HOST."/".SYS_DB_BASE);
			$db->query("SET character_set_client = 'utf8', character_set_connection = 'utf8', character_set_results = 'utf8'");
		}
		if ($cid = $db->query("DELETE FROM user_categories WHERE category_id = ?d OR parent_id = ?d AND system_category_id != 0", $id, $id)) {
			return $cid;
		} else {
			return hmCategoryTree::DatabaseError;
		}

	}	
	/** Проверяет, нет ли точно такой же категории. */
	static function CheckExistsByName($name, $user_id, $parent_id, &$db) {
		
		if (!$db) {
			$db = DbSimple_Generic::connect("mysql://".SYS_DB_USER.":".SYS_DB_PASS."@".SYS_DB_HOST."/".SYS_DB_BASE);
			$db->query("SET character_set_client = 'utf8', character_set_connection = 'utf8', character_set_results = 'utf8'");
		}
		$rows = $db->select("SELECT category_id FROM user_categories WHERE category_name = ? AND user_id = ? AND parent_id = ?d", $name, $user_id, $parent_id);
		if (count($rows)) {
			return true;
		} else {
			return false;
		}
		
	}
	
	/** Проверяет, существует ли категория. */
	static function CheckExistsById($id, &$db) {
		if (!$db) {
			$db = DbSimple_Generic::connect("mysql://".SYS_DB_USER.":".SYS_DB_PASS."@".SYS_DB_HOST."/".SYS_DB_BASE);
			$db->query("SET character_set_client = 'utf8', character_set_connection = 'utf8', character_set_results = 'utf8'");
		}
		$rows = $db->select("SELECT category_id FROM user_categories WHERE category_id = ?", $id);
		
		if (count($rows)) {
			return true;
		} else {
			return false;
		}
	}
	
	/** Проверяет, может ли категория быть родителем. */
	static function CheckParentById($id, &$db) {
		if ($id == 0) {
			return true;
		}
		if (!$db) {
			$db = DbSimple_Generic::connect("mysql://".SYS_DB_USER.":".SYS_DB_PASS."@".SYS_DB_HOST."/".SYS_DB_BASE);
			$db->query("SET character_set_client = 'utf8', character_set_connection = 'utf8', character_set_results = 'utf8'");
		}
		$rows = $db->select("SELECT category_id FROM user_categories WHERE category_id = ? AND parent_id = 0", $id);
		
		if (count($rows)) {
			return true;
		} else {
			return false;
		}
	}
	
	/** Проверяет валидность имени. */
	static function ValidName($name) {
		return true;
	}
	
	/** Создает для пользователя системные категории.	*/
	static function System2User($user_id, $sub, &$db = null) {
		if (!$db) {
			$db = DbSimple_Generic::connect("mysql://".SYS_DB_USER.":".SYS_DB_PASS."@".SYS_DB_HOST."/".SYS_DB_BASE);
			$db->query("SET character_set_client = 'utf8', character_set_connection = 'utf8', character_set_results = 'utf8'");
		}
		$rows = $db->select("SELECT * FROM system_categories WHERE parent_id = 0 AND system_category_id NOT IN (SELECT system_category_id FROM user_categories WHERE system_category_id != 0 AND user_id = ?)", $user_id);
		$sys2us = Array();
		foreach ($rows as $row) {
			$data = Array(
				"user_id" => $user_id,
				"system_category_id" => $row['system_category_id'],
				"parent_id" => 0,
				"category_name" => $row['system_category_name'],
				"visible" => 1
			);
			if ($db->selectRow("SELECT category_id FROM user_categories WHERE category_name = ? AND parent_id = 0 AND user_id = ?", $data['category_name'], $user_id)) {
				$db->query("UPDATE user_categories SET system_category_id = ?d WHERE category_name = ? AND parent_id = 0 AND user_id = ?", $data['system_category_id'], $data['category_name'], $user_id);
			} else {
				$sys2us[$row['system_category_id']] = $db->query("INSERT INTO user_categories (?#) VALUES (?a)", array_keys($data), array_values($data));
			
			}
		}
		if ($sub) {
			$rows = $db->select("SELECT * FROM system_categories WHERE parent_id != 0 AND system_category_id NOT IN (SELECT system_category_id FROM user_categories WHERE system_category_id != 0 AND user_id = ?)", $user_id);
			foreach ($rows as $row) {
				if (in_array($row['parent_id'], $sys2us)) {
					$data = Array(
						"user_id" => $user_id,
						"system_category_id" => 0,
						"parent_id" => $sys2us[$row['parent_id']],
						"category_name" => $row['system_category_name'],
						"visible" => 1
					);
					$db->query("INSERT INTO user_categories (?#) VALUES (?a)", array_keys($data), array_values($data));		
				}
			}
		}
	}
	
	/** Закончили статичные свойства и методы, переходим к основным. */
	
	private $tree = Array();
	private $db = NULL;
	
	function __construct($database_descriptor = null) {

		if ($database_descriptor) {
			$this->db = $database_descriptor;
		} else {
			$this->db = DbSimple_Generic::connect("mysql://".SYS_DB_USER.":".SYS_DB_PASS."@".SYS_DB_HOST."/".SYS_DB_BASE);
			$this->db->query("SET character_set_client = 'utf8', character_set_connection = 'utf8', character_set_results = 'utf8'");
		}
	}
	
	/** Возвращает все дерево категорий. */
	function Tree() {
		return $this->tree;
	} 
	
	/** Получает полное дерево категорий, упорядоченное по пользователям. */
	function loadFullTree() {
	
		$forest = $this->db->select("SELECT *, category_id AS ARRAY_KEY, parent_id AS PARENT_KEY FROM user_categories ORDER BY user_id");
		$this->loadTree($forest);
	
	}
	
	/** Получает все дерево категорий определнного пользователя. */
	function loadUserTree($user_id) {
	
		$forest = $this->db->select("SELECT *, category_id AS ARRAY_KEY, parent_id AS PARENT_KEY FROM user_categories WHERE user_id = ?", $user_id);
		$this->loadTree($forest);
	
	}
	/** Гененрирует дерево. */
	function loadTree($forest) {
		if (!is_array($forest)) return;
		foreach ($forest as $tree) {
			$category = new hmCategory($this->db);
			$category->Set($tree['category_id'], $tree['category_name'], $tree['user_id'], $tree['visible'],$tree['system_category_id'], $tree['parent_id']);
			if (is_array($tree['childNodes'])) {
				foreach ($tree['childNodes'] as $child) {
					$category->AppendChild($child);
				}
			}
			$this->tree[$tree['category_id']] = $category;
		}
	}
	
	
}

/** Класс категории */
class hmCategory {
	
	private $id; // Идентификатор Категории
	private $name; // Иия категории
	private $user; // Владелец категории
	private $visible; // Видимость категории
	private $parent; // Объект Родительской категории
	private $parent_id; // Идентификатор Родительской категории
	private $system_id; // Идентификатор Системной Категории
	public $children; // Массив дочерних категорий
	private $db = NULL;
	
	function __construct($database_descriptor = null) {
		$this->user = null;
		$this->id = 0;
		$this->name = "";
		$this->children = Array();
		if ($database_descriptor) {
			$this->db = $database_descriptor;
		} else {
			$this->db = DbSimple_Generic::connect("mysql://".SYS_DB_USER.":".SYS_DB_PASS."@".SYS_DB_HOST."/".SYS_DB_BASE);
			$this->db->query("SET character_set_client = 'utf8', character_set_connection = 'utf8', character_set_results = 'utf8'");
		}
	}
	
	/** Возвращает идентификатор категории. */
	function Id() {
		return $this->id;
	}
	
	/** Возвращает имя категории. */
	function Name() {
		return $this->name;
	}
	
	/** Возвращает владельца категории. */
	function User() {
		return $this->user;
	}
	
	/** Возвращает Видима ли категории. */
	function Visible() {
		return $this->visible;
	}
	
	/** Возвращает Системная ли категория. */
	function isSystem() {
		if ($this->system_id > 0) {
			return true;	
		}
		return false;
	}
	
	/** Возвращает либо количество дочерних категорий, 
		либо проверяет, есть ли в дереве дочерних категорий
		конкретная категория. */
	function hasChild($id = null) {
		if (!$id) {
			return (count($this->children));
		} else {
			return array_key_exists($id, $this->children);
		}
	}
	
	/** Возвращает дочернюю категорию. */
	function Child($id) {
		if ($this->hasChild($id)) {
			return $this->children[$id];
		} else {
			return null;
		}
	}
		
	/** Возвращает массив дочерних категорий. */
	function Children() {
		return $this->children;
	}
	
	/** Загружает данные из БД */
	function Load($id) {
		$row = $this->db->selectRow("SELECT * FROM user_categories WHERE category_id = ?", $id);
		if (count($row)) {
			$this->Set($row['category_id'], $row['category_name'], $row['user_id'], $row['visible'], $row['system_category_id'], $row['parent_id']);
			return $this->id;
		} else {
			return hmCategoryTree::NoSuchCategory;
		}
	}
	
	/** Устанавливает данные текущей категории. */
	function Set($id, $name, $user_id, $visible, $system_id, $parent) {

		$this->id = $id;
		$this->name = $name;
		$this->user = $user_id;
		$this->visible = $visible;
		$this->system_id = $system_id;
		if (is_numeric($parent)) {
			$this->parent = NULL;
			$this->parent_id = $parent;
		} else if ($parent instanceof hmCategory) {
			$this->parent = $parent;
			$this->parent_id = $parent->Id();			
		}
				
	}
	
	/** Добавляет дочернюю категорию. */
	function AppendChild($array) {
		
		$child = new hmCategory($this->db);
		$child->Set($array['category_id'], 
					$array['category_name'], 
					$array['user_id'], 
					$array['visible'], 
					$array['system_category_id'], 
					$this); 
		$this->children[$array['category_id']] = $child; 
	
	}
	
	/** Переключает видимость категории. */
	function ToggleVisible($instantsave = true) {
		$this->visible = ($this->visible)? 0 : 1;
		if ($instantsave) {
			$this->SaveChanges();
		}
	}
	
	/** Переименовывает категорию. */
	function Rename($name, $instantsave = true) {
		if ($this->isSystem()) {
			return hmCategoryTree::ReadOnly;
		}
		if (hmCategoryTree::ValidName($name)) {
			$this->name = $name;
			if ($instantsave) {
				$this->SaveChanges();
			}
		} else {
			return hmCategoryTree::NameError;
		}
	}
	
	/** Создает дочернюю категорию. */
	function CreateChild($name, $parent_id = 0) {

		$id = hmCategoryTree::CreateNewCategory($name, $this->user, $this->id, $this->db);
		if ($id > 0) {
			$this->AppendChild(array($id, $name, $this->user, 0, $this));
		}
		return $id;	
	}
	
	/** Сохраняет категорию в базу данных. */
	function SaveChanges() {
		$row = Array(
			"user_id" => $this->user,
			"system_category_id" => $this->system_id,
			"parent_id" => $this->parent_id,
			"category_name" => $this->name,
			"visible" => $this->visible
		);
		if ($this->id) {
			return $this->db->query("UPDATE user_categories SET ?a WHERE category_id = ?d", $row, $this->id);
		} else {
			return hmCategoryTree::CreateNewCategory($this->name, $this->user, $this->parent_id, $this->db);
		}
	}
}
?>