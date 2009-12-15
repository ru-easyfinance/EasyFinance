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
	$restoreHash = base64_encode( md5( microtime(true) . session_id() ) . 'RfacztT' . time() + 60);
	
	$_SESSION['restoreHash'] = $restoreHash;
	setcookie('sessIds', $restoreHash, time() + 60);
	
    }
    
    public function confirm()
    {
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
    	
    	
    	
    	return $json;
    }

    public function submit_confirm()
    {
    	
    }
}
