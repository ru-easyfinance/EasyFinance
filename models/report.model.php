<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс модели для управления отчётами
 * @category report
 * @author Max Kamashev (ukko) <max.kamashev@gmail.com>
 * @copyright http://home-money.ru/
 * @version SVN $Id$
 */
class Report_Model {
    /**
     * Ссылка на экземпляр DBSimple
     * @var DbSimple_Mysql
     */
    private $db = NULL;

    /**
     * Конструктор
     * @return void
     */
    function  __construct() {
        $this->db = Core::getInstance()->db;
        require_once 'OFC/OFC_Chart.php';
    }

    /**
     * Возвращает JSON для пирожной диаграммы
     *
     * @param int $drain 0 - доход, 1 - расход
     */
    function getPie($drain = 0) {
        if ($drain == 1) {
            $title = new OFC_Elements_Title('Расход');
        } else {
            $title = new OFC_Elements_Title('Доход за период с 12.12.12 по 12.12.12');
        }
        //http://teethgrinder.co.uk/open-flash-chart-2/pie-chart.php
        
    }

}