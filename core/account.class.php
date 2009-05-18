<?
/**
 * file: account.class.php
 * author: Roman Korostov
 * date: 13/03/07
 **/

class Account
{
    private $db = null;
    var $user           = false;
    var $user_id        = 0;
    var $cat_id 		= 0;
    var $current_account_id = 0;
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

    function selectAccount($id)
    {
        $sql = "SELECT b.`bill_id` as `id`, b.`bill_name` as `name`, b.`bill_type` as `type`,
					   b.`user_id`, m.`money`, b.`bill_currency` as `currency`, c.`cur_name`
					FROM `bill` b
					LEFT JOIN `money` m on m.`bill_id` = b.`bill_id`
					LEFT JOIN `currency` c on b.`bill_currency` = c.`cur_id`
						WHERE b.`bill_id` = '".$id."' 
								AND b.`user_id` = '".$this->user_id."' ORDER BY b.`bill_name`";

        if ( !($result = $this->db->sql_query($sql)) )
        {
            message_error(GENERAL_ERROR, 'Ошибка в получении счета!', '', __LINE__, __FILE__, $sql);
        }
        else
        {
            $row = $this->db->sql_fetchrowset($result);
            	
            return $row;
        }
    }

    function selectAccountForEdit($id)
    {
        $sql = "SELECT b.`bill_id` as `id`, b.`bill_name` as `name`, b.`bill_type` as `type`,
					   b.`user_id`, m.`money`, b.`bill_currency` as `currency`, c.`cur_name`
					FROM `bill` b
					LEFT JOIN `money` m on m.`bill_id` = b.`bill_id`
					LEFT JOIN `currency` c on b.`bill_currency` = c.`cur_id`
						WHERE b.`bill_id` = '".$id."' 
								AND b.`user_id` = '".$this->user_id."' and m.cat_id = 0 ORDER BY b.`bill_name`";

        if ( !($result = $this->db->sql_query($sql)) )
        {
            message_error(GENERAL_ERROR, 'Ошибка в получении счета!', '', __LINE__, __FILE__, $sql);
        }
        else
        {
            $row = $this->db->sql_fetchrowset($result);
            	
            return $row;
        }
    }

    function getTotalSum($id)
    {
        $sql = "
				select SUM(`money`) as sum from money where `user_id` = '".$this->user_id."' and `bill_id` = '".$id."'
				";
        if ($result = $this->db->sql_query($sql))
        {
            $row = $this->db->sql_fetchrow($result);
            $this->total_sum = $row['sum'];
            return $row['sum'];
        }
    }

    function getAccountsList()
    {
        $sql = "SELECT * FROM bill left join account_deposit on bill_id = account_id where user_id = '".$this->user_id."' order by bill_type, bill_name";
        $result = $this->db->sql_query($sql);
        $row = $this->db->sql_fetchrowset($result);
        	
        return $row;
    }

    //получаем информацию по депозиту
    function getAboutDeposit($id)
    {
        $sql = "select *, DATE_FORMAT(open_date,'%d.%m.%Y') as open_date, DATE_FORMAT(close_date,'%d.%m.%Y') as close_date
				from `account_deposit` where `account_id` = '".$id."'";
        $result = $this->db->sql_query($sql);
        $row = $this->db->sql_fetchrow($result);
        	
        return $row;
    }

    function saveAccountDeposite($type, $name, $bank, $sum, $currency, $percent, $getpercent, $dateCreated, $from_account, $from_currency, $from_sum)
    {
        $this->saveAccount($type, $name, '0', $currency);

        $sql = "INSERT INTO `account_deposit`
					(`account_id`, `name_bank`, `percent`, `get_percent`, `open_date`)
				VALUES
					('".$this->current_account_id."', '".$bank."', '".$percent."', '".$getpercent."', '".$dateCreated."')
				";		
        $result = $this->db->sql_query($sql);
    }

