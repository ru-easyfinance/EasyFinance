<?
define("SCRIPTS_ROOT", "../");
require_once (SCRIPTS_ROOT . "core/classes/hmCategoryTree.class.php");
require_once (SCRIPTS_ROOT . "core/external/DBSimple/Mysql.php");
if (empty($_SESSION['user'])) {
    header("Location: index.php");
}

$action = html($g_action);

switch ($action) {
case "add":
	
	switch($cid = hmCategoryTree::CreateNewCategory($_GET['name'], $_SESSION['user']['user_id'], $_GET['parent'])) {
	case hmCategoryTree::AlreadyExists:
		$tpl->assign("error", hmCategoryTree::AlreadyExists);
	break;
	case hmCategoryTree::NameError:
		$tpl->assign("error", hmCategoryTree::NameError);
	break;
	case hmCategoryTree::NoSuchCategory:
		$tpl->assign("error", hmCategoryTree::NoSuchCategory);
	break;
	case hmCategoryTree::DatabaseError:
		$tpl->assign("error", hmCategoryTree::DatabaseError);
	break;
	default:
		$tpl->assign("success", $cid);
	break;
	}
	$tpl->display("category_ajax.html");exit;
break;
case "delete":
	switch(hmCategoryTree::DeleteCategory($_GET['id'])) {
	case hmCategoryTree::DatabaseError:
		$tpl->assign("error", hmCategoryTree::DatabaseError);
	break;
	default:
		$tpl->assign("success", $_GET['id']);
	break;	
	}
	$tpl->display("category_ajax.html");exit;
break;
case "edit":
	$cat = new hmCategory;
	if ($cid = $cat->Load($_GET['id'])) {
		if ($cid1 = $cat->Rename($_GET['name'])) {
			$tpl->assign("success", $cid);
		} else {
			$tpl->assign("error", $cid);
		}
	} else {
		$tpl->assign("error", $cid);
	}
	$tpl->display("category_ajax.html");exit;
break;
}
?>