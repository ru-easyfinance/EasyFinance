<?php
// подключаем все необходимые библиотеки
require_once (SYS_DIR_LIBS . "external/DBSimple/Mysql.php");
require_once (SYS_DIR_LIBS . "categories.class.php");

if (empty($_SESSION['user'])) {
    header("Location: index.php");
}

// создаем объект класса DB_Simple
$dbs = DbSimple_Generic::connect("mysql://".SYS_DB_USER.":".SYS_DB_PASS."@".SYS_DB_HOST."/".SYS_DB_BASE);
$dbs->query("SET character_set_client = 'utf8', 
			character_set_connection = 'utf8', 
			character_set_results = 'utf8'");

$action = html($g_action);

switch($action)
{
	case "create_new_category":
		$category['user_id'] = $_SESSION['user']['user_id'];
		$category['category_name'] = @html($_GET['name']);
		$category['type'] = @html($_GET['type']);
		$category['parent_id'] = @html($_GET['parent']);
		$category['system_category_id'] = @html($_GET['system']);
		$category['visible'] = 1;
		
		CategoriesClass::createNewCategory($category, &$dbs);
	break;
}

?>