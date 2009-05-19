<?php

$action = html($_GET['action']);

switch ($action)
{
	case "security":
		$tpl->assign('name_page', 'about/security');
	break;
}

?>