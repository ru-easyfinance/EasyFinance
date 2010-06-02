<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера для журанала операций
 * @category operation
 * @copyright http://easyfinance.ru/
 * @version SVN $Id$
 */
class Operation_Controller extends _Core_Controller_UserCommon
{
    /**
     * Модель класса журнала операций
     * @var Operation_Model
     */
    private $model = null;

    /**
     * Ссылка на экземпляр класса User
     * @var User
     */
    private $user = null;


    /**
     * Конструктор класса
     * @return void
     */
    protected function __init()
    {
        $this->model = new Operation_Model();
        $this->user = Core::getInstance()->user;
    }

    /**
     * Индексная страница
     * @param $args array mixed
     * @return void
     */
    function index( $args = array() )
    {
         $this->tpl->assign('category',     get_tree_select());
         $this->tpl->assign('accounts',     $this->user->getUserAccounts());

            $this->tpl->assign('dateFrom',     date('d.m.Y', time() - 60*60*24*7));
            $this->tpl->assign('dateTo',     date('d.m.Y')); //date(date('t').'.m.Y'));

         $this->tpl->assign('name_page', 'operations/operation');
    }

    /**
     * Возвращает дату операции
     * @return string
     */
    function getDateOperation ()
    {
        // Определяем массив данных для обработки

        // Если дата передана массивом (PDA) ...
        if ( is_array ( $this->request->post['date'] ) ) {

            return $this->request->post['date']['day']
                . '.' . $this->request->post['date']['month']
                . '.' . $this->request->post['date']['year'];

            // если пустая дата - подставляем сегодняшний день
        } elseif( empty( $this->request->post['date'] ) ) {

            return date ( "d.m.Y" );

        } else {

            return $this->request->post['date'];

        }
    }

    /**
     * Добавляет новое событие
     * @param $args array mixed Какие-нибудь аргументы
     * @return void
     */
     function add( $args = array() )
    {

        // Определяем массив данных для обработки
        $operation = array();

        // Типы операций для кастомизации логики
        $operationTypes = array_flip( Operation::getTypesArray() );

        if( array_key_exists( 0, $args ) && array_key_exists( $args[0], $operationTypes ) ) {
            $operation['type'] = $operationTypes[ $args[0] ];
        }
        else {
            $operation['type'] = 0;
        }

        if( isset( $this->request->get['accountId'] ) && $this->request->get['accountId']) {
            $operation['account'] = $this->request->get['accountId'];
        }

        // Определяем дефолтную дату
        $operation['date'] = date('j.n.Y');

        if( $this->request->method == 'POST' ) {
            // Определяем массив данных для обработки
            $operation = array (
                //тип операции (расход и тд)
                'type'         => isset($this->request->post['type'])?$this->request->post['type']:$operation['type'],
                'account'     => $this->request->post['account'],
                'amount'     => $this->request->post['amount'],
                'category'     => isset($this->request->post['category'])?$this->request->post['category']:null,
                'date'         => $this->getDateOperation(),
                'comment'     => $this->request->post['comment'],
                'tags'         => isset($this->request->post['tags'])?$this->request->post['tags']:null,
                'convert'     => isset($this->request->post['convert'])?$this->request->post['convert']:array(),
                'close'     => isset($this->request->post['close'])?$this->request->post['close']:array(),
                'currency'     => isset($this->request->post['currency'])?$this->request->post['currency']:array(),
                'toAccount' => isset($this->request->post['toAccount'])?$this->request->post['toAccount']:null,
                'target'     => isset($this->request->post['target'])?$this->request->post['target']:null,
                'accepted'  => isset($this->request->post['accepted'])?(int)$this->request->post['accepted']:1,
            );

            $operation = $this->model->checkData($operation);

            // Если есть ошибки, то возвращаем их пользователю в виде массива
            if (sizeof($this->model->errorData) == 0) {
                // Добавление в зависимости от типа (расход\доход) и тд
                $operation['drain'] = 1;
                switch ($operation['type'])
                {
                    //Расход
                    case Operation::TYPE_WASTE:
                        $operation['amount'] = abs($operation['amount']) * -1;

                        $this->model->add(
                            $operation['amount'],
                            $operation['date'],
                            $operation['category'],
                            $operation['drain'],
                            $operation['comment'],
                            $operation['account'],
                            $operation['tags']
                        );
                        break;
                    // Доход
                    case Operation::TYPE_PROFIT:
                        $operation['drain'] = 0;
                        $this->model->add(
                            $operation['amount'],
                            $operation['date'],
                            $operation['category'],
                            $operation['drain'],
                            $operation['comment'],
                            $operation['account'],
                            $operation['tags']
                        );
                        break;
                    // Перевод со счёта
                    case Operation::TYPE_TRANSFER:
                        $operation['category'] = -1;
                        $this->model->addTransfer(
                            $operation['amount'],
                            $operation['convert'],
                            $operation['currency'],
                            $operation['date'],
                            $operation['account'],
                            $operation['toAccount'],
                            $operation['comment'],
                            $operation['tags']
                            );
                        break;
                    // Перевод на финансовую цель
                    case Operation::TYPE_TARGET:
                        $target = new Targets_Model();
                        $target->addTargetOperation(
                            $operation['account'],
                            $operation['target'],
                            $operation['amount'],
                            $operation['comment'],
                            $operation['date'],
                            $operation['close']
                        );
                        //@FIXME Сделать автоматическое получение нового списка операций, при удачном добавлении
                        //exit(json_encode($target->getLastList(0, 100)));
                    break;
                }

                // #856. fixed by Jet. выводим разные сообщения для обычной и PDA версии
                $text = '';
                if (_Core_TemplateEngine::getResponseMode($this->request) == "json") {
                    $text = "Операция успешно добавлена.";
                } else {
                    $text = "Операция успешно добавлена. <a href='/operation/last'>последние операции</a>";
                }

                $this->tpl->assign( 'result',
                    array('text' => $text)
                );
            } else {
                $this->tpl->assign( 'error', array('text'=> implode(" \n", $this->model->errorData) ) );
            }
        }

        // Переделываем дату изменённую в checkData для вставки в mysql обратно в человеческий вид
        $operation['date'] = date('d.m.Y', strtotime( $operation['date'] ) );

        $this->tpl->assign( 'operation', $operation );

        $this->tpl->assign( 'name_page', 'operations/edit' );
    }

