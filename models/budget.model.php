<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс модели для управления бюджетом
 * @category budget
 * @author Max Kamashev <max.kamashev@gmail.com>
 * @copyright http://home-money.ru/
 * @version SVN $Id: budget.model.php 119 2009-08-04 15:40:26Z korogen $
 */
class Budget_Model {
    /**
     * Ссылка на экземпляр класса базы данных
     * @var DbSimple_Mysql
     */
    private $db = null;

    /**
     * Ссылка на экземпляр класса пользователя
     * @var User
     */
    private $user = null;

    /**
     * Массив со списком ошибок, появляющимися при добавлении, удалении или редактировании (если есть)
     * @var array mixed
     */
    public $errorData = array();

    /**
     * Конструктор
     * @return bool
     */
    function __construct()
    {
        $this->db = Core::getInstance()->db;
        $this->user = Core::getInstance()->user;
        return true;
    }

  /**
   * Получает план бюджета
   * @param string $date период, за который надо показать план
   *
   * @return array список категорий, общая сумма и настройки плана
   * @access public
   */
  public function getUserPlan($dateFrom = false)
  {
      list($year,$month,$day) = explode("-", $dateFrom);
      $dateTo = date("Y-m-d", mktime(0, 0, 0, $month+2, "01", $year));

      $sql = "SELECT * FROM plan_settings WHERE user_id=? AND date_start_plan>=? AND date_finish_plan <=?";
      $plan = $this->db->select($sql, $this->user->getId(), $dateFrom, $dateTo);

      return $plan;
  }

    /**
    * Получает категории для бюджета
    * @param string $drain доход или расход
    *
    * @return array список категорий
    * @access public
    */
    public function getCategories($type)
    {
        $sql = "select * from category where user_id=? and (type=? or type=2) order by cat_name";
        $plan = $this->db->select($sql, $this->user->getId(), $type);
        pre($plan);
    }
}