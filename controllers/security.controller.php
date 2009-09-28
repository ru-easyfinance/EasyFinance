<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера для модуля "безопасность"
 * @copyright http://home-money.ru/
 * @version SVN $Id: $
 */

class Security_Controller extends Template_Controller
{  
	/**
     * Ссылка на класс Smarty
     * @var <Smarty>
     */
    private $tpl = null;
	
    /**
     * Конструктор класса
     * @return void
     */
    function __construct()
    {

    }

	function index()
    {
		$this->tpl   = Core::getInstance()->tpl;
		$this->tpl->assign('no_menu', '1');
		$this->tpl->assign('head_val', '/security/');
        $this->tpl->assign('name_page', 'security');
    }
}