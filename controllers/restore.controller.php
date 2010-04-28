<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Восстановление пароля
 * @category restore
 * @copyright http://easyfinance.ru/
 */
class Restore_Controller extends _Core_Controller
{
    private $_timelimit = 900; // 15 минут

	/**
	 * Конструктор класса
	 * @return void
	 */
	protected function __init()
	{
		if( !session_id() )
		{
			session_start();
		}
		
		$this->tpl->assign('no_menu', '1');
	}
	
	public function index()
	{
		$this->tpl->assign('name_page', 'profile/restore/form');
		
		// Валидация homo-sapiens
		$restoreHash = base64_encode( $this->generateCode() . 'RfacztT' . (time() + $this->_timelimit) );
		
		$_SESSION['restoreHash'] = $restoreHash;
		setcookie('sessIds', $restoreHash, time() + $this->_timelimit);
	}
	
	public function submit_request()
	{
		$json = array();
		
		// Валидация homo-sapiens
		$restoreHash = $_SESSION['restoreHash'];
		list( ,$restoreTime ) = explode('RfacztT', base64_decode($restoreHash));
		
		// Тройная проверка ! Теперь на 43% страннее !
		if(
			!isset($_POST['verifyCode']) 
			|| $_POST['verifyCode'] != $restoreHash 
			|| $_COOKIE['sessIds'] != $restoreHash 
			|| time() > $restoreTime
		)
		{
			$json['error']['text'] = 'Произошла ошибка! Пожалуйста, обновите страницу и повторите запрос.';
			exit(json_encode($json));
		}
		
		try//Пробуем загрузить пользователя с указанным логином. И, если вышло - сохраняем запрос в базу.
		{		
			$moron = _User::loadByLogin( $_POST['login'] );
			
			// Уничтожаем хранимый код валидации
			unset($_COOKIE['sessIds'], $_SESSION['restoreHash']);
			
			$code = $this->storeRequest( $moron );
			
			// Хачок для mail.ru (шоб не банило)
			$domain = (URL_ROOT == 'easyfinance.ru')?'easyfin.ru':URL_ROOT;
			
			$href = 'https://' . $domain . 'restore/confirm/' . $code;
			
			$body = '<html><head><title>Запрос на восстановление пароля :: EasyFinance.ru</title></head>
				<body><p>Здравствуйте, ' . $moron->getName() . '!</p>
				<p>Был произведён запрос на восстановление пароля от вашей учётной записи.<br/>
				Чтобы завершить процедуру и изменить пароль, перейдите по ссылке:</p>
				<p><a href="' . $href . '">' . $href . '</a></p>
				<p>C уважением,
				<br/>Администрация системы <a href="https:// ' . $domain . '" />Easy Finance</a>
				</body>
				</html>';
			
			$subject = "";
            
			$message = Swift_Message::newInstance()
			->setSubject( 'Запрос на восстановление пароля :: Easy Finance' )// Заголовок
			->setFrom( array('support@easyfinance.ru' => 'Easy Finance') )// Указываем "От кого"
			->setTo( array( $moron->getMail()=>$moron->getName() ) )// Говорим "Кому"
			->setBody($body, 'text/html');// Устанавливаем "Тело"
			
			// Отсылаем письмо
			if( !Core::getInstance()->mailer->send($message) )
			{
				throw new Exception('');
			}
			
			$json['result']['text'] = 'Запрос успешно обработан. На ваш электронный адрес было выслано письмо с дальнейшими инструкциями.';
		}
		catch( _User_Exception $e )
		{
			$json['error']['text'] = 'Введённый вами логин не существует!';
		}
		catch( Exception $i )
		{
			$json['error']['text'] = 'Произошла ошибка! Пожалуйста, обновите страницу и повторите запрос.';
		}
		
		exit( json_encode($json));
	}
	
	public function confirm( array $args )
	{
		$this->tpl->assign('name_page', 'profile/restore/confirm');
		
		$code = trim( str_ireplace('/','',$args[0]) );
		
		if( $code == '' )
		{
			header('Location: /restore');
		}
		
		try
		{
			$row 		= $this->loadRequest( $code );
			
			$userId 	= $row['user_id'];
			$date		= $row['date'];
		}
		catch( Exception $e )
		{
			header('Location: /restore');
		}
		
		if( (strtotime($date) + 60 * 30) < time() )
		{
			$_SESSION['errorMessage'] = 'Время действия ссылки восстановления пароля истекло! Пожалуйста, повторите запрос.';
			header('Location: /restore');
		}
		
		$_SESSION['restoreCode'] = $code;
	}
	
	public function submit_confirm()
	{
		$json = array();
		
		if( !isset($_SESSION['restoreCode']) || !$_SESSION['restoreCode'])
		{
			$json['error'] = array( 'text' => '', 'redirect' => '/restore' );
			exit( json_encode($json) );
		}
		
		try
		{
			$row 		= $this->loadRequest( $_SESSION['restoreCode'] );
			
			$userId	= $row['user_id'];
			$date 		= $row['date'];
		}
		catch( Exception $e )
		{
			$json['error'] = array('redirect' => '/restore' );
			exit( json_encode($json) );
		}
		
		if( (strtotime($date) + 60 * 30) < time() )
		{
			$json['result'] = array(
				'redirect' => '/restore'
			);
			
			$_SESSION['errorMessage'] = 'Время действия ссылки восстановления пароля истекло! Пожалуйста, повторите запрос.';
			
			exit( json_encode($json) );
		}
		
		$moron = _User::load( $userId );
		$moron->setPass( $_POST['pass'] );
		
		$this->unsetRequest( $_SESSION['restoreCode'] );
		unset($_SESSION['restoreCode'] );
		
		$json['result'] = array(
			'text'=>'',
			'redirect' => 'https://' . URL_ROOT_MAIN . 'login'
		);
		
		$_SESSION['resultMessage'] = 'Изменение пароля успешно выполнено. Теперь вы можете войти в систему используя новый пароль.';
		
		exit( json_encode($json) );
	}
	
	private function generateCode()
	{
		return sha1( microtime(true) . session_id() );
	}
	
	private function storeRequest( _User $moron )
	{
		$code = $this->generateCode();
		
		Core::getInstance()->db->query( 'insert into registration values (?, now(), ?)', $moron->getId(), $code );
		
		return $code;
	}
	
	private function loadRequest( $code )
	{
		$row = Core::getInstance()->db->selectRow( 'select user_id, date from registration where reg_id=?', $code );
		
		if( !$row || !is_array($row) || !sizeof($row) )
		{
			throw new Exception('Row with given code do not exist!');
		}
		
		return $row;
	}
	
	private function unsetRequest( $code )
	{
		// Удаляем запись с кодом подтверждения из бд
		Core::getInstance()->db->query('delete from registration where reg_id=?', $code);
	}
}