    function getTest()
    {
        $sql = "SELECT *
  FROM (SELECT inc_money.user_id,
               u.user_name,
               u.user_login,
               inc_money.income,
               exp_money.outcome,
               (inc_money.income - exp_money.outcome) AS net_amount
          FROM       (SELECT m1.user_id, sum(m1.money) AS income
                        FROM money m1
                       WHERE m1.drain = 0
                         AND m1.date >= '2008-01-01'
                         AND m1.date < '2008-02-01'
                      GROUP BY m1.user_id) inc_money
                  JOIN
                     (SELECT m2.user_id, sum(m2.money) AS outcome
                        FROM money m2
                       WHERE m2.drain = 1
                         AND m2.date >= '2008-01-01'
                         AND m2.date < '2008-02-01'
                      GROUP BY m2.user_id) exp_money
                  ON inc_money.user_id = exp_money.user_id
               JOIN
                  users u
               ON u.user_id = inc_money.user_id) user_stat
ORDER BY user_stat.net_amount DESC";
        //DATE_SUB(curdate(), INTERVAL 1 MONTH)
        $result = $this->db->sql_query($sql);
        $row = $this->db->sql_fetchrowset($result);
        	
        return $row;
    }

    function getTotalSumAccount($id)
    {
        $sql = "select SUM(`money`) as sum from money where bill_id = '".$id."'";
        $result = $this->db->sql_query($sql);
        $row = $this->db->sql_fetchrow($result);
        return $row['sum'];
    }

    function saveAccount($type, $name, $money, $currency)
    {
        $user_id = $this->user_id;
        $date = "";
        $id = 0;

        $sql = "INSERT INTO `bill`
					(`bill_name`, `user_id`, `bill_type`, `bill_currency`)
				VALUES
					('".$name."', '".$user_id."', '".$type."', '".$currency."')
				";

        if ( !($result = $this->db->sql_query($sql)) )
        {
            message_error(GENERAL_ERROR, 'Ошибка в cохранении счета!', '', __LINE__, __FILE__, $sql);
        }
        else
        {
            $id = $this->db->sql_nextid();
            $this->current_account_id = $id;
            $pos = strpos($money, "-");
            $drain = 0;
            	
            if ($pos !== false)
            {
                $drain = 1;
            }
            	
            /*if($type == 3)
             {
             if ($pos === false)
             {
             $money = "-".$money;
             }
             $drain = 1;
             }*/

            $sql = "INSERT INTO `money`
					(`user_id`, `money`, `date`, `cat_id`, `bill_id`, `drain`, `comment`)
				VALUES
					('".$user_id."', '".$money."', NOW(), '0', '".$id."', '".$drain."', '')
				";
            if ( !($result = $this->db->sql_query($sql)) )
            {
                message_error(GENERAL_ERROR, 'Ошибка в cохранении начального капитала!', '', __LINE__, __FILE__, $sql);
            }
            else
            {
                $this->user->initUserAccount($user_id);
                $this->user->save();
                	
                return true;
            }
        }
    }

    function updateAccount($id, $type, $name, $money, $currency)
    {
        $user_id = $this->user_id;

        $sql = "UPDATE `bill` SET
					`bill_type` = '".$type."', `bill_name` = '".$name."' , `bill_currency` = '".$currency."'
				WHERE `bill_id` = '".$id."' AND `user_id` = '".$user_id."'
				";
        	
        if ( !($result = $this->db->sql_query($sql)) )
        {
            message_error(GENERAL_ERROR, 'Ошибка в изменении счета!', '', __LINE__, __FILE__, $sql);
        }
        else
        {
            $pos = strpos($money, "-");
            $drain = 0;
            	
            if ($pos !== false)
            {
                $drain = 1;
            }
            	
            /*if($type == 3)
             {
             if ($pos === false)
             {
             $money = "-".$money;
             }
             $drain = 1;
             }*/

            $sql = "UPDATE `money` SET
						`money` = '".$money."', `drain` = '".$drain."'
					WHERE `cat_id` = '0' AND `bill_id` = '".$id."' AND `user_id` = '".$user_id."'
					";

            if ( !($result = $this->db->sql_query($sql)) )
            {
                message_error(GENERAL_ERROR, 'Ошибка в изменении счета!', '', __LINE__, __FILE__, $sql);
            }
            else
            {
                $_SESSION['user_money'] = 'reload';
                $_SESSION['account_money'] = false;

                $this->user->initUserAccount($user_id);
                $this->user->save();

                return true;
            }
        }
    }

