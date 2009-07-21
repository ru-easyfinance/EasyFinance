<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс-родитель для классов контроллеров
 * @author Max Kamashev "ukko" <max.kamashev@gmail.com>
 * @copyright http://home-money.ru/
 * @category template
 * @version SVN $Id$
 */
class Template_Controller {

    /**
     * Если нам были переданы ошибочные данные, генерируем 404 страницу
     * @param $method
     * @param $args
     * @return void
     */
    public function __call($method, $args)
    {
        //@XXX Делаем хак для XDEBUG
        if (substr($method, 0, 7) != '?XDEBUG') {
            error_404();
        }
    }
}