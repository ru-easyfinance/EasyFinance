<?php
/** 
 * Класс системы экспертов
 */

class hmExpertSystem {

	// Сначала статичные свойства и методы
	
	const DatabaseError = -1;
	const LoginError = -2;
	const MailError = -3;
	const AlreadyExists = -4;
	const PhotoError = -5;
	const EnterError = -6;
	
	private $db = NULL;
	public $expert_costs = array();
	public $expert_categories = array();
	public $system_expert_costs = array();
	public $system_expert_categories = array();
	
	// Регистрация нового эксперта
	static function CreateNewExpert ($reg, $file)
	{
		if (!$db) {
			$db = DbSimple_Generic::connect("mysql://".SYS_DB_USER.":".SYS_DB_PASS."@".SYS_DB_HOST."/".SYS_DB_BASE);
		}
		if (!hmExpertSystem::ValidLogin($reg['login']))	{
			return hmExpertSystem::LoginError;
		}		
		if (!hmExpertSystem::ValidEmail($reg['mail'])) {
			return hmExpertSystem::MailError;
		}		
		if (hmExpertSystem::CheckExistsByLogin($reg['login'], &$db))	{
			return hmExpertSystem::AlreadyExists;
		}
		if (!empty($file)) {
			if (!$photo = hmExpertSystem::UploadPhoto($file, $reg['login'])) {
				return hmExpertSystem::PhotoError;
			}
		}
		$row = array(
				'login' => $reg['login'], 
				'password' => md5($reg['pass']),
				'first_name' => $reg['f_name'], 
				'last_name' => $reg['l_name'],
				'middle_name' => $reg['m_name'],
				'email' => $reg['mail'],
				'last_name' => $reg['l_name'],
				'photo' => $photo,
				'date_created' => date("Y-m-d")
				);
		if (!$db->query('INSERT INTO experts(?#) VALUES(?a)', array_keys($row), array_values($row)))
		{
			return hmExpertSystem::DatabaseError;
		}
		return 777;
	}
	
	// Проверяет валидность имени
	static function ValidLogin($login) 
	{
		if(preg_match("/^[a-zA-Z0-9_]+$/", $login)) {
			return true;
		}		
		return false;
	}
	
	// Проверяет валидность email
	static function ValidEmail($email) 
	{
		if (preg_match('/^[a-z0-9&\'\.\-_\+]+@[a-z0-9\-]+\.([a-z0-9\-]+\.)*?[a-z]+$/is', $email)) {
			return true;
		}
		return false;
	}
	
	// Проверяет, существует ли эксперт с таким именем?
	static function CheckExistsByLogin($login, &$db) {
		if (!$db) {
			$db = DbSimple_Generic::connect("mysql://".SYS_DB_USER.":".SYS_DB_PASS."@".SYS_DB_HOST."/".SYS_DB_BASE);
		}
		$rows = $db->selectRow("SELECT login FROM experts WHERE login = ?", $login);

		if (count($rows)) {
			return true;
		} else {
			return false;
		}
	}
	
	// Загружает фотографию на сервер
	static function UploadPhoto($file, $file_name) {		
		$file_type = substr($file['photo']['name'], 1 + strrpos($file['photo']['name'], "."));
		$file_name = $file_name.".".$file_type;
		if (!move_uploaded_file($file['photo']['tmp_name'], UPLOAD_DIR . $file_name)) {
			return hmExpertSystem::PhotoError;
		}
		return $file_name;
	}
	
	// Добавление нового вопроса
	static function CreateNewTopic ($question, $id)
	{
		if (!$db) {
			$db = DbSimple_Generic::connect("mysql://".SYS_DB_USER.":".SYS_DB_PASS."@".SYS_DB_HOST."/".SYS_DB_BASE);
		}
		$row = array(
				'title' => $question['title'], 
				'description' => substr($question['question'], 0, 200),
				'user_id' => $id, 
				'expert_id' => $question['exp_id'],
				'category_id' => $question['category_id'],
				'cost_id' => $question['cost_id'],
				'date_created' => date("Y-m-d")
				);
		if (!$db->query('INSERT INTO experts_topic(?#) VALUES(?a)', array_keys($row), array_values($row)))
		{
			return hmExpertSystem::DatabaseError;
		}
		$topic_id = mysql_insert_id();
		if (!hmExpertSystem::CreateNewPost($question, $topic_id, &$db)) {
			return hmExpertSystem::DatabaseError;
		}
		return 777;
	}
	
