<?
/**
* file: news.class.php
* author: Roman Korostov
* date: 8/09/07	
**/

class News  
{
	var $db             = false;
    var $user           = false;
	var $user_id        = 0;
	
	function News(&$db, &$user)
	{
		if (is_object($db) && is_a($db,'sql_db') && is_object($user) && is_a($user,'User')) {
			$this->db = $db;
			$this->user = $user;
			$this->user_id = $user->getId();
			
			return true;                
		}
		else {
			message_error(GENERAL_ERROR, 'Ошибка в загрузке новостей!', '', __LINE__, __FILE__);
			return false;
		}
	}
	
	function getNews()
	{
		$date_now = date('Y.m.d');
		
		$sql = "
				select *, nb.`bank_name`
				from `news` n
				left join `news_bank` nb on nb.`bank_id` = n.`bank_id`
				order by n.`news_date` desc,  n.`news_id` DESC
				limit 0, 50
		";
		//echo $sql;
		if ( !($result = $this->db->sql_query($sql)) )
		{
			message_error(GENERAL_ERROR, 'Ошибка в получении новостей!', '', __LINE__, __FILE__, $sql);
		}
		else
		{
			$row = $this->db->sql_fetchrowset($result);	
	
			return $row;
		}
	}
	
	function getNewsId($id)
	{		
		$sql = "select * from `news` n where news_id='".$id."'";
		//echo $sql;
		if ( !($result = $this->db->sql_query($sql)) )
		{
			message_error(GENERAL_ERROR, 'Ошибка в получении новостей!', '', __LINE__, __FILE__, $sql);
		}
		else
		{
			$row = $this->db->sql_fetchrow($result);
	
			return $row;
		}
	}
	
	function getTotalNews($start, $finish, $news_date)
	{		
		$sql = "
				select n.*, DATE_FORMAT( n.`news_date`,'%d.%m.%Y') as news_date 
				from `news` n
				where n.`news_date` = '".$news_date."'
				order by n.`news_date`,  n.`news_id` DESC
				limit ".$start.", ".$finish."
		";
		//echo $sql;
		if ( !($result = $this->db->sql_query($sql)) )
		{
			message_error(GENERAL_ERROR, 'Ошибка в получении новостей!', '', __LINE__, __FILE__, $sql);
		}
		else
		{
			$row = $this->db->sql_fetchrowset($result);	

			return $row;
		}
	}
	
	function getCountNews($news_date)
	{
		$sql = "
				select count(n.news_id) as cnt
				from `news` n
				where n.`news_date` = '".$news_date."'
		";
		//echo $sql;
		if ( !($result = $this->db->sql_query($sql)) )
		{
			message_error(GENERAL_ERROR, 'Ошибка в получении количества новостей!', '', __LINE__, __FILE__, $sql);
		}
		else
		{
			$row = $this->db->sql_fetchrow($result);	

			return $row;
		}
	}
	
	function getTitleNews($limit, $news_date)
	{		
		$sql = "
				select *, nb.`bank_name`
				from `news` n
				left join `news_bank` nb on nb.`bank_id` = n.`bank_id`
				where n.`news_date` = '".$news_date."'
				order by n.`news_date`,  n.`news_id` DESC
				limit 0, ".$limit."
		";
		//echo $sql;
		if ( !($result = $this->db->sql_query($sql)) )
		{
			message_error(GENERAL_ERROR, 'Ошибка в получении новостей!', '', __LINE__, __FILE__, $sql);
		}
		else
		{
			$row = $this->db->sql_fetchrowset($result);	

			return $row;
		}
	}
	
	function getBanks()
	{
		$date_now = date('Y.m.d');
		
		$sql = "
				select *
				from `news_bank` b where b.bank_visible = 1
				order by b.`bank_name`
		";
		//echo $sql;
		if ( !($result = $this->db->sql_query($sql)) )
		{
			message_error(GENERAL_ERROR, 'Ошибка в получении банков!', '', __LINE__, __FILE__, $sql);
		}
		else
		{
			$row = $this->db->sql_fetchrowset($result);	
	
			return $row;
		}
	}
	
	function saveNews($data)
	{
		$user_id = $this->user_id;
		
		$sql = "INSERT INTO `news` 
					(`news_id`, `news_title`, `news_short`, `news_body`, `news_visible`, `news_archive`, `news_date`, `bank_id`)
				VALUES
					('', '".$data['news_title']."', '".$data['news_short']."', '".$data['news_body']."', '".$data['news_visible']."', '0', '".$data['news_date']."', '".$data['bank_id']."')
				";

		if ( !($result = $this->db->sql_query($sql)) )
		{
			message_error(GENERAL_ERROR, 'Ошибка в cохранении новости!', '', __LINE__, __FILE__, $sql);
		}
		else
		{	
			return true;
		}
	}
	
	function selectNews($id)
	{		
		$sql = "
				select n.`news_id`,
				       DATE_FORMAT( n.`news_date`,'%d.%m.%Y') as news_date,
				       n.`news_title`,
				       n.`news_short`,
				       n.`news_body`,
				       n.`news_visible`,
				       n.`news_archive`,
				       n.`bank_id`
				from `news` n
				where n.`news_id` = ".$id
		;
		
		if ( !($result = $this->db->sql_query($sql)) )
		{
			message_error(GENERAL_ERROR, 'Ошибка в получении новости!', '', __LINE__, __FILE__, $sql);
		}
		else
		{
			$row = $this->db->sql_fetchrowset($result);			
			return $row;
		}
	}
	
	function updateNews($data)
	{
		
		$sql = "UPDATE `news` SET
					`bank_id` = '".$data['bank_id']."', `news_date` = '".$data['news_date']."' , 
					`news_title` = '".$data['news_title']."',`news_short` = '".$data['news_short']."' , 
					`news_body` = '".$data['news_body']."',`news_visible` = '".$data['news_visible']."', 
					`news_archive` = '".$data['news_archive']."'
				WHERE `news_id` = '".$data['news_id']."'
				";
			
		if ( !($result = $this->db->sql_query($sql)) )
		{
			message_error(GENERAL_ERROR, 'Ошибка в изменении бюджета!', '', __LINE__, __FILE__, $sql);
		}
		else
		{			
			return true;
		}
	}
	
	function deleteNews($id)
	{
		$user_id = $this->user_id;

		$sql = "DELETE FROM `news` WHERE `news_id` = '".$id."'";
		
		if ( !($result = $this->db->sql_query($sql)) )
		{
			message_error(GENERAL_ERROR, 'Ошибка в удалении новости!', '', __LINE__, __FILE__, $sql);
		}
		else
		{		
			return true;
		}
	}
}

?>