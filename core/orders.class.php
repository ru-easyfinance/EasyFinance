<?
/**
* file: orders.class.php
* author: Roman Korostov
* date: 07/03/07	
**/

class Orders  
{
	var $db             = false;
    var $user           = false;
	var $user_id        = 0;
	
	function __construct(&$db, &$user)
	{
		if (is_object($db) && is_a($db,'sql_db') && is_object($user) && is_a($user,'User')) {
			$this->db = $db;
			$this->user = $user;
			$this->user_id = $user->getId();
			//$this->current_year = date('Y');
			
			return true;                
		}
		else {
			message_error(GENERAL_ERROR, 'Ошибка в загрузке объектов!', '', __LINE__, __FILE__);
			return false;
		}
	}
}
?>