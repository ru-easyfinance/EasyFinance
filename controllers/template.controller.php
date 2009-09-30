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
    
    /**
     * 
     */
    private function loadJS ()
    {
        $mdl = strtolower(Core::getInstance()->url[0]);
        if (DEBUG) {
            $sfx='.js';
        } else {
            $sfx='.min.js';
        }
        
        foreach (Core::getInstance()->js[$mdl] as $v) {
            Core::getInstance()->tpl->append('js', $v.$sfx);
        }
    }

    /**
     * При завершении работы, контроллера
     */
    function __destruct()
    {
        $this->loadJS();
//periodic	*[key]	id	ид			выводить за сегодня и завтра
//		title	название
//		date	дата
//		amount	сумма

//flash	title	имя				общее состояние(сумма всех показателей)
//	value	значение
//	color	Цвет (1 красный,2 желтый, 3 зелёный)

//var res = {tags:['asd'],
//    periodic:{1:{id:1,title:'asd',date:'12.12.1111',amount:'1231231.12'}},
//    flash:{title:'asdad',value:100,color:1}};


        $user = Core::getInstance()->user;
        if (is_null($user->getId())) { 
            Core::getInstance()->tpl->assign('res', json_encode(array('errors'=>Core::getInstance()->errors)));
            Core::getInstance()->tpl->assign('url_root', URL_ROOT);
            return false;
        }
        Core::getInstance()->tpl->assign('account', Core::getInstance()->user->getUserAccounts());
        // Подготавливаем счета
        $accounts = array();
        foreach ($user->getUserAccounts() as $v) {
            $accounts[$v['account_id']] =array(
                'id'            => $v['account_id'],
                'type'          => $v['account_type_id'],
                'cur'           => $v['account_currency_name'],
                'name'          => $v['account_name'],
                'descr'         => $v['account_description'],
                'def_cur'       => Core::getInstance()->currency[$v['account_currency_id']]['value'] * $v['total_sum'],
                'total_balance' => $v['total_sum']
            );
        }
        
        // Подготавливаем фин.цели
        $targets = array();
        foreach ($user->getUserTargets() as $key => $var) {
            if ($key == 'user_targets') {
                foreach ($var as $v) {
                    $targets['user_targets'][$v['id']] = array(
                        'title'        => $v['title'],
                        'date_end'     => $v['end'],
                        'amount_done'  => $v['amount_done'],
                        'percent_done' => $v['percent_done'],
                        'money'        => $v['money'],
                        'account'      => $v['account'],
                        'amount_done'  => $v['amount_done'],
                    );
                }
            } elseif ($key == 'pop_targets') {
                foreach ($var as $v) {
                    $targets['pop_targets'][] = array(
                        'title'        => $v['title']
                    );
                }
            }
        }
        $currency = array();
        foreach ($user->getUserCurrency() as $k => $v) {
            $currency[$k] = array(
                'cost' => $v['value'],
                'name' => $v['charCode'],
                'progress' => ''
            );
        }

        Core::getInstance()->tpl->assign('res', json_encode(array(
            'tags' => $user->getUserTags(),
            'accounts' => $accounts,
            'periodic' => array(
                'id' => array(
                    'title' => '',
                    'date' => '',
                    'amount'=>'')
                ),
            'user_targets' => $targets['user_targets'],
            'popup_targets' => $targets['pop_targets'],
            'currency' => $currency,
            'flash' => array(
                'title' => '',
                'value' => 0,
            ),
            'targets_category'=>array(
                '1' => 'Квартира',
                '2' => 'Автомобиль',
                '3' => 'Отпуск',
                '4' => 'Финансовая подушка',
                '0' => 'Прочее'
            ),
            'errors'=>Core::getInstance()->errors,
        )));
    }
}