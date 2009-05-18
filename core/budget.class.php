<?
/**
 * file: account.class.php
 * author: Roman Korostov
 * date: 13/03/07
 **/

class Budget
{
    var $db = null;
    var $user = null;
    var $user_id = 0;
    var $cat_id = 0;
    var $account_total_sum = 0;

    /**
     * Конструктор
     * @param DbSimple_Mysql $db
     * @param User $user
     * @return bool
     */
    function __construct(DbSimple_Mysql $db, User $user)
    {
        $this->db = $db;
        $this->user = $user;
        $this->user_id = $user->getId();

        return true;
    }

    function getBudgets($filter, $month, $year)
    {
        $date_now = date('Y.m.d');

        $sql2 = "
				select b.`id`,
				       DATE_FORMAT( b.`date_from`,'%d.%m.%Y') as date_from,
				       DATE_FORMAT( b.`date_to`,'%d.%m.%Y') as date_to,
				       b.`money`,
				       b.`bill_id`,
				       b.`comment`,
				       b.`cat_id`,
				       c.`cat_name`,
				       acc.`bill_name`,
				       sum(m.`money`) as fact
				from `budget` b
				     left join `category` c on c.`cat_id` = b.`cat_id`
				     left join `bill` acc on acc.`bill_id` = b.`bill_id`
				     left join `money` m on m.`bill_id` = b.`bill_id` and m.`cat_id` =b.`cat_id` and 
					 			m.`date` < '".$date_now ."' and m.`date` > '".$date_now ."'
				where b.`date_from` < '".$date_now ."' and
				      b.`date_to` > '".$date_now ."' and
				      b.`user_id` = '".$this->user_id."' and
				      b.`drain` = '".$filter['drain']."'
				      ".$where_id."
				group by b.`bill_id`
				order by acc.`bill_name`, c.`cat_name`
		";

        $sql3 = "
				select b.`id`,
				       DATE_FORMAT( b.`date_from`,'%d.%m.%Y') as date_from,
				       DATE_FORMAT( b.`date_to`,'%d.%m.%Y') as date_to,
				       b.`money`,
				       b.`bill_id`,
				       b.`comment`,
				       c.`cat_name`,
				       acc.`bill_name`,
				       (select sum(m.`money`) from `money` m where m.`bill_id` = b.`bill_id`
				       and m.`cat_id` = b.`cat_id` and m.`date` >= '2007.09.01' and 
					   m.`date` <= '2007.12.31') as fact
				from `budget` b,
				     `category` c,
				     `bill` acc
				where c.`cat_id` = b.`cat_id` and
				      acc.`bill_id` = b.`bill_id` and
				      b.`date_from` <= '2007.12.31' and
      				  b.`date_to` >= '2007.09.01' and
				      b.`user_id` = '".$this->user_id."' and
				      b.`drain` = '".$filter['drain']."'
				order by acc.`bill_name`,  c.`cat_name`
		";

        $m = date("Y.m",mktime(0, 0, 0, $month+1, date("d"), $year));

        $mn = $year.".".$month;

        $_SESSION['budgetCurMonth'] = $mn;
        $_SESSION['budgetNextMonth'] = $m;

        $sql = "
				select b.`id`,
				c.`cat_parent`,
				       DATE_FORMAT( b.`date_from`,'%d.%m.%Y') as date_from,
				       DATE_FORMAT( b.`date_to`,'%d.%m.%Y') as date_to,
				       b.`money`,
						b.`drain`,
				       b.`bill_id`,
				       b.`comment`,
				       c.`cat_name`,
				       (select sum(m.`money`) from `money` m where m.`cat_id` = b.`cat_id` and m.`date` >= '".$mn.".01' and 
					   m.`date` < '".$m.".01') as fact,
					   (select cc.`cat_name` from `category` cc where cc.`cat_id` = c.`cat_parent`) as parent_name
				from `budget` b,
				     `category` c
				where c.`cat_id` = b.`cat_id` and				      
				      b.`date_from` < '".$m.".01' and
      				  b.`date_to` > '".$mn.".01' and
				      b.`user_id` = '".$this->user_id."' and
				      b.`drain` = '".$filter['drain']."'
				order by c.`cat_name`
		";

        if ( !($result = $this->db->sql_query($sql)) )
        {
            message_error(GENERAL_ERROR, 'Ошибка в получении бюджета!', '', __LINE__, __FILE__, $sql);
        }
        else
        {
            $row = $this->db->sql_fetchrowset($result);
            if ($_GET['et'] == 'on')
            {
                echo $sql;
                pre($row);
            }
            for ($i=0; $i<count($row); $i++)
            {
                if (!empty($row[$i]['fact']))
                {
                    $pos = strpos($row[$i]['fact'], "-");
                    if ($pos !== false)
                    {
                        $row[$i]['fact'] = substr($row[$i]['fact'], 1);
                    }
                }else{
                    $row[$i]['fact']='0';
                }
            }

            return $row;
        }
    }

