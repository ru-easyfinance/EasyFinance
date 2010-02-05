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
     * @var Money
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
        $this->tpl->assign('name_page', 'operations/operation');

        $targets = new Targets_Model();
        $this->tpl->assign('targetList',     $targets->getLastList(0, 100));
        $this->tpl->assign('accounts',       $this->user->getUserAccounts());
        $this->tpl->assign('dateFrom',       date('d.m.Y', time() - 60*60*24*7));
        $this->tpl->assign('dateTo',         date('d.m.Y')); //date(date('t').'.m.Y'));
    }

	/**
	 * Индексная страница
	 * @param $args array mixed
	 * @return void
	 */ 
	function index( $args = array() )
	{

	}
	
	/**
	 * Добавляет новое событие
	 * @param $args array mixed Какие-нибудь аргументы
	 * @return void
	 */
 	function add( $args = array() )
	{
		$operation = array();
		
		// Типы операций для кастомизации логики
		$operationTypes = array_flip( Operation::getTypesArray() );
		
		if( array_key_exists( 0, $args ) && array_key_exists( $args[0], $operationTypes ) )
		{
			$operationType = $operationTypes[ $args[0] ];
			//$operation['type'] = $operationTypes[ $args[0] ];
		}
		else
		{
			$operationType = 0;
		}
		
		if( _Core_Request::getCurrent()->method == 'POST' )
		{
			// Определяем массив данных для обработки
			$request = _Core_Request::getCurrent();
			$operation = array(
				//тип операции (расход и тд)
				'type' 		=> isset($request->post['type'])?$request->post['type']:$operationType,
				'account' 	=> $request->post['account'],
				'amount' 	=> $request->post['amount'],
				'category' 	=> isset($request->post['category'])?$request->post['category']:null,
				// дата определяется ниже
				'date' 		=> null,
				'comment' 	=> $request->post['comment'],
				'tags' 		=> isset($request->post['tags'])?$request->post['tags']:null,
				'convert' 	=> isset($request->post['convert'])?$request->post['convert']:array(),
				'close' 	=> isset($request->post['close'])?$request->post['close']:array(),
				'currency' 	=> isset($request->post['currency'])?$request->post['currency']:array(),
				'toAccount' 	=> isset($request->post['toAccount'])?$request->post['toAccount']:null,
				'target' 	=> isset($request->post['target'])?$request->post['target']:null,
			);
			
			// Если дата передана массивом (PDA) ...
			if( is_array($request->post['date']) )
			{
				$operation['date'] = $request->post['date']['day'] 
					. '.' . $request->post['date']['month']
					. '.' . $request->post['date']['year'];
			}
			
			// если пустая дата - подставляем сегодняшний день
			elseif( empty($request->post['date']) )
			{
				$operation['date'] = date("d.m.Y");
			}
			else
			{
				$operation['date'] = $request->post['date'];
			}
			
			$operation = $this->model->checkData($operation);
			
			// Если есть ошибки, то возвращаем их пользователю в виде массива
			if (sizeof($this->model->errorData) == 0)
			{
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
				
				$this->tpl->assign( 'result', array('text'=>"Операция успешно добавлена.") );
			}
			else
			{
				$this->tpl->assign( 'error', array('text'=> implode(" \n", $this->model->errorData) ) );
			}
		}
		
		$this->tpl->assign( 'operation', $operation );
		
		$templateName = 'operations/edit_';
		
		switch ( $operationType )
		{
			// доход
			case 0:
				$templateName .= 'waste'; break;
			// расход
			case 1:
				$templateName .= 'profit'; break;
			// перевод
			case 2:
				$templateName .= 'transfer'; break;
			// финцель
			case 4:
				$templateName .= 'target'; break;
		}
		
		$this->tpl->assign( 'name_page', $templateName );
		$this->tpl->assign( 'operationType', $operationType );
	}

	/**
	 * Редактирует событие
	 * @param $args array mixed Какие-нибудь аргументы
	 * @return void
	 */
	function edit( array $args = array() )
	{
		//тип редактируемой операции
		$request = _Core_Request::getCurrent();
		
		$operationId		= 0;
		$operation 		= array();
		
		if( array_key_exists(0 ,$args) && is_numeric($args[0]) && $args[0] )
		{
			$operationId = (int)$args[0];
		}
		elseif( isset($request->post['id']) && $request->post['id'] )
		{
			$operationId = $request->post['id'];
		}
		
		// Получаем данные по редактируемой операции (а если нет ид, то и даных фиг..)
		if( $operationId )
		{
			$operation = $this->model->getOperation( Core::getInstance()->user->getId(), $operationId );
			$initType = $operation['type'];
		}
		else
		{
			$operation = array();
		}
		
		if( _Core_Request::getCurrent()->method == 'POST' )
		{
			// Определяем массив данных для обработки
			$operation = array(
				'id' 		=> $operationId,
				//тип операции (расход и тд)
				'type' 		=> isset($request->post['type'])?$request->post['type']:$operation['type'],
				'account' 	=> $request->post['account'],
				'amount' 	=> $request->post['amount'],
				'category' 	=> isset($request->post['category'])?$request->post['category']:null,
				// дата определяется ниже
				'date' 		=> null,
				'comment' 	=> isset($request->post['comment'])?$request->post['comment']:'',
				'tags' 		=> isset($request->post['tags'])?$request->post['tags']:$operation['tags'],
				'convert' 	=> isset($request->post['convert'])?$request->post['convert']:array(),
				'close' 	=> isset($request->post['close'])?$request->post['close']:array(),
				'currency' 	=> isset($request->post['currency'])?$request->post['currency']:array(),
				'toAccount' 	=> isset($request->post['toAccount'])?$request->post['toAccount']:null,
				'target' 	=> isset($request->post['target'])?$request->post['target']:null,
				'tr_id'		=> isset($operation['tr_id'])?$operation['tr_id']:0,
			);			

			// Если дата передана массивом (PDA) ...
			if( is_array($request->post['date']) )
			{
				$operation['date'] = $request->post['date']['day'] 
					. '.' . $request->post['date']['month']
					. '.' . $request->post['date']['year'];
			}
			
			// если пустая дата - подставляем сегодняшний день
			elseif( empty($request->post['date']) )
			{
				$operation['date'] = date("d.m.Y");
			}
			else
			{
				$operation['date'] = $request->post['date'];
			}
			
			$operation = $this->model->checkData($operation);
			
			if ( is_null($operation['type']) )
			{
				$this->model->errorData['id'] = 'Не удалось изменить операцию';
			}
			
			// Если нет ошибок - проводим операцию
			if (count($this->model->errorData) == 0)
			{
				// Видимо какая то часть дальнейшей логики
				$operation['drain'] = 1;
				
				//если изменили тип операции
				if ( $operation['type'] != $initType )
				{				
					if ( $initType == 4 )
					{
						$this->model->deleteTargetOperation( $operation['id'] );
					}
					else
					{
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
						$this->model->editTransfer( $operation['tr_id']?$operation['tr_id']:$operation['id'],
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
				
				$this->tpl->assign( 'result', array('text'=>"Операция успешно изменена.") );
			}
			else 
			{
				$this->tpl->assign( 'error', array('text'=> implode(" \n", $this->model->errorData) ) );
			}
		}
		
		$this->tpl->assign( 'operation', $operation );
		
		$templateName = 'operations/edit_';
		
		switch ( $operation['type'] )
		{
			// доход
			case 0:
				$templateName .= 'waste'; break;
			// расход
			case 1:
				$templateName .= 'profit'; break;
			// перевод
			case 2:
				$templateName .= 'transfer'; break;
			// финцель
			case 4:
				$templateName .= 'target'; break;
		}
		
		$this->tpl->assign( 'name_page', $templateName );
		$this->tpl->assign( 'operationType', $operation['type'] );
	}

    /**
     * Удаляет выбранное событие
     * @param $args array mixed Какие-нибудь аргументы
     * @return void
     */
    function del($args)
    {
        $id = abs((int)$_POST['id']);
        die($this->model->deleteOperation($id));
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
        /*foreach ($id as $k=>$v)
            if ($virt[$k] != 1)
                $this->model->deleteOperation($v);
            else
                $this->model->deleteTargetOperation($v);*/
        foreach ($id as $k=>$v)
            $this->model->deleteOperation($v);
        foreach ($virt as $k=>$v)
            $this->model->deleteTargetOperation($v);
        die('[]');
    }

    /**
     * Получить список
     */
    function listOperations($args)
    {
        /**
         * Дата начала
         * @var DATETIME Mysql
         */
        $dateFrom   = Helper_Date::getMysqlFromString(@$_GET['dateFrom']);

        /**
         * Дата окончания
         * @var DATETIME Mysql
         */
        $dateTo     = Helper_Date::getMysqlFromString(@$_GET['dateTo']);
        
        /**
         * Категория
         * @var int
         */
        $category   = isset($_GET['category'])?(int)$_GET['category']:0;

        /**
         * Счёт
         * @var int
         */
        $account    = isset($_GET['account'])?(int)$_GET['account']:0;

        /**
         * Тип операции
         * @var int
         * @example
         *  0 - Доход
         *  1 - Расход
         *  2 - Перевод
         *  4 - Фин.Цель //именно 4
         */
        $type = null;
        if (@$_GET['type']== '') {
            $type = -1;
        } else {
            $type = @$_GET['type'];
        }

        /**
         * Показывать операции на сумму не меньше ..
         * @var float
         */
        $sumFrom = null;
        if (@$_GET['sumFrom'] != '') {
            $sumFrom = (float)@$_GET['sumFrom'];
        }
        
        /**
         * Показывать операции на сумму не больше ..
         * @var float
         */
        $sumTo = null;

        if (@$_GET['sumTo'] != '')
        {
            $sumTo = (float)@$_GET['sumTo'];
        }

        $list = $this->model->getOperationList($dateFrom, $dateTo, $category, $account, $type, $sumFrom, $sumTo);
        
        if( !$list )
        {
		$list = array();
        }
        
        $accounts = Core::getInstance()->user->getUserAccounts();

        //@TODO Похоже, что тут надо что-то дописать в массиве
        foreach ($list as $val) {
            if (!is_null($val['account_name'])) {
               $array[$val['id']] = $val;
            } else {
                $array[$val['id']] = $val;
                $array[$val['id']]['account_name'] = '';
            }
        }
        
        $this->tpl->assign( 'operations', $array );
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
		if( isset($args[0]) && (int)$args[0] )
		{
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
		}
		else
		{
			//_Core_Router::redirect('/info' , true);
		}
		
		if( !is_array($operations) )
		{
			$operations = array();
		}
		
		$this->tpl->assign('accountId', $accountId);
		$this->tpl->assign('operations', $operations);
		$this->tpl->assign('name_page', 'account/operations');
	}
}