    /**
     * Редактирует событие
     * @param $args array mixed Какие-нибудь аргументы
     * @return void
     */
    function edit( array $args = array() )
    {
        //тип редактируемой операции
        $operationId        = 0;
        $operation         = array();

        if( array_key_exists(0 ,$args) && is_numeric($args[0]) && $args[0] ) {
            $operationId = (int)$args[0];
        } elseif( isset($this->request->post['id']) && $this->request->post['id'] ) {
            $operationId = $this->request->post['id'];
        }

        // Получаем данные по редактируемой операции (а если нет ид, то и даных фиг..)
        if( $operationId ) {
            $operation = $this->model->getOperation( Core::getInstance()->user->getId(), $operationId );
            $initType = $operation['type'];
        }
        else {
            $operation = array();
        }

        if( _Core_Request::getCurrent()->method == 'POST' ) {
            // Определяем массив данных для обработки
            $operation = array(
                'id'         => $operationId,
                //тип операции (расход и тд)
                'type'         => isset($this->request->post['type'])?$this->request->post['type']:$operation['type'],
                'account'     => $this->request->post['account'],
                'amount'     => $this->request->post['amount'],
                'category'     => isset($this->request->post['category'])?$this->request->post['category']:null,
                // дата определяется ниже
                'date'         => null,
                'comment'     => isset($this->request->post['comment'])?$this->request->post['comment']:'',
                'tags'         => isset($this->request->post['tags'])?$this->request->post['tags']:$operation['tags'],
                'convert'     => isset($this->request->post['convert'])?$this->request->post['convert']:array(),
                'close'     => isset($this->request->post['close'])?$this->request->post['close']:array(),
                'currency'     => isset($this->request->post['currency'])?$this->request->post['currency']:array(),
                'toAccount'     => isset($this->request->post['toAccount'])?$this->request->post['toAccount']:null,
                'target'     => isset($this->request->post['target'])?$this->request->post['target']:null,
                'tr_id'        => isset($operation['tr_id'])?$operation['tr_id']:0,
                'accepted'  => isset($this->request->post['accepted'])?(int)$this->request->post['accepted']:1,
            );

            // Если дата передана массивом (PDA) ...
            if( is_array($this->request->post['date']) ) {
                $operation['date'] = $this->request->post['date']['day']
                    . '.' . $this->request->post['date']['month']
                    . '.' . $this->request->post['date']['year'];
            }

            // если пустая дата - подставляем сегодняшний день
            elseif( empty($this->request->post['date']) ) {
                $operation['date'] = date("d.m.Y");
            } else {
                $operation['date'] = $this->request->post['date'];
            }

            $operation = $this->model->checkData($operation);

            if ( is_null($operation['type']) ) {
                $this->model->errorData['id'] = 'Не удалось изменить операцию';
            }

            // Если нет ошибок - проводим операцию
            if (count($this->model->errorData) == 0) {
                // Видимо какая то часть дальнейшей логики
                $operation['drain'] = 1;

                //если изменили тип операции
                if ( $operation['type'] != $initType ) {
                    if ( $initType == 4 ) {
                        $this->model->deleteTargetOperation( $operation['id'] );
                    }
                    else {
                        $this->model->deleteOperation( $operation['id'] );
                    }

                    //удалили операцию. вот теперь создадим новую

                    switch ($operation['type'])
                    {
                        //Расход
                        case Operation::TYPE_WASTE:
                            $operation['amount'] = abs($operation['amount']) * -1;

                            $this->model->add(
                                $operation['amount'],
                                $operation['date'],
                                $operation['category'],
                                $operation['drain'],
                                $operation['comment'],
                                $operation['account'],
                                $operation['tags']
                            );
                            break;
                        // Доход
                        case Operation::TYPE_PROFIT:
                            $operation['drain'] = 0;
                            $this->model->add(
                                $operation['amount'],
                                $operation['date'],
                                $operation['category'],
                                $operation['drain'],
                                $operation['comment'],
                                $operation['account'],
                                $operation['tags']
                            );
                            break;
                        // Перевод со счёта
                        case Operation::TYPE_TRANSFER:
                            $operation['category'] = -1;
                            $this->model->addTransfer(
                                $operation['amount'],
                                $operation['convert'],
                                $operation['currency'],
                                $operation['date'],
                                $operation['account'],
                                $operation['toAccount'],
                                $operation['comment'],
                                $operation['tags']
                                );
                            break;
                        // Перевод на финансовую цель
                        case Operation::TYPE_TARGET:
                            $target = new Targets_Model();
                            $target->addTargetOperation(
                                $operation['account'],
                                $operation['target'],
                                $operation['amount'],
                                $operation['comment'],
                                $operation['date'],
                                $operation['close']
                            );
                            //@FIXME Сделать автоматическое получение нового списка операций, при удачном добавлении
                            //exit(json_encode($target->getLastList(0, 100)));
                        break;
                    }
                }

                // а иначе редактируем по старому, конкретную операцию
                switch ($operation['type'])
                {
                    case Operation::TYPE_WASTE:  //Расход
                        $operation['amount'] = abs($operation['amount']) * -1;
                        $this->model->edit($operation['id'],$operation['amount'],
                            $operation['date'], $operation['category'], $operation['drain'],
                            $operation['comment'], $operation['account'], $operation['tags']);
                        break;
                    case Operation::TYPE_PROFIT: //Доход
                        $operation['drain'] = 0;
                        $this->model->edit($operation['id'],$operation['amount'], $operation['date'],
                            $operation['category'], $operation['drain'], $operation['comment'],
                            $operation['account'], $operation['tags']);
                        break;
                    case Operation::TYPE_TRANSFER: // Перевод со счёта
                        $operation['category'] = -1;
                        $this->model->editTransfer( @$operation['tr_id']?$operation['tr_id']:$operation['id'],
                            $operation['amount'], $operation['convert'], $operation['date'], $operation['account'],
                            $operation['toAccount'],$operation['comment'],$operation['tags']);
                        break;
                    case Operation::TYPE_TARGET: // Перевод на финансовую цель см. в модуле фин.цели
                        $target = new Targets_Model();
                        $target->editTargetOperation($operation['id'], $operation['amount'],
                            $operation['date'], $operation['target'],$operation['account'],
                            $operation['comment'], $operation['close']);
                    break;
                }

                // #856. fixed by Jet. выводим разные сообщения для обычной и PDA версии
                $text = '';
                if (_Core_TemplateEngine::getResponseMode($this->request) == "json") {
                    $text = "Операция успешно изменена.";
                } else {
                    $text = "Операция успешно изменена. <a href='/operation/last'>последние операции</a>";
                }

                $this->tpl->assign( 'result',
                    array('text' => $text)
                );
            } else {
                $this->tpl->assign( 'error', array('text'=> implode(" \n", $this->model->errorData) ) );
            }
        }

        // Переделываем дату изменённую в checkData для вставки в mysql обратно в человеческий вид
        $operation['date'] = date('d.m.Y', strtotime( $operation['date'] ) );

        $this->tpl->assign( 'operation', $operation );

        $this->tpl->assign( 'name_page', 'operations/edit' );
    }