    function checkBudget($user_id, $month, $year)
    {
        $m = date("Y.m",mktime(0, 0, 0, $month, date("d"), $year));

        $mn = date("Y.m",mktime(0, 0, 0, $month-1, date("d"), $year));

        $sql = "select * from budget where user_id = '".$user_id."' and  `date_to` >= '".$mn.".01' and
					   `date_from` < '".$m.".01'";

        if ( !($result = $this->db->sql_query($sql)) )
        {
            message_error(GENERAL_ERROR, 'Ошибка в получении бюджета!', '', __LINE__, __FILE__, $sql);
        }else{
            $row = $this->db->sql_fetchrowset($result);
        }

        return $row;
    }

    function copyBudget($user_id, $ml, $mn, $month, $year)
    {
        if ($ml == '12')
        {
            $year_l = $year - 1;
        }else{
            $year_l = $year;
        }
        $sql = "select * from budget where user_id = '".$user_id."' and  `date_to` >= '".$year_l.".".$ml.".01' and
					   `date_from` < '".$year.".".$month.".01'";

        if ( !($result = $this->db->sql_query($sql)) )
        {
            message_error(GENERAL_ERROR, 'Ошибка в получении бюджета!', '', __LINE__, __FILE__, $sql);
        }else{
            $row = $this->db->sql_fetchrowset($result);
        }

        for ($i = 0; $i < count($row); $i++)
        {
            $sql = "INSERT INTO `budget`
						(`id`, `user_id`, `bill_id`, `cat_id`, `money`, `drain`, `date_from`, `date_to`, `comment`)
					VALUES
						('', '".$user_id."', '0', '".$row[$i]['cat_id']."', '".$row[$i]['money']."', '".$row[$i]['drain']."', '".$year.".".$month.".01', '".$year.".".$month.".31', '".$row[$i]['comment']."')
					";		

            if ( !($result = $this->db->sql_query($sql)) )
            {
                message_error(GENERAL_ERROR, 'Ошибка в cохранении бюджета!', '', __LINE__, __FILE__, $sql);
            }
        }


        header("Location: index.php?modules=budget");
    }

    function selectBudget($id)
    {
        $sql = "
				select b.`id`,
				       DATE_FORMAT( b.`date_from`,'%d.%m.%Y') as date_from,
				       DATE_FORMAT( b.`date_to`,'%d.%m.%Y') as date_to,
				       b.`money`,
				       b.`bill_id`,
				       b.`comment`,
				       b.`cat_id`,
				       b.`drain`,
				       c.`cat_name`,
				       acc.`bill_name`
				from `budget` b
				     left join `category` c on c.`cat_id` = b.`cat_id`
				     left join `bill` acc on acc.`bill_id` = b.`bill_id`
				where b.`id` = ".$id
        ;

        if ( !($result = $this->db->sql_query($sql)) )
        {
            message_error(GENERAL_ERROR, 'Ошибка в получении бюджета!', '', __LINE__, __FILE__, $sql);
        }
        else
        {
            $row = $this->db->sql_fetchrowset($result);
            return $row;
        }
    }

    function saveBudget($data)
    {
        $user_id = $this->user_id;

        $sql = "select * from budget where cat_id = '".$data['cat_id']."' and user_id = '".$user_id."'";
        $result = $this->db->sql_query($sql);
        $row = $this->db->sql_fetchrow($result);

        if (!empty($row))
        {
            $row['money'] = $row['money'] + $data['money'];
            $row['date_to'] = $data['date_to'];
            $row['date_from'] = $data['date_from'];

            if ($this->updateBudget($row))
            {
                return true;
            }
        }else{

            $sql = "INSERT INTO `budget`
					(`id`, `user_id`, `bill_id`, `cat_id`, `money`, `drain`, `date_from`, `date_to`, `comment`)
				VALUES
					('', '".$user_id."', '".$data['bill_id']."', '".$data['cat_id']."', '".$data['money']."', '".$data['drain']."', '".$data['date_from']."', '".$data['date_to']."', '".$data['comment']."')
				";

            if ( !($result = $this->db->sql_query($sql)) )
            {
                message_error(GENERAL_ERROR, 'Ошибка в cохранении бюджета!', '', __LINE__, __FILE__, $sql);
            }
            else
            {
                return true;
            }
        }
    }

