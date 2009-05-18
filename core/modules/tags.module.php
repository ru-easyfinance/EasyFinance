<?
define("SCRIPTS_ROOT", "../");
require_once (SCRIPTS_ROOT . "core/classes/hmTagsTree.class.php");
require_once (SCRIPTS_ROOT . "core/external/DBSimple/Mysql.php");

if (empty($_SESSION['user'])) {
    header("Location: index.php");
}

$tpl->assign('name_page', 'tags');

$action = html($g_action);

switch ($action) {
    default:
        
		$tpl->assign('name_page', 'tags');
        $dbs = DbSimple_Generic::connect("mysql://".SYS_DB_USER.":".SYS_DB_PASS."@".SYS_DB_HOST."/".SYS_DB_BASE);
		$dbs->query("SET NAMES utf8");
		$tags = new hmTagsTree(&$dbs);
        $tags->loadUserTree($_SESSION['user']['user_id']);
        $tpl->assign_by_ref("tags", $tags->Tree());
		        
        if ($_SESSION['good_text']) {
            $tpl->assign('good_text', $_SESSION['good_text']);
            $_SESSION['good_text'] = false;
        }

        break;
}
?>