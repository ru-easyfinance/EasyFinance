<?
/**
* file: report.class.php
* author: Roman Korostov
* date: 26/01/07	
**/

class Report  
{
	var $db             = false;
    var $user           = false;
	var $user_id        = 0;
	
	function Report(&$db, &$user)
	{
		if (is_object($db) && is_a($db,'sql_db') && is_object($user) && is_a($user,'User')) {
			$this->db = $db;
			$this->user = $user;
			$this->user_id = $user->getId();

			return true;                
		}
		else {
			message_error(GENERAL_ERROR, 'Ошибка в загрузке объектов!', '', __LINE__, __FILE__);
			return false;
		}
	}
	
	function getReportCat($filter, $group_account)
	{
		if ($group_account == 'on'){
			$groupby_group_account = ', c.`cur_name`';
			$select_group_account = "'Общий счет' as `bill_name`, ";
			$orderby_group_account = '';
		}else{
			$groupby_group_account = ', b.`bill_id`';
			$select_group_account = 'b.`bill_name`, ';
			$orderby_group_account = 'b.`bill_name`, ';
		}
		$sql = "
				SELECT sum(m.`money`) as sum,
					   c.`cur_name` as currency_name,
					   c.`cur_id`,
					   ".$select_group_account."
					   b.`bill_id`,
					   m.`transfer`,
					   m.`drain`,
					   ca.`cat_id`,
					   ca.`cat_name`,
					   ca.`cat_parent`,
					   b2.`bill_name` as `to_account`,
					   DATE_FORMAT(m.date, '%m.%Y') as date_for
				FROM `bill` b
					 LEFT JOIN `money` m on m.`bill_id` = b.`bill_id` and m.`user_id` = '".$this->user_id."'
					 LEFT JOIN `currency` c on c.`cur_id` = b.`bill_currency`
					 LEFT JOIN `category` ca on (ca.`cat_id` = m.`cat_id`) and
					 			ca.`user_id` = '".$this->user_id."'
					 LEFT JOIN `bill` b2 on b2.`bill_id` = m.`transfer`
				WHERE b.`user_id` = '".$this->user_id."' 
					  and m.`money` <> '0'
					  ".$filter."
				GROUP BY ca.`cat_id`,
						 date_for
						 ".$groupby_group_account."
				ORDER By ".$orderby_group_account." m.date DESC
			   ";
		//echo $sql;
		// or ca.`cat_parent` = m.`cat_id`
		if ( !($result = $this->db->sql_query($sql)) )
		{
			message_error(GENERAL_ERROR, 'Ошибка в получении отчета!', '', __LINE__, __FILE__, $sql);
		}
		else
		{
			$row = $this->db->sql_fetchrowset($result);
			return $row;
		}	
	}
	
	function getReportTotalSumm($filter)
	{
		/*$sql = "
				select sum(m.`money`) as summ,
					   m.`drain`,
					   DATE_FORMAT(m.date, '%m.%Y') as date_for,
					   m.`bill_id`
				from `money` m
				where m.`user_id` = '".$this->user_id."'
					  and m.`money` <> '0'
					  ".$filter."
				group by date_for, m.`bill_id`
			   ";*/

$sql = "
				select sum(m.`money`) as summ,
					   m.`drain`,
					   DATE_FORMAT(m.date, '%m.%Y') as date_for,
					   m.`bill_id`,
					   b.`bill_currency`,
				       c.`cur_name`
				from `money` m
				  left join `bill` b on b.`bill_id` = m.`bill_id`
				  left join `currency` c on c.`cur_id` = b.`bill_currency`
				where m.`user_id` = '".$this->user_id."'
					  and m.`money` <> '0'
					  ".$filter."
				group by date_for, m.`bill_id`
			   ";

		if ( !($result = $this->db->sql_query($sql)) )
		{
			message_error(GENERAL_ERROR, 'Ошибка в получении отчета!', '', __LINE__, __FILE__, $sql);
		}
		else
		{
			$row = $this->db->sql_fetchrowset($result);
			return $row;
		}
	}
	
