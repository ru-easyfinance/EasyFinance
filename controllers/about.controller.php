<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * ����� ����������� ��� ������ "� ��������"
 * @copyright http://home-money.ru/
 * @version SVN $Id: $
 */

class About_Controller extends Template_Controller
{  
	/**
     * ������ �� ����� Smarty
     * @var <Smarty>
     */
    private $tpl = null;
	
    /**
     * ����������� ������
     * @return void
     */
    function __construct()
    {

    }

	function index()
    {
		$this->tpl   = Core::getInstance()->tpl;
		$this->tpl->assign('no_menu', '1');
		$this->tpl->assign('head_val', '/about/');
        $this->tpl->assign('name_page', 'about');
    }
}