	// конструктор
	public function __construct($database_descriptor = null) {
		if ($database_descriptor) {
			$this->db = $database_descriptor;
		} else {
			$this->db = DbSimple_Generic::connect("mysql://".SYS_DB_USER.":".SYS_DB_PASS."@".SYS_DB_HOST."/".SYS_DB_BASE);
			//$this->db->query("SET character_set_client = 'cp1251', character_set_connection = 'cp1251', character_set_results = 'cp1251'");
		}
	}
	
	// Загружает список вопросов
	public function loadUserQuestion($id)
	{
	/*echo "SELECT et.*, ep.date_created, ep.is_new, ep.from_expert_id, ex.first_name, ex.last_name FROM experts_topic et 
									left join experts_post ep on et.id = ep.topic_id  
									left join experts ex on et.expert_id = ex.id 
								  WHERE user_id = '".$id."'";*/
		return $this->db->select("SELECT et.*, (
										select from_expert_id 
											from experts_post 
											where topic_id = et.id 
											order by date_created desc, id desc 
											limit 0,1) from_expert_id,
											(
										select is_new 
											from experts_post 
											where topic_id = et.id
											order by date_created desc, id desc 
											limit 0,1) as post_is_new, ex.first_name, ex.last_name 
									FROM experts_topic et 
									left join experts ex on et.expert_id = ex.id 
									WHERE user_id = '".$id."' order by et.date_created desc, et.id desc");
	}
	
	public function UnCheckNewQuestion($user_id, $id)
	{
		$this->db->query("UPDATE experts_topic SET is_new = '0' WHERE user_id=? and id =?", $user_id, $id);
		//echo mysql_error(); exit;
	}
	
	public function UnCheckNewQuestionForExpert($exp_id, $id)
	{
		$this->db->query("UPDATE experts_topic SET is_new = '0' WHERE expert_id=? and id =?", $exp_id, $id);
		//echo mysql_error(); exit;
	}
	
	// Загружает список экспертов
	public function loadListExperts()
	{
		return $this->db->select("SELECT * FROM experts where active=1 order by rank");
	}
	
	// Загружает список неактивных экспертов
	public function loadListUnactiveExperts()
	{
		return $this->db->select("SELECT * FROM experts order by rank");
	}
	
	public function CreateNewPost($question, $topic_id, &$db)
	{		
		$row = array(
				'topic_id' => $topic_id, 
				'message' => $question['question'],
				'from_expert_id' => $question['exp_id'], 
				'date_created' => date("Y-m-d"),
				'report' => $question['report']
				);

		if (!$db) {
			$db = DbSimple_Generic::connect("mysql://".SYS_DB_USER.":".SYS_DB_PASS."@".SYS_DB_HOST."/".SYS_DB_BASE);
		}
		if (!$db->query('INSERT INTO experts_post(?#) VALUES(?a)', array_keys($row), array_values($row)))
		{
			return false;
		}	

		return true;
	}
	
	public function doLogin($login, $pass)
	{
		$row = $this->db->select("SELECT * FROM experts where login='".$login."' and password = '".$pass."'");
		
		if (count($row)) {
			return $row;
		} else {
			return hmExpertSystem::EnterError;
		}
	}
	
	public function loadExpertQuestion($id, $order, $where = '')
	{
		return $this->db->select("SELECT et.*, DATE_FORMAT(et.date_created,'%d.%m.%Y') as date, u.user_login, u.user_name, (
										select from_expert_id 
											from experts_post 
											where topic_id = et.id 
											order by date_created desc, id desc 
											limit 0,1) from_expert_id,
											(
										select is_new 
											from experts_post 
											where topic_id = et.id 
											order by date_created desc, id desc 
											limit 0,1) post_is_new
									FROM experts_topic et 
									left join users u on u.user_id = et.user_id
									WHERE expert_id = '".$id."' ".$where."
									order by ".$order.", is_new desc
									");
		/*return $this->db->select("SELECT et.*, ep.date_created, ep.is_new, ep.from_expert_id, u.user_login, u.user_name FROM experts_topic et 
									left join experts_post ep on et.id = ep.topic_id  
									left join users u on u.user_id = et.user_id 
								  WHERE expert_id = ?", $id);*/
	}
	
