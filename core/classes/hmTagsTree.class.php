<?
/** Класс дерева категорий */
class hmTagsTree {
	
	/** Сначала статичные свойства и методы */
	
	const AlreadyExists = -1;
	const NameError = -2;
	const NoSuchTag = -3;
	const DatabaseError = -4;
	const ReadOnly = -5;
	
	/** Создание новой категории. 
		Нужен чтобы можно было создавать категории без генерации дерева
		категорий.
	*/
	static function CreateNewTag($name, $user_id, $db = null) {

		if (!$db) {
			$db = DbSimple_Generic::connect("mysql://".SYS_DB_USER.":".SYS_DB_PASS."@".SYS_DB_HOST."/".SYS_DB_BASE);
			$db->query("SET character_set_client = 'utf8', character_set_connection = 'utf8', character_set_results = 'utf8'");
		}
		if (!hmTagsTree::ValidName($name)) {
			return hmTagsTree::NameError;
		}
		if (hmTagsTree::CheckExistsByName($name, $user_id, &$db)) {
			return hmTagsTree::AlreadyExists;
		}
		$row = Array(
				"user_id" => $user_id,
				"tag_name" => $name
				);
		if ($cid = $db->query("INSERT INTO user_tags (?#) VALUES (?a)", array_keys($row), array_values($row))) {
			return $cid;
		} else {
			return hmTagsTree::DatabaseError;
		}

	}
	/** Удаляет категорию  */
	
	static function DeleteTag($id, $db = null) {

		if (!$db) {
			$db = DbSimple_Generic::connect("mysql://".SYS_DB_USER.":".SYS_DB_PASS."@".SYS_DB_HOST."/".SYS_DB_BASE);
			$db->query("SET character_set_client = 'utf8', character_set_connection = 'utf8', character_set_results = 'utf8'");
		}
		if ($cid = $db->query("DELETE FROM user_tags WHERE tag_id = ?d", $id)) {
			return $cid;
		} else {
			return hmTagsTree::DatabaseError;
		}

	}	
	/** Проверяет, нет ли точно такой же категории. */
	static function CheckExistsByName($name, $user_id, &$db) {
		
		if (!$db) {
			$db = DbSimple_Generic::connect("mysql://".SYS_DB_USER.":".SYS_DB_PASS."@".SYS_DB_HOST."/".SYS_DB_BASE);
			$db->query("SET character_set_client = 'utf8', character_set_connection = 'utf8', character_set_results = 'utf8'");
		}
		$rows = $db->select("SELECT tag_id FROM user_tags WHERE tag_name = ? AND user_id = ?", $name, $user_id);
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
		$rows = $db->select("SELECT tag_id FROM user_tags WHERE tag_id = ?", $id);
		
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
	
		$forest = $this->db->select("SELECT * FROM user_tags");
		$this->loadTree($forest);
	
	}
	
	/** Получает все дерево категорий определнного пользователя. */
	function loadUserTree($user_id) {
	
		$forest = $this->db->select("SELECT * FROM user_tags WHERE user_id = ?", $user_id);
		$this->loadTree($forest);
	
	}
	/** Гененрирует дерево. */
	function loadTree($forest) {
		if (!is_array($forest)) return;
		foreach ($forest as $tree) {
			$tag = new hmTag($this->db);
			$tag->Set($tree['tag_id'], $tree['tag_name'], $tree['user_id']);
			$this->tree[$tree['tag_id']] = $tag;
		}
	}
	
	
}

/** Класс категории */
class hmTag {
	
	private $id; // Идентификатор Категории
	private $name; // Иия категории
	private $user; // Владелец категории
	private $db = NULL;
	
	function __construct($database_descriptor = null) {
		$this->user = null;
		$this->id = 0;
		$this->name = "";
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

	/** Загружает данные из БД */
	function Load($id) {
		$row = $this->db->selectRow("SELECT * FROM user_tags WHERE tag_id = ?", $id);
		if (count($row)) {
			$this->Set($row['tag_id'], $row['tag_name'], $row['user_id']);
			return $this->id;
		} else {
			return hmTagsTree::NoSuchTag;
		}
	}
	
	/** Устанавливает данные текущей категории. */
	function Set($id, $name, $user_id) {

		$this->id = $id;
		$this->name = $name;
		$this->user = $user_id;
				
	}
	
	/** Переименовывает категорию. */
	function Rename($name, $instantsave = true) {
		if (hmTagsTree::ValidName($name)) {
			$this->name = $name;
			if ($instantsave) {
				$this->SaveChanges();
			}
		} else {
			return hmTagsTree::NameError;
		}
	}

	/** Сохраняет категорию в базу данных. */
	function SaveChanges() {
		$row = Array(
			"user_id" => $this->user,
			"tag_name" => $this->name
		);
		if ($this->id) {
			return $this->db->query("UPDATE user_tags SET ?a WHERE tag_id = ?d", $row, $this->id);
		} else {
			return hmTagsTree::CreateNewTag($this->name, $this->user, $this->db);
		}
	}
}
?>