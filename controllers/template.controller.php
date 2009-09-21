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
     * При завершении работы, контроллера
     */
    function __destruct()
    {
//periodic	*[key]	id	ид			выводить за сегодня и завтра
//		title	название
//		date	дата
//		amount	сумма
//user_targets	*[key]<=5	title	название
//		amount_done	сумма
//		percent_done	проценты
//		date_end	Дата завершения
//popup_targets	*[key]=5	title	название			отсортировать по популярности
///---------------------/
//currency	*[key]	cost	курс
//		name	имя
//		progress	прогресс(up или down)
//flash	title	имя				общее состояние(сумма всех показателей)
//	value	значение
//	color	Цвет (1 красный,2 желтый, 3 зелёный)

//var res = {tags:['asd'],
//    accounts : {1:{type:'1',id:'1',cur:'rur',def_cur:'12',name:'a',total_balance:'123'}},
//    periodic:{1:{id:1,title:'asd',date:'12.12.1111',amount:'1231231.12'}},
//    user_targets:{1:{title:'asd',amount_done:'134',percent_done:'34',date_end:'12.12.1211'}},
//    popup_targets:{1:{title:'asd'}},
//    currency:{1:{cost:'12',name:'ero',progress:'down'}},
//    flash:{title:'asdad',value:100,color:1}};


        $user = Core::getInstance()->user;
        if (is_null($user->getId())) { 
            Core::getInstance()->tpl->assign('res', '[]');
            return false;
        }

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
                        'percent_done' => $v['percent_done']
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
                'id' => '',
                'title' => '',
                'date' => '',
                'amount'=>''),

            'user_targets' => $targets['user_targets'],
            'popup_targets' => $targets['pop_targets'],
            'currency' => $currency,
            'flash' => array(
                'title' => '',
                'value' => 0,

            )
        )));
    }
}