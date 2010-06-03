<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера для модуля Администрирования системой
 * @copyright http://easyfinance.ru/
 * SVN $Id: admin.controller.php 83 2009-07-07 14:33:54Z korogen $
 */

class Admin_Controller extends Template_Controller
{
    /**
     * Ссылка на класс Smarty
     * @var <Smarty>
     */
    private $tpl = null;

    /**
     * Ссылка на класс модель
     * @var <Accounts_Model>
     */
    private $model = null;

    /**
     * Конструктор класса
     * @return void
     */
    function __construct()
    {
        if (Core::getInstance()->user->getUserProps('login')!='Anthrax') {
            error_404();
        }
        $this->user = Core::getInstance()->user;
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
        $this->tpl->assign("page_title", "admin all");
    $this->tpl->assign("template", "admin.default");
    }

    public function account_save_type()
    {
        $id = (int)$_POST['type_id'];
        $name = htmlspecialchars($_POST['type_name']);
        if ($name)
        {
            $this->model->saveTypeAccount($name,$id);
        }
        $this->tpl->assign("list_type", $this->model->getAccountsTypeList());
        die($this->tpl->fetch("admin/accounts/accounts.type_list.html"));
    }


    public function account_save_fields()
    {
        $fields['field_description_id'] = (int)$_POST['id'];
    $fields['field_visual_name']    = htmlspecialchars($_POST['field_visual_name']);
    $fields['field_name']           = htmlspecialchars($_POST['field_name']);
    $fields['field_default_value']  = htmlspecialchars($_POST['field_default_value']);
    $fields['field_type']           = $_POST['field_type'];
    $fields['field_regexp']         = $_POST['field_regexp'];
    $fields['field_permissions']    = $_POST['field_permissions'];
    if (isset($fields['field_visual_name']) && $fields['field_visual_name'] != "")
    {
            $this->model->saveFieldAccount($fields);
    }
    $this->tpl->assign("list_fields", $this->model->getAccountsFieldsList());
        die($this->tpl->fetch("admin/accounts/accounts.fields_list.html"));
    }

    public function account_save_type_fields()
    {
                $id = (int)$_POST['type_id'];
        $type = (int)$_POST['type'];
        $field = (int)$_POST['field'];
        if (!empty($type) && !empty($field))
        {
                    $this->model->saveTypeFieldAccount($id,$type,$field);
                    $this->tpl->assign("list_type_fields", $this->model->getAccountsTypeFieldsList());
                    echo $this->tpl->fetch("admin/accounts/accounts.$args[0]_list.html");
                    die();
        }
        $this->tpl->assign("list_type", $this->model->getAccountsTypeList());
        $this->tpl->assign("list_fields", $this->model->getAccountsFieldsList());
        $this->tpl->assign("list_type_fields", $this->model->getAccountsTypeFieldsList());
        $this->tpl->assign("template", "accounts/accounts.type_fields");
    }

    public function account_del_type()
    {
        $id = $_POST['id'];
        $this->model->del_type($id);
        die(json_encode($id));
    }

    public function account_del_fields()
    {
        $id = $_POST['id'];
        $this->model->del_fields($id);
        die(json_encode($id));
    }

    public function account_del_type_fields()
    {
        $id = $_POST['id'];
        $this->model->del_type_fields($id);
        die(json_encode($id));
    }
    /**
     * Страница управления счетами
     * @param $args array mixed
     * @return void
     */
    function accounts($args)
    {
        if (!$args[0])
            $this->tpl->assign("template", "admin.accounts");

        switch ($args[0])
    {
            case "type_fields":
                $this->tpl->assign("list_type", $this->model->getAccountsTypeList());
                $this->tpl->assign("list_fields", $this->model->getAccountsFieldsList());
                $this->tpl->assign("list_type_fields", $this->model->getAccountsTypeFieldsList());
                $this->tpl->assign("template", "accounts/accounts.".$args[0]);
                break;
            case "type":
                $this->tpl->assign("list_type", $this->model->getAccountsTypeList());
                $this->tpl->assign("template", "accounts/accounts.".$args[0]);
                break;
            case "fields":
        $this->tpl->assign("list_fields", $this->model->getAccountsFieldsList());
                $this->tpl->assign("template", "accounts/accounts.".$args[0]);
                break;
    }
    }
}