    /**
     * Удаляет выбранное событие
     * @param $args array mixed Какие-нибудь аргументы
     * @return void
     */
    function del($args)
    {
        $operationId        = 0;

        if( array_key_exists(0 ,$args) && is_numeric($args[0]) && $args[0] ) {
            $operationId = (int)$args[0];
        } elseif( isset($this->request->post['id']) && $this->request->post['id'] ) {
            $operationId = $this->request->post['id'];
        }

        // Если удаление подтверждено....
        if( isset($this->request->get['confirmed']) && $this->request->get['confirmed'] ) {
            if( $this->model->deleteOperation($operationId) ) {
                $this->tpl->assign( 'result', array('text'=>"Операция успешно удалена.") );
            }
            // Исключительная ситуация.
            else {
                $this->tpl->assign( 'error', array('text'=> "Не удалось удалить операцию." ) );
            }

            //возвращаемся
            if( array_key_exists('redirect', $_SESSION) ) {
                _Core_Router::redirect( $_SESSION['redirect'],true );
                unset( $_SESSION['redirect'] );
            }
        }
        // Если нет  - показываем форму для подтверждения
        elseif( !isset($this->request->get['confirmed']) ) {
            $confirm= array (
                'title'         => 'Удаление операции',
                'message'     => 'Вы действительно хотите удалить операцию?',
                'yesLink'    => '/operation/del/' . $operationId . '?confirmed=1',
                'noLink'     => $_SERVER['HTTP_REFERER'],
            );

            // Сохраняем в сессии адрес куда идти если согласится
            $_SESSION['redirect'] = $_SERVER['HTTP_REFERER'];

            $this->tpl->assign('confirm', $confirm);
            $this->tpl->assign('name_page', 'confirm');
        }
        // Видимо передумали удалять и наша логика не сработала - редиректим на инфо
        else {
            _Core_Router::redirect( '/info' );
        }
    }