	public function loadExpertQuestionId($exp_id = false, $user_id = false, $id)
	{
		if (!empty($exp_id))
		{
			$where = "and et.expert_id = '".$exp_id."'";
			$this->db->query("update experts_post set is_new='0' where from_expert_id=? and topic_id=?", $exp_id, $id);
		}else{			
			$where = "and et.user_id = '".$user_id."'";
			//$exp_id = $this->db->selectRow("select expert_id from experts_topic where id=?", $id);
			$this->db->query("update experts_post set is_new='0' where from_expert_id=? and topic_id=?", 0, $id);
		}
		return $this->db->select("SELECT ep.*, et.title, et.user_id FROM experts_post ep 
									left join experts_topic et on ep.topic_id = et.id
									where topic_id = '".$id."' ".$where."
									order by ep.date_created, ep.id");
	}
	
	public function CheckExistsByVoice($q_id)
	{
		$rows = $this->db->selectRow("SELECT topic_id FROM experts_rank WHERE topic_id = ?", $q_id);
		
		if (count($rows) > 0) {
			return false;
		}
		return true;
	}
	
	public function VoiceRank($id, $exp_id, $q_id, $user_id)
	{
		
		if (!$this->CheckExistsByVoice($q_id))
		{
			return false;
		}
		
		$row = $this->db->select("SELECT * FROM experts where id='".$exp_id."'");
		if ($id == 1)
		{
			$voice_up = 1;
			$voice_down = 0;
			$voice = $row[0]['voice_up']+1;
			$rank = ($row[0]['voice_up']+1)-$row[0]['voice_down'];
			$set = "voice_up = '".$voice."', rank = '".$rank."'";
		}else{
			$voice_up = 0;
			$voice_down = 1;
			$voice = $row[0]['voice_down']+1;
			$rank = $row[0]['voice_up']-($row[0]['voice_down']+1);
			$set = "voice_down = '".$voice."', rank = '".$rank."'";
		}
		$row = array(
				'user_id' => $user_id, 
				'expert_id' => $exp_id,
				'voice_up' => $voice_up,
				'voice_down' => $voice_down, 
				'topic_id' => $q_id
				);
		$this->db->query('INSERT INTO experts_rank(?#) VALUES(?a)', array_keys($row), array_values($row));	

		$this->db->query("UPDATE experts SET ".$set." WHERE id=? ", $exp_id);
		
		return true;
	}
	
	public function getExpertProfile($exp_id)
	{
		$profile = $this->db->select("SELECT * FROM experts where id = ?", $exp_id);
		
		$this->system_expert_costs = $this->db->select("SELECT * FROM system_experts_cost");
		
		$this->expert_costs = $this->db->select("SELECT * FROM experts_cost where expert_id = ?", $exp_id);
		
		$this->system_expert_categories = $this->db->select("SELECT * FROM system_experts_categories");
		
		$this->expert_categories = $this->db->select("SELECT * FROM experts_categories where expert_id = ?", $exp_id);
		
		return $profile;		
	}
	
	public function saveExpertProfile($exp_id, $data, $data_cost, $data_cat, $file)
	{
		if (!empty($file)) {
			if (!$photo = hmExpertSystem::UploadPhoto($file, $_SESSION['expert']['login'])) {
				return hmExpertSystem::PhotoError;
			}
		}
		
		$set = "email = '".$data['mail']."', 
				first_name = '".$data['f_name']."',
				last_name = '".$data['l_name']."',
				middle_name = '".$data['m_name']."',
				photo = '".$photo."',
				about = '".$data['about']."'";

		if (isset($data['pass']) && $data['pass'] != '')
		{
			$set .= ", password = '".$data['pass']."'";
		}		
		$this->db->query("UPDATE experts SET ".$set." WHERE id=? ", $exp_id);
		

		$this->db->query("delete from experts_cost WHERE expert_id=? ", $exp_id);

		for ($i=0; $i<count($data_cost); $i++)
		{
			$row = array(
				'cost_id' => $data_cost[$i]['cost_id'], 
				'expert_id' => $exp_id, 
				'price' => $data_cost[$i]['price'],
				'desc' => $data_cost[$i]['desc']
				);
			$this->db->query('INSERT INTO experts_cost(?#) VALUES(?a)', array_keys($row), array_values($row));
		}
		
		$this->db->query("delete from experts_categories WHERE expert_id=? ", $exp_id);
		for ($i=0; $i<count($data_cat); $i++)
		{
			$row = array(
				'category_id' => $data_cat[$i]['category_id'], 
				'expert_id' => $exp_id
				);
			$this->db->query('INSERT INTO experts_categories(?#) VALUES(?a)', array_keys($row), array_values($row));
		}
		return true;
	}
	
	public function deleteExpertPhoto($exp_id)
	{
		$photo = $this->db->selectRow("select photo from experts where id =?", $exp_id);
		
		if (is_file(UPLOAD_DIR . $photo['photo']))
		{
			unlink(UPLOAD_DIR . $photo['photo']);			
		}
		
		if ($this->db->query("update experts set `photo` = '' where id=?", $exp_id))
		{
			return true;
		}

		return false;
	}
	
	public function deleteExpertFile($id)
	{
		if (is_file(FILE_DIR . $id))
		{
			unlink(FILE_DIR . $id);			
		}
		if ($this->db->query("delete from experts_attach_content  where `file_name` =? and expert_id=?", $id, $_SESSION['expert']['id']))
		{
			return true;
		}
	}
	
	public function deleteExpertArticles($id)
	{
		if ($this->db->query("delete from experts_attach_content  where `id` =? and expert_id=?", $id, $_SESSION['expert']['id']))
		{
			return true;
		}
	}
	
	public function getExpertAttachContent($exp_id)
	{
		return $this->db->query("select * from experts_attach_content where expert_id =?", $exp_id);
	}
	
	public function saveExpertAttachContent($exp_id, $file, $url)
	{
		$row = array(
				'file_name' => $file['file_name'],
				'about_file' => $file['file_about'],
				'expert_id' => $exp_id, 
				'url_article' => $url
				);
		if ($this->db->query('INSERT INTO experts_attach_content(?#) VALUES(?a)', array_keys($row), array_values($row)))
		{
			return true;
		}
		return false;
	}
	
	public function unblockExpert($exp_id, $is_active)
	{
		if ($this->db->query("update experts set `active` = '".$is_active."' where `id`=?", $exp_id))
		{
			return true;
		}
		return false;
	}
	
	public function getSystemExpertCategories()
	{
		return $this->db->select("SELECT * FROM system_experts_categories");
	}
	
	public function saveSystemExpertCategories($id, $name, $type)
	{
		if ($type == "insert")
		{
			$row = array(
				'category_name' => $name
				);
			if ($this->db->query('INSERT INTO system_experts_categories(?#) VALUES(?a)', array_keys($row), array_values($row)))
			{
				return true;
			}
		}else{
			if ($this->db->query("update system_experts_categories set `category_name` = '".$name."' where `category_id`=?", $id))
			{
				return true;
			}
		}
		return false;
	}
	
	public function deleteSystemExpertCategories($id)
	{
		if ($this->db->query("delete from system_experts_categories where category_id=?", $id))
		{
			return true;
		}
		return false;
	}
	
	public function getSystemExpertCost()
	{
		return $this->db->select("SELECT * FROM system_experts_cost");
	}
	
	public function saveSystemExpertCost($id, $name, $type)
	{
		if ($type == "insert")
		{
			$row = array(
				'cost_name' => $name
				);
			if ($this->db->query('INSERT INTO system_experts_cost(?#) VALUES(?a)', array_keys($row), array_values($row)))
			{
				return true;
			}
		}else{
			if ($this->db->query("update system_experts_cost set `cost_name` = '".$name."' where `cost_id`=?", $id))
			{
				return true;
			}
		}
		return false;
	}
	
	public function deleteSystemExpertCost($id)
	{
		if ($this->db->query("delete from system_experts_cost where cost_id=?", $id))
		{
			return true;
		}
		return false;
	}
	
	public function getListReview($exp_id)
	{
		return $this->db->select("SELECT * FROM experts_review where expert_id=? order by review_date", $exp_id); 
	}
	
	public function saveExpertReview($exp_id, $user_id, $review)
	{
		$row = array(
				'expert_id' => $exp_id,
				'user_id' => $user_id,
				'review' => $review,
				'review_date' => date("Y-m-d")
				);
		if ($this->db->query('INSERT INTO experts_review(?#) VALUES(?a)', array_keys($row), array_values($row)))
		{
			return true;
		}
	}
	
	public function checkExpertQuestionInWork($id, $exp_id)
	{
		$row = $this->db->select("select id from experts_post where topic_id=? and from_expert_id=?", $id, 0);
		if (count($row))
		{
			return true;
		}
		return false;
	}
}

?>