    function deleteAccount($id)
    {
        $user_id = $this->user_id;

        $sql = "DELETE FROM `money` WHERE `bill_id` = '".$id."' and `user_id` = '".$user_id."'";

        if ( !($result = $this->db->sql_query($sql)) )
        {
            message_error(GENERAL_ERROR, 'Ошибка в удалении счета!', '', __LINE__, __FILE__, $sql);
        }
        else
        {
            $sql = "DELETE FROM `bill` WHERE `bill_id` = '".$id."' and `user_id` = '".$user_id."'";

            if ( !($result = $this->db->sql_query($sql)) )
            {
                message_error(GENERAL_ERROR, 'Ошибка в удалении счета!', '', __LINE__, __FILE__, $sql);
            }
            else
            {
                $this->user->initUserAccount($user_id);
                $this->user->save();

                return true;
            }
        }
    }

    function saveMoney($money, $convert, $date, $from_account, $to_account, $comment)
    {
        $user_id = $this->user_id;
        $tr_id = md5($user_id."+".date("d-m-Y H-i-s"));
        $drain_money = "-$money";

        $sql = "INSERT INTO `money`
					(`id`, `user_id`, `money`, `date`, `cat_id`, `bill_id`, `drain`, `comment`, `transfer`, `tr_id`)
				VALUES
					('', '".$user_id."', '".$drain_money."', '".$date."', '-1', '".$from_account."', '1', '".$comment."', '".$to_account."', '".$tr_id."')
				";

        if ( !($result = $this->db->sql_query($sql)) )
        {
            message_error(GENERAL_ERROR, 'Ошибка в cохранении финансов!', '', __LINE__, __FILE__, $sql);
        }
        else
        {
            $sql = "INSERT INTO `money`
						(`id`, `user_id`, `money`, `date`, `cat_id`, `bill_id`, `drain`, `comment`, `transfer`, `tr_id`)
					VALUES
						('', '".$user_id."', '".$convert."', '".$date."', '-1', '".$to_account."', '0', '".$comment."' , '".$from_account."', '".$tr_id."')
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

    function updateMoney($money, $convert, $date, $from_account, $to_account, $tr_id, $comment)
    {
        $user_id = $this->user_id;
        $drain_money = "-$money";

        $sql = "UPDATE `money` SET
						`money` = '".$drain_money."', `date` = '".$date."', `transfer` = '".$to_account."', `comment` = '".$comment."'
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
						`money` = '".$convert."', `date` = '".$date."', `bill_id` = '".$to_account."', `comment` = '".$comment."'
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
			   SELECT DATE_FORMAT(m.`date`,'%d.%m.%Y') as date, m.`drain`, m.`money`,m.`transfer`, m.`comment`, 
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
    } // deleteMoney

    /**
     * Возвращает список всех счетов указанного пользователя
     *
     * @param string $userID Код пользователя
     *
     * @return array Список счетов пользовател
     * @throws Exception
     * @access public
     */
    public function getUserAccounts($userID='') {
        if (!$userID) throw new Exception('Не указан код пользователя',1); 	

        $sql = "SELECT bill_id,bill_name FROM bill WHERE user_id='$userID' ORDER BY bill_name";
        if ( !($result = $this->db->sql_query($sql)) )
        {
            throw new Exception('Ошибка при получении списка счетов пользователя',2);
        }
        else
        {
            $rows = $this->db->sql_fetchrowset($result);
            	
            $myRes = array();
            if (count($rows)>0) {
                foreach ($rows as $r) {
                    $myRes[$r['bill_id']] = $r['bill_name'];
                } // foreach
            } // if
            //pre($myRes);
            return $myRes;
        }  	// if result
    } // getUserAccounts



} // class Account