	function getOutCameForPeriod($filter)
	{
		$sql = "SELECT bill.bill_name,
				       category.cat_name,
				       sum(money.money) AS total_sum,
				       currency.cur_name,
				       category.cat_id,
				       category.cat_parent,
				       c2.cat_id as parent_cat_id,
				       c2.cat_name as parent_cat_name
				FROM money
				     JOIN category ON category.cat_id = money.cat_id
				     JOIN bill ON money.bill_id = bill.bill_id
				     JOIN currency ON bill.bill_currency = currency.cur_id
				     JOIN users ON bill.user_id = users.user_id
				     LEFT JOIN category c2 ON category.cat_parent = c2.cat_id
				WHERE users.user_id = '".$this->user_id."'
				      AND money.drain = ".$filter['drain']."
				      AND money.date >= str_to_date('".$filter['date_from']."', '%d.%m.%Y')
				      AND money.date < str_to_date('".$filter['date_to']."', '%d.%m.%Y')
				GROUP BY category.cat_name,
				         bill.bill_id
				ORDER BY bill.bill_name,
				         category.cat_name";
		
		$sql = "SELECT bill.bill_name,
				       category.cat_name,
				       sum(money.money) AS total_sum,
				       currency.cur_name,
				       category.cat_id,
				       category.cat_parent,
				       c2.cat_id AS parent_cat_id,
				       CASE
				         WHEN category.cat_parent = 0 THEN category.cat_name ELSE c2.cat_name
				       END AS parent_cat_name
				FROM money
				     JOIN category ON category.cat_id = money.cat_id
				     JOIN bill ON money.bill_id = bill.bill_id
				     JOIN currency ON bill.bill_currency = currency.cur_id
				     JOIN users ON bill.user_id = users.user_id
				     LEFT JOIN category c2 ON category.cat_parent = c2.cat_id
				WHERE users.user_id = '".$this->user_id."'
				      AND money.drain = ".$filter['drain']."
				      AND money.date >= str_to_date('".$filter['date_from']."', '%d.%m.%Y')
				      AND money.date <= str_to_date('".$filter['date_to']."', '%d.%m.%Y')
				GROUP BY category.cat_name,
				         bill.bill_id
				ORDER BY bill.bill_name,
				         parent_cat_name, money.date desc";
		//echo $sql;
		if ( !($result = $this->db->sql_query($sql)) )
		{
			message_error(GENERAL_ERROR, 'Ошибка в получении отчета!', '', __LINE__, __FILE__, $sql);
		}
		else
		{
			$row = $this->db->sql_fetchrowset($result);
			return $row;
		}
	}
	
	function getWithoutBillForPeriod($filter)
	{
		if (!empty($filter['cat_id']))
		{
			$cat_id = " AND money.cat_id = ".$filter['cat_id'];
		}
		if (!empty($filter['bill_id']))
		{
			$bill_id = " AND money.bill_id = ".$filter['bill_id'];
		}
		
		$sql = "SELECT category.cat_name,
				       sum(money.money) AS total_sum,
				       currency.cur_name,
				       currency.cur_id,
				       category.cat_id ,
				       category.cat_parent,
				       c2.cat_id AS parent_cat_id,
				       CASE
				         WHEN category.cat_parent = 0 THEN category.cat_name
				         ELSE c2.cat_name
				       END AS parent_cat_name
				FROM money 
				     JOIN category ON category.cat_id = money.cat_id
				     JOIN bill ON money.bill_id = bill.bill_id
				     JOIN currency ON bill.bill_currency = currency.cur_id
				     JOIN users ON bill.user_id = users.user_id
				     LEFT JOIN category c2 ON category.cat_parent = c2.cat_id
				WHERE users.user_id = '".$this->user_id."' AND
				      money.drain = ".$filter['drain']." AND
				      money.date >= str_to_date('".$filter['date_from']."', '%d.%m.%Y') AND 
				      money.date <= str_to_date('".$filter['date_to']."', '%d.%m.%Y')
				      ".$cat_id."
				      ".$bill_id."
				GROUP BY category.cat_name,
				         currency.cur_id
				ORDER BY parent_cat_name,
				         category.cat_name,
				         currency.cur_name, money.date desc";
		//echo "<!-- $sql -->";
		if ( !($result = $this->db->sql_query($sql)) )
		{
			message_error(GENERAL_ERROR, 'Ошибка в получении отчета!', '', __LINE__, __FILE__, $sql);
		}
		else
		{
			$row = $this->db->sql_fetchrowset($result);
			return $row;
		}
	}
	
	function getOutCameGroupedMonth($filter)
	{
		$sql = "SELECT category.cat_name,
				       sum(money.money) AS total_sum,
				       date_format(money.date, '%m.%Y') AS date_new,
				       currency.cur_name,
				       currency.cur_id,
				       category.cat_id,
				       category.cat_parent,
				       c2.cat_id AS parent_cat_id,
				       CASE
				         WHEN category.cat_parent = 0 THEN category.cat_name
				         ELSE c2.cat_name
				       END AS parent_cat_name
				FROM money
				     JOIN category ON category.cat_id = money.cat_id
				     JOIN bill ON money.bill_id = bill.bill_id
				     JOIN currency ON bill.bill_currency = currency.cur_id
				     JOIN users ON bill.user_id = users.user_id
				     LEFT JOIN category c2 ON category.cat_parent = c2.cat_id
				WHERE users.user_id = '".$this->user_id."' AND
				      money.drain = ".$filter['drain']." AND
				      money.date >= str_to_date('".$filter['date_from']."', '%d.%m.%Y') AND
				      money.date <= str_to_date('".$filter['date_to']."', '%d.%m.%Y')
				GROUP BY category.cat_name,
				         currency.cur_id ,
				         date_new
				ORDER BY money.date desc,
						 parent_cat_name,
				         category.cat_name,				         
				         currency.cur_name";
		
		if ( !($result = $this->db->sql_query($sql)) )
		{
			message_error(GENERAL_ERROR, 'Ошибка в получении отчета!', '', __LINE__, __FILE__, $sql);
		}
		else
		{
			$row = $this->db->sql_fetchrowset($result);
			return $row;
		}
	}
}