    function updateBudget($data)
    {
        $user_id = $this->user_id;

        $sql = "UPDATE `budget` SET
					`bill_id` = '".$data['bill_id']."', `cat_id` = '".$data['cat_id']."' , `money` = '".$data['money']."',
					`drain` = '".$data['drain']."' , `date_from` = '".$data['date_from']."',
					`date_to` = '".$data['date_to']."', `comment` = '".$data['comment']."'
				WHERE `id` = '".$data['id']."' AND `user_id` = '".$user_id."'
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

    function deleteBudget($id)
    {
        $user_id = $this->user_id;

        $sql = "DELETE FROM `budget` WHERE `id` = '".$id."' and `user_id` = '".$user_id."'";

        if ( !($result = $this->db->sql_query($sql)) )
        {
            message_error(GENERAL_ERROR, 'Ошибка в удалении бюджета!', '', __LINE__, __FILE__, $sql);
        }
        else
        {
            return true;
        }
    }

    function saveMoney($money, $convert, $date, $from_account, $to_account)
    {
        $user_id = $this->user_id;
        $tr_id = md5($user_id."+".date("d-m-Y H-i-s"));
        $drain_money = "-$money";

        $sql = "INSERT INTO `money`
					(`id`, `user_id`, `money`, `date`, `cat_id`, `bill_id`, `drain`, `transfer`, `tr_id`)
				VALUES
					('', '".$user_id."', '".$drain_money."', '".$date."', '-1', '".$from_account."', '1', '".$to_account."', '".$tr_id."')
				";

        if ( !($result = $this->db->sql_query($sql)) )
        {
            message_error(GENERAL_ERROR, 'Ошибка в cохранении финансов!', '', __LINE__, __FILE__, $sql);
        }
        else
        {
            $sql = "INSERT INTO `money`
						(`id`, `user_id`, `money`, `date`, `cat_id`, `bill_id`, `drain`, `transfer`, `tr_id`)
					VALUES
						('', '".$user_id."', '".$convert."', '".$date."', '-1', '".$to_account."', '0', '".$from_account."', '".$tr_id."')
					";
            if ( !($result = $this->db->sql_query($sql)) )
            {
                message_error(GENERAL_ERROR, 'Ошибка в cохранении финансов!', '', __LINE__, __FILE__, $sql);
            }
            else
            {
                $_SESSION['user_money'] = "reload";

                $this->user->initUserAccount($user_id);
                $this->user->save();

                return true;
            }
        }
    }

    function updateMoney($money, $convert, $date, $from_account, $to_account, $tr_id)
    {
        $user_id = $this->user_id;
        $drain_money = "-$money";

        $sql = "UPDATE `money` SET
						`money` = '".$drain_money."', `date` = '".$date."', `transfer` = '".$to_account."'
					WHERE `bill_id` = '".$from_account."' AND `user_id` = '".$user_id."' AND 
						  `drain` = '1' AND `tr_id` = '".$tr_id."'
					";

        if ( !($result = $this->db->sql_query($sql)) )
        {
            message_error(GENERAL_ERROR, 'Ошибка в cохранении финансов!', '', __LINE__, __FILE__, $sql);
        }
        else
        {
            $sql = "UPDATE `money` SET
						`money` = '".$convert."', `date` = '".$date."', `bill_id` = '".$to_account."'
					WHERE `transfer` = '".$from_account."' AND `user_id` = '".$user_id."' AND 
						  `drain` = '0' AND `tr_id` = '".$tr_id."'
					";
             
             
            if ( !($result = $this->db->sql_query($sql)) )
            {
                message_error(GENERAL_ERROR, 'Ошибка в cохранении финансов!', '', __LINE__, __FILE__, $sql);
            }
            else
            {
                $_SESSION['user_money'] = "reload";

                $this->user->initUserAccount($user_id);
                $this->user->save();

                return true;
            }
        }
    }

    function selectTransfer($id)
    {
        $sql = "
			   SELECT DATE_FORMAT(m.`date`,'%d.%m.%Y') as date, m.`drain`, m.`money`,m.`transfer`, 
			   		  m.`tr_id`, m2.`bill_id` as `to_account`, m2.`money` as `convert`, b.`bill_currency`
			   FROM `money` m
			 	LEFT JOIN `money` m2 on m2.`tr_id` = '".$id."'
			 			   AND m2.`drain` = '0' AND m2.`user_id` = '".$this->user_id."'
			 	LEFT JOIN `bill` b on b.`bill_id` = m2.`bill_id`
			   WHERE m.`tr_id` = '".$id."' AND
			  		 m.`drain` = '1' AND
			  		 m.`user_id` = '".$this->user_id."'
	  ";

        if ( !($result = $this->db->sql_query($sql)) )
        {
            message_error(GENERAL_ERROR, 'Ошибка в получении счета!', '', __LINE__, __FILE__, $sql);
        }
        else
        {
            $row = $this->db->sql_fetchrow($result);
            return $row;
        }
    }

    function deleteMoney($id)
    {
        $user_id = $this->user_id;

        $sql = "DELETE FROM `money` WHERE `tr_id` = '".$id."' and `user_id` = '".$user_id."'";

        if ( !($result = $this->db->sql_query($sql)) )
        {
            message_error(GENERAL_ERROR, 'Ошибка в удалении перечислений!', '', __LINE__, __FILE__, $sql);
        }
        else
        {
            $_SESSION['user_money'] = "reload";
            $this->user->initUserAccount($user_id);
            $this->user->save();

            return true;
        }
    }
}