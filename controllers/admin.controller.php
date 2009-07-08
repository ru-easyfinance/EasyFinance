<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера для модуля Администрирования системой
 * @copyright http://home-money.ru/
 * SVN $Id: admin.controller.php 83 2009-07-07 14:33:54Z korogen $
 */
 
class Admin_Controller extends Template_Controller
{
	private $model = null;
	
	/**
     * Конструктор класса
     * @return void
     */
    function __construct()
    {
        $this->user = Core::getInstance()->user;
        $this->tpl = Core::getInstance()->tpl;
        $this->model = new Admin_Model();

        $this->tpl->assign('name_page', 'admin/admin');
    }
	
	/**
     * Индексная страница
     * @param $args array mixed
     * @return void
     */
    function index($args)
    {
        if (!$this->user->getId()) {
            header("Location: /");
            exit;
        }

        $this->tpl->assign("page_title", "admin all");
		$this->tpl->assign("template", "admin.default");
    }
	
	/**
     * Страница управления счетами
	 * @param $args array mixed
     * @return void
     */
	function accounts($args)
	{
		switch ($args[0])
		{
			case "type":			    
				$id = (int)$_POST['type_id'];
				$name = htmlspecialchars($_POST['type_name']);
				
				if (isset($name) && $name != "")
				{
					$this->model->saveTypeAccount($name,$id);
					$this->tpl->assign("list_type", $this->model->getTypeList());
					echo $this->tpl->fetch("admin/accounts/accounts.type_list.html");
					die();
				}
				$this->tpl->assign("list_type", $this->model->getTypeList());
			    $this->tpl->assign("template", "accounts/accounts.type");
			break;
			default:
			    $this->tpl->assign("template", "admin.accounts");
			break;
		}		
	}
}