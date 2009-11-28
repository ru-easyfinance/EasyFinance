<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * ����� ����������� ��� ������ "�������"
 * @copyright http://easyfinance.ru/
 * @version SVN $Id: $
 */

class Rules_Controller extends _Core_Controller
{  

	function __init(){}

	function index()
    {
		$this->tpl->assign('no_menu', '1');
		$this->tpl->assign('head_val', '/rules/');
        $this->tpl->assign('name_page', 'rules');
    }
}