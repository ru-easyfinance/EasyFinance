<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Восстановление пароля
 * @category restore
 * @copyright http://easyfinance.ru/
 */
class Restore_Controller extends _Core_Controller
{
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
		$restoreHash = base64_encode( $this->generateCode() . 'RfacztT' . (time() + 60) );
		
		$_SESSION['restoreHash'] = $restoreHash;
		setcookie('sessIds', $restoreHash, time() + 60);
	}
	
	public function confirm( array $args)
	{
		$code = trim( str_ireplace('/','',$args[0]) );
		
		if( $code == '' )
		{
			header('Location: /restore');
		}
		
		$this->tpl->assign('name_page', 'profile/restore/confirm');
	}
	
	public function submit_request()
	{
		$json = array();
		
		if( !isset($_POST['login']) )
		{
			$json['error']['text'] = 'Необходимо указать логин!';
		}
		
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
			$json['error']['text'] = 'Произошла ошибка! Пожалуйста, повторите запрос.';
		}
		
		try//Пробуем загрузить пользователя с указанным логином. И, если вышло - сохраняем запрос в базу.
		{
			$moron = _User::loadByLogin( $_POST['login'] );
			
			$code = $this->storeRequest( $moron );
			
			$href = 'https://' . URL_ROOT . 'restore/confirm/' . $code;
			
			$body = '<html><head><title>Запрос на восстановление пароля :: EasyFinance.ru</title></head>
				<body><p>Здравствуйте, ' . $moron->getName() . '!</p>
				<p>Был произведён запрос на восстановление пароля от вашей учётной записи.<br/>
				Чтобы завершить процедуру и изменить пароль, перейдите по ссылке:</p>
				<p><a href="' . $href . '">' . $href . '</a></p>
				<p>C уважением,
				<br/>Администрация системы <a href="https:// ' . URL_ROOT . '" />EasyFinance.ru</a>
				</body>
				</html>';
			
			$subject = "";
            
			$message = Swift_Message::newInstance()
			->setSubject( 'Запрос на восстановление пароля :: EasyFinance.ru' )// Заголовок
			->setFrom( array('support@easyfinance.ru' => 'EasyFinance.ru') )// Указываем "От кого"
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
			$json['error']['text'] = 'Произошла ошибка! Пожалуйста, повторите запрос.';
		}
		
		exit( json_encode($json));
	}
	
	public function submit_confirm()
	{
		
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
		$row = Core::getInstance()->db->selectRow( 'select * from registration where reg_id=?', $code );
		
		if( !$row || !is_array($row) || !sizeof($row) )
		{
			throw Exception('Row with given code do not exist!');
		}
		
		if( $row['date'] )
		
		return _User::load( $row['user_id'] );
	}
}
