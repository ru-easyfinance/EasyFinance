<?
define("SCRIPTS_ROOT", "../");
require_once (SCRIPTS_ROOT . "core/classes/hmTagsTree.class.php");
require_once (SCRIPTS_ROOT . "core/external/DBSimple/Mysql.php");
if (empty($_SESSION['user'])) {
    header("Location: index.php");
}

$action = html($g_action);

switch ($action) {
case "add":
	
	switch($cid = hmTagsTree::CreateNewTag($_GET['name'], $_SESSION['user']['user_id'], $_GET['parent'])) {
	case hmTagsTree::AlreadyExists:
		$tpl->assign("error", hmTagsTree::AlreadyExists);
	break;
	case hmTagsTree::NameError:
		$tpl->assign("error", hmTagsTree::NameError);
	break;
	case hmTagsTree::DatabaseError:
		$tpl->assign("error", mysql_error());
	break;
	default:
		$tpl->assign("success", $cid);
	break;
	}
	$tpl->display("tags_ajax.html");exit;
break;
case "delete":
	switch(hmTagsTree::DeleteTag($_GET['id'])) {
	case hmTagsTree::DatabaseError:
		$tpl->assign("error", hmTagsTree::DatabaseError);
	break;
	default:
		$tpl->assign("success", $_GET['id']);
	break;	
	}
	$tpl->display("tags_ajax.html");exit;
break;
case "edit":
	$tag = new hmTag;
	if ($cid = $tag->Load($_GET['id'])) {
		if ($cid1 = $tag->Rename($_GET['name'])) {
			$tpl->assign("success", $cid);
		} else {
			$tpl->assign("error", $cid);
		}
	} else {
		$tpl->assign("error", $cid);
	}
	$tpl->display("tags_ajax.html");exit;
break;
}
?>