    function deleteTargetOp($args)
    {
        $id = abs((int)$_POST['id']);
        die($this->model->deleteTargetOperation($id));
    }

    /**
     * Удаляет выбранные события
     * @param $args array mixed Какие-нибудь аргументы
     * @return void
     */
    function del_all($args)
    {
        $id = explode(',', $_POST['id']);
        $virt = explode(',', $_POST['virt']);
        foreach ( $id as $k => $v ) {
            if ( (int)$v > 0 ) {
                $this->model->deleteOperation( (int)$v );
            }
        }
        foreach ($virt as $k=>$v) {
            if ( (int)$v > 0 ) {
                $this->model->deleteTargetOperation( (int)$v );
            }
        }

        die(json_encode(array('result'=>array('text'=>'Операция успешно удалена'))));
    }

    /**
     * Получить список операций
     */
    function listOperations($args)
    {
        // Дата начала
        $dateFrom   = isset($this->request->get['dateFrom'])?
                    Helper_Date::getMysqlFromString($this->request->get['dateFrom']):
                    // Если дата не установлена - показываем за последнюю неделю
                    Helper_Date::getMysql( time() - (7*24*60*60) );

        // Костылёк для PDA
        if( isset($this->request->get['period'])) {
            switch ( $this->request->get['period'] )
            {
                case 'month':
                    $dateFrom = Helper_Date::getMysql( time() - (30*24*60*60) );
                    break;
                case 'day':
                    $dateFrom = Helper_Date::getMysql( time() - (1*24*60*60) );
                    break;
                case 'week':
                    $dateFrom = Helper_Date::getMysql( time() - (7*24*60*60) );
            }
            if (_Core_TemplateEngine::getResponseMode($this->request) != "csv") {
                $this->tpl->assign('period', $this->request->get['period']);
            }
        } else {
            if (_Core_TemplateEngine::getResponseMode($this->request) != "csv") {
                $this->tpl->assign('period', 'week');
            }
        }



        // Дата окончания
        $dateTo     = isset($this->request->get['dateTo'])?
                    Helper_Date::getMysqlFromString($this->request->get['dateTo']):
                    Helper_Date::getMysql( time() );

        // Категория
        $category   = isset($this->request->get['category'])?(int)$this->request->get['category']:0;

        // Счёт
        $account    = isset($this->request->get['account'])?(int)$this->request->get['account']:0;

        if (_Core_TemplateEngine::getResponseMode($this->request) != "csv") {
            $this->tpl->assign( 'accountId' , $account);
        }

        //Тип операции
        $type = null;
        if ( !isset($this->request->get['type']) ) {
            // WTF ?!!
            $type = -1;
        } else {
            $type = $this->request->get['type'];
        }

        // Показывать операции на сумму не меньше ..
        $sumFrom = null;
        if ( isset($this->request->get['sumFrom']) && $this->request->get['sumFrom'] ) {
            $sumFrom = (float)$this->request->get['sumFrom'];
        }

        // Показывать операции на сумму не больше ..
        $sumTo = null;
        if ( isset($this->request->get['sumTo']) && $this->request->get['sumTo'] ) {
            $sumTo = (float)$this->request->get['sumTo'];
        }

        $search_field = isset($this->request->get['search_field'])? $this->request->get['search_field'] : '';

        if (_Core_TemplateEngine::getResponseMode($this->request) != "csv") {
            $list = $this->model->getOperationList($dateFrom, $dateTo, $category, $account, $type, $sumFrom, $sumTo, $search_field);
        } else {
            $list = $this->model->getOperationList($dateFrom, $dateTo, $category, $account, $type, $sumFrom, $sumTo, $search_field);
        }

        $firstDate = Helper_Date::getMysqlFromString('01.01.1970');
        $dateBeforeFrom = Helper_Date::getMysql(strtotime($dateFrom." -1 day"));
        $listBefore = $this->model->getOperationMoneyBeforeAndAfter($firstDate, $dateBeforeFrom);
        $listAfter = $this->model->getOperationMoneyBeforeAndAfter($firstDate, $dateTo);

        if( !$list ) {
            $list = array();
        }

        // Привет кэп !
        $array = array();

                // Составляем список операций
        foreach ($list as $key => $operation)
        {

            if (!is_null($operation['account_name'])) {
                $array[$key] = $operation;
            } else {
                $array[$key] = $operation;
                $array[$key]['account_name'] = '';
            }

            if (_Core_TemplateEngine::getResponseMode($this->request) == "csv") switch( $array[$key]['type'] ) {
                case 0: $array[$key]['type'] = 'Расход'; break;
                case 1: $array[$key]['type'] = 'Доход'; break;
                case 2: $array[$key]['type'] = 'Перевод со счёта'; break;
                case 4: $array[$key]['type'] = 'Перевод на фин. цель'; break;
            }
        }
        $this->tpl->assign('name_page', 'operations/operation');

        if (_Core_TemplateEngine::getResponseMode($this->request) != "csv") {
            $this->tpl->assign( 'operations', $array );
            $this->tpl->assign( 'list_before', $listBefore );
            $this->tpl->assign( 'list_after', $listAfter );
        } else { //CSV
            $headers = array('Дата', 'Тип', 'Сумма', 'Счет', 'Категория', 'Метки', 'Комментарий');
//          $headers = array('1', '2', '3', '4', '5', '6', '7');
            $this->tpl->assign( 'elements', $array );
            $this->tpl->assign( 'headers', $headers );
            $this->tpl->assign( 'filename', 'EasyFinance Operations '.$this->request->get['dateFrom']." - ".$this->request->get['dateTo'].'.csv' );
        }
    }

    /**
     * Возвращает валюту пользователя
     * @param array $args
     * @return array
     */
    function get_currency($args) {
        die(json_encode($this->model->getCurrency()));
    }

    /**
     * Список операций для счёта (PDA)
     *
     * @param array $args
     */
    function account( $args = array() )
    {
        // Если указан id счёта
        if( isset($args[0]) && (int)$args[0] ) {
            $accountId = (int)$args[0];

            // Получаем последние 10 операций по нему
            // На самом деле это пока невозможно, получаем все операции
            $operations = $this->model->getOperationList(
                Helper_Date::getMysql( 3 ),
                Helper_Date::getMysql( time() ),
                null,
                $accountId,
                -1,
                null,
                null
            );
        } else {
            //_Core_Router::redirect('/info' , true);
        }

        if( !is_array($operations) ) {
            $operations = array();
        }

        $this->tpl->assign('accountId', $accountId);
        $this->tpl->assign('operations', $operations);
        $this->tpl->assign('name_page', 'account/operations');
    }

    public function last( array $args = array() )
    {
        $operations = $this->model->getLastOperations( 10 );

        $this->tpl->assign('operations', $operations);

        $this->tpl->assign('name_page', 'operations/last');
    }
}
