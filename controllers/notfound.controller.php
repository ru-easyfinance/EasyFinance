<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);

class Notfound_Controller extends _Core_Controller
{
	function __init()
	{
		
	}
	
	function index()
	{
		$this->tpl->assign('no_menu', '1');
		$this->tpl->assign('name_page', '404');
	}
}
