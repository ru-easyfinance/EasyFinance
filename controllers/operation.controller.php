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
    }

	/**
	 * Индексная страница
	 * @param $args array mixed
	 * @return void
	 */ 
	function index( $args = array() )
	{
 		$this->tpl->assign('category', 	get_tree_select());
 		$this->tpl->assign('accounts', 	$this->user->getUserAccounts());
 		
	        $this->tpl->assign('dateFrom', 	date('d.m.Y', time() - 60*60*24*7));
	        $this->tpl->assign('dateTo', 	date('d.m.Y')); //date(date('t').'.m.Y'));
 		
 		$this->tpl->assign('name_page', 'operations/operation');
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
			$operation['type'] = $operationTypes[ $args[0] ];
		}
		else
		{
			$operation['type'] = 0;
		}
		
		if( isset( $this->request->get['accountId'] ) && $this->request->get['accountId'])
		{
			$operation['account'] = $this->request->get['accountId'];
		}
		
		// Определяем дефолтную дату
		$operation['date'] = date('j.n.Y');
		
		if( _Core_Request::getCurrent()->method == 'POST' )
		{
			// Определяем массив данных для обработки
			$request = _Core_Request::getCurrent();
			$operation = array(
				//тип операции (расход и тд)
				'type' 		=> isset($request->post['type'])?$request->post['type']:$operation['type'],
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

                                // #856. fixed by Jet. выводим разные сообщения для обычной и PDA версии
                                global $request;
                                $text = '';
                                if (_Core_TemplateEngine::getResponseMode($request) == "json") {
                                    $text = "Операция успешно добавлена.";
                                } else {
                                    $text = "Операция успешно добавлена. <a href='/operation/last'>последние операции</a>";
                                }

                                $this->tpl->assign( 'result',
                                    array('text' => $text)
                                );
			}
			else
			{
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

                                // #856. fixed by Jet. выводим разные сообщения для обычной и PDA версии
                                global $request;
                                $text = '';
                                if (_Core_TemplateEngine::getResponseMode($request) == "json") {
                                    $text = "Операция успешно изменена.";
                                } else {
                                    $text = "Операция успешно изменена. <a href='/operation/last'>последние операции</a>";
                                }

                                $this->tpl->assign( 'result',
                                    array('text' => $text)
                                );
			}
			else 
			{
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
		$request = _Core_Request::getCurrent();
		
		$operationId		= 0;
		
		if( array_key_exists(0 ,$args) && is_numeric($args[0]) && $args[0] )
		{
			$operationId = (int)$args[0];
		}
		elseif( isset($request->post['id']) && $request->post['id'] )
		{
			$operationId = $request->post['id'];
		}
		
		// Если удаление подтверждено....
		if( isset($request->get['confirmed']) && $request->get['confirmed'] )
		{
			if( $this->model->deleteOperation($operationId) )
			{
				$this->tpl->assign( 'result', array('text'=>"Операция успешно удалена.") );
			}
			// Исключительная ситуация.
			else
			{
				$this->tpl->assign( 'error', array('text'=> "Не удалось удалить операцию." ) );
			}
			
			//возвращаемся
			if( array_key_exists('redirect', $_SESSION) )
			{
				_Core_Router::redirect( $_SESSION['redirect'],true );
				unset( $_SESSION['redirect'] );
			}
		}
		// Если нет  - показываем форму для подтверждения
		elseif( !isset($request->get['confirmed']) )
		{
			$confirm= array (
				'title' 		=> 'Удаление операции',
				'message' 	=> 'Вы действительно хотите удалить операцию?',
				'yesLink'	=> '/operation/del/' . $operationId . '?confirmed=1',
				'noLink' 	=> $_SERVER['HTTP_REFERER'],
			);
			
			// Сохраняем в сессии адрес куда идти если согласится
			$_SESSION['redirect'] = $_SERVER['HTTP_REFERER'];
			
			$this->tpl->assign('confirm', $confirm);
			$this->tpl->assign('name_page', 'confirm');
		}
		// Видимо передумали удалять и наша логика не сработала - редиректим на инфо
		else
		{
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
            $this->model->deleteOperation( $v );
        }
        foreach ($virt as $k=>$v) {
            $this->model->deleteTargetOperation($v);
        }
        /*if ($virt) {
            foreach ( $id as $k => $v ) {
                $this->model->deleteTargetOperation($v);
            }
        }*/

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
		if( isset($this->request->get['period']))
		{
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
			
			$this->tpl->assign('period', $this->request->get['period']);
		}
		else
		{
			$this->tpl->assign('period', 'week');
		}
		
		
		
		// Дата окончания
		$dateTo     = isset($this->request->get['dateTo'])?
					Helper_Date::getMysqlFromString($this->request->get['dateTo']):
					Helper_Date::getMysql( time() );
		
		// Категория
		$category   = isset($this->request->get['category'])?(int)$this->request->get['category']:0;
		
		// Счёт
		$account    = isset($this->request->get['account'])?(int)$this->request->get['account']:0;
		$this->tpl->assign( 'accountId' , $account);
		
		//Тип операции
		$type = null;
		if ( !isset($this->request->get['type']) )
		{
			// WTF ?!!
			$type = -1;
		}
		else
		{
			$type = $this->request->get['type'];
		}
		
		// Показывать операции на сумму не меньше ..
		$sumFrom = null;
		if ( isset($this->request->get['sumFrom']) && $this->request->get['sumFrom'] )
		{
			$sumFrom = (float)$this->request->get['sumFrom'];
		}
		
		// Показывать операции на сумму не больше ..
		$sumTo = null;
		if ( isset($this->request->get['sumTo']) && $this->request->get['sumTo'] )
		{
			$sumTo = (float)$this->request->get['sumTo'];
		}
		
		$list = $this->model->getOperationList($dateFrom, $dateTo, $category, $account, $type, $sumFrom, $sumTo);
        
		if( !$list )
		{
			$list = array();
		}
		
		// Привет кэп !
		$array = array();

                // Составляем список операций
		foreach ($list as $key => $operation)
		{
			
			if (!is_null($operation['account_name']))
			{
				$array[$key] = $operation;
			}
			else
			{
				$array[$key] = $operation;
				$array[$key]['account_name'] = '';
			}
		}
		$this->tpl->assign('name_page', 'operations/operation');
		
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
	
	public function last( array $args = array() )
	{
		$operations = $this->model->getLastOperations( 10 );
		
		$this->tpl->assign('operations', $operations);
		
		$this->tpl->assign('name_page', 'operations/last');
	}
}
