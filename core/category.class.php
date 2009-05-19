<?
/**
* file: category.class.php
* author: Roman Korostov
* date: 9/03/07
**/

class Category
{
	var $db             = false;
    var $user           = false;
	var $user_id        = 0;
	var $cat_id 		= 0;

	function Category(&$db, &$user)
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

	// cat_id -> cat_parent , Родитель новой категории
	// cat_name -> название новой категории
	function saveCategory($cat_id, $cat_name)
	{
		$user_id = $this->user_id;

		if (!$cat_id) $cat_id = 0;

		$sql = "INSERT INTO `category`
					(`cat_name`, `cat_parent`, `user_id`, `cat_active`)
				VALUES
					('".$cat_name."', '".$cat_id."', '".$user_id."', '1')
				";

		if ( !($result = $this->db->sql_query($sql)) )
		{
			message_error(GENERAL_ERROR, 'Ошибка в cохранении категории!', '', __LINE__, __FILE__, $sql);
		}
		else
		{
			$new_cat_id = $this->db->sql_nextid();
			$this->user->initUserCategory($user_id);
			$this->user->save();

			//return true;
			return $new_cat_id;
		}
	}

	function updateCategory($cat_id, $cat_parent, $cat_name)
	{
		$user_id = $this->user->getId();

		$sql = "UPDATE `category` SET
					`cat_name` = '".$cat_name."', `cat_parent` = '".$cat_parent."',
					`user_id` = '".$user_id."'
				WHERE `cat_id` = '".$cat_id."'
				";
		if ( !($result = $this->db->sql_query($sql)) )
		{
			message_error(GENERAL_ERROR, 'Ошибка в cохранении категории!', '', __LINE__, __FILE__, $sql);
		}
		else
		{
			$this->user->initUserCategory($user_id);
			$_SESSION['user_money'] = "reload";
			$this->user->save();

			return true;
		}
	}

	function selectCategory($id)
	{
		$sql = "SELECT `cat_id` as `id`, `cat_name` as `name`, `cat_parent` as `parent`,
						`user_id`, `cat_active` as active
					FROM `category`
						WHERE `cat_id` = '".$id."'
							   AND `cat_active` = '1'";

		if ( !($result = $this->db->sql_query($sql)) )
		{
			message_error(GENERAL_ERROR, 'Ошибка в получении категории!', '', __LINE__, __FILE__, $sql);
		}
		else
		{
			$row = $this->db->sql_fetchrowset($result);
			return $row;
		}
	}

	function deleteCategory($id)
	{
		$user_id = $this->user->getId();

		$sql = "UPDATE `category` SET
				`cat_active` = '0'
				WHERE (`cat_id` = '".$id."' AND `user_id` = '".$user_id."')
											OR (`cat_parent` = '".$id."' AND `user_id` = '".$user_id."')";

		if ( !($result = $this->db->sql_query($sql)) )
		{
			message_error(GENERAL_ERROR, 'Ошибка в удалении категории!', '', __LINE__, __FILE__, $sql);
		}
		else
		{
			$this->user->initUserCategory($user_id);
			$this->user->save();

			return true;
		}
	}

	/**
   * Возвращает список всех категорий указанного пользователя
   *
   * @param string $userID Код пользователя
   *
   * @return array Список категорий пользователя
   * @throws Exception
   * @access public
   */
  public function getUserCategories($userID='') {
  	if (!$userID) throw new Exception('Не указан код пользователя',1);

  	$sql = "SELECT cat_id,cat_name FROM category WHERE user_id='$userID' and cat_active=1 ORDER BY cat_name";
  	if ( !($result = $this->db->sql_query($sql)) )
		{
			throw new Exception('Ошибка при получении списка счетов пользователя',2);
		}
		else
		{
			$rows = $this->db->sql_fetchrowset($result);

			$myRes = array();
			foreach ($rows as $r) {
				$myRes[$r['cat_id']] = $r['cat_name'];
			} // while
			//pre($myRes);
			return $myRes;
		}  	// if result
  } // getUserCategories

  /**
   * Возвращает список родительских категорий
   *
   * @param string $userID Код пользователя
   *
   * @return array Список категорий пользователя
   * @throws Exception
   * @access public
   */
  public function getParents($catID) {
  	if (!$catID) throw new Exception('Не указан код категории',1);
  	$parents = array();

  	$catData = $this->selectCategory($catID);
  	$parents[] = $catData[0]['name'];


  	$parentID = $catData[0]['parent'];
  	if ($parentID == 0) return $parents;

  	$parents = array_merge($parents,$this->getParents($parentID));
  	return $parents;
  } // getParents

  public function getOftenCategories($user_id)
  {
	  $sql = "select count(m.cat_id) as cnt, m.cat_id, c.cat_name from money  m
	  			left join category c on c.cat_id = m.cat_id and c.user_id = '".$user_id."'
			where m.user_id = '".$user_id."' and m.cat_id > 0
			group by m.cat_id order by cnt desc limit 0,7";
	  $result = $this->db->sql_query($sql);
	  return $this->db->sql_fetchrowset($result);
  }

  public function loadUserTree($user_id)
  {
	  $sql = "SELECT * FROM category WHERE user_id = '".$user_id."' and `cat_active` = '1' order by cat_name";
	  $result = $this->db->sql_query($sql);
	  return $this->db->sql_fetchrowset($result);
	  //$this->loadTree($forest);
  }

  /**
   * Возвращает список категорий для детализации в планировании бюджета
   * @param array $data Список категорий из сессии
   * @param string $prefix префикс для детализации in-доходы, out-расходы
   * @param $sum_period массив суммы по категориям за выбранный период
   * @param $sys_currency массив валют для пересчета
   * @param $check_cats список категорий, которые пользователь уже отметил
   *
   * @return string Список категорий пользователя
   * @access public
   */
  public function getDetalizeCategoriesForPlan2($data, $prefix, $sum_period, $sys_currency, $check_cats = 0)
  {
	$cnt = count($data);
	$cnt_sum = count($sum_period);

	//pre($data);

	$cats = "";

	for ($i=0; $i<$cnt; $i++)
	{
		if ($data[$i]['cat_parent'] == 0)
		{
			$display = 'none';
			$checked = '';

			if (!empty($check_cats))
			{
				foreach ($check_cats as $key=>$value)
				{
					if ($key == $data[$i]['cat_id'])
					{
						$display = 'block';
						$checked = 'checked';
					}
				}
			}

			$is_checkbox = 0;

			for ($c=0; $c<$cnt; $c++)
			{
				if ($data[$c]['cat_parent'] == $data[$i]['cat_id'])
				{
					$is_checkbox++;
				}
			}

			if ($is_checkbox > 0)
			{
				$cats .= "
					<tr>
						<td class=cat_add >
							<div style='display:none;'>
								<input type=checkbox $checked id='parent_".$prefix."_".$data[$i]['cat_id']."' name='".$prefix."[".$data[$i]['cat_id']."][0]'>
							</div>
							<b><a href='javascript:showChildCategory(\"show_".$prefix."_".$data[$i]['cat_id']."\");' class='cat_add'>
								<img src='/img/tree/nolines_plus.gif' border=0>".$data[$i]['cat_name']."</a></b>
						</td>
						<td class=cat_add colspan=2>&nbsp;</td>
					</tr>";
			}else{
				$cats .= "
					<tr>
						<td class=cat_add width=360>
							<div style='display:none;'>
								<input type=checkbox $checked id='parent_".$prefix."_".$data[$i]['cat_id']."' name='".$prefix."[".$data[$i]['cat_id']."][0]'>
							</div>
							&nbsp;&nbsp;&nbsp;&nbsp;<b>
							<a href='javascript:showChildCategory(\"show_".$prefix."_".$data[$i]['cat_id']."\");' class='cat_add'> ".$data[$i]['cat_name']."</a></b>
						</td>
						<td class=cat_add width=160>
							<div style='display:none;'>
								<input type=checkbox checked name='".$prefix."[".$data[$i]['cat_id']."][0]'
									id='ch_".$prefix."_".$data[$i]['cat_id']."'>
							</div>
							<input type='text' value='$cat_sum' name='".$prefix."[".$data[$i]['cat_id']."][1]'
											class='child_".$prefix."_".$data[$i]['cat_parent']."'
											id='inp_".$prefix."_".$data[$i]['cat_id']."' style='width:140px;'
											onblur=\"cp(this); checkSumOutcome();\">
						</td>
						<td align=left class=cat_add><span>$sum руб.</span></td>
					</tr>";
			}

			$c = 0;

			$cats .= "<tr id='show_".$prefix."_".$data[$i]['cat_id']."' style='display:$display;'>
						<td colspan=2>
							<table width=100%>";
			for ($j=0; $j<$cnt; $j++)
			{
				if ($data[$j]['cat_parent'] == $data[$i]['cat_id'])
				{
				$sum = 0;
					for ($n=0; $n<$cnt_sum; $n++)
					{


							if ($sum_period[$n]['cat_id'] == $data[$j]['cat_id'])
							{
								$temp_sum = $sys_currency[$sum_period[$n]['bill_currency']] * $sum_period[$n]['sum'];
								$sum = $sum + $temp_sum;
								$sum = round(($sum / 3),2);
								if ($sum < 0)
								{
									$sum = $sum * -1;
								}
							}
					}

					$checked = '';
					$cat_sum = '';

					if (!empty($check_cats))
					{
						foreach ($check_cats as $key=>$value)
						{
							if ($key == $data[$j]['cat_id'])
							{
								$cat_sum = $value;
								$checked = 'checked';
							}
						}
					}

					$cats .= "<tr onMouseOver=this.style.backgroundColor='#ffffff';
						   		onMouseOut=this.style.backgroundColor='#f8f8d8';>
								<td width=330 style='padding-left:30px;' class=cat_add>".$data[$j]['cat_name']."</td>
								<td width=160 align=left class=cat_add>
									<div style='display:none;'>
										<input type=checkbox $checked name='".$prefix."[".$data[$j]['cat_id']."][0]'
											id='ch_".$prefix."_".$data[$j]['cat_id']."'>
									</div>
									<input type='text' value='$cat_sum' name='".$prefix."[".$data[$j]['cat_id']."][1]'
										class='child_".$prefix."_".$data[$j]['cat_parent']."'
										id='inp_".$prefix."_".$data[$j]['cat_id']."' style='width:140px;'
										onblur=\"cp(this); checkCategory('".$prefix."_".$data[$j]['cat_id']."'); checkSumOutcome();\">
								</td>
								<td align=left class=cat_add><span>$sum руб.</span></td>
							</tr>";
					$c++;
				}
			}
			$cats .= "</table></td></tr>";
		}
	}
	return "<table width='100%'>".$cats."</table>";
  } // getDetalizeIncomeCategories

public function getDetalizeCategoriesForPlan($data, $prefix, $sum_period, $sys_currency, $check_cats = 0)
  {
	$cnt = count($data);
	$cnt_sum = count($sum_period);

	$sum_outcome = "";

	if ($prefix == 'out')
	{
		$sum_outcome = "checkSumOutcome();";
	}

	$cats = "";

	for ($i=0; $i<$cnt; $i++)
	{
		if ($data[$i]['cat_parent'] == 0)
		{
			$display = 'none';
			$checked = '';

			if (!empty($check_cats))
			{
				foreach ($check_cats as $key=>$value)
				{
					if ($key == $data[$i]['cat_id'])
					{
						$display = 'block';
						$checked = 'checked';
					}
				}
			}

			$is_checkbox = 0;

			for ($c=0; $c<$cnt; $c++)
			{
				if ($data[$c]['cat_parent'] == $data[$i]['cat_id'])
				{
					$is_checkbox++;
				}
			}

			$sum = 0;
			for ($n=0; $n<$cnt_sum; $n++)
			{
				if ($sum_period[$n]['cat_id'] == $data[$i]['cat_id'])
				{
					$temp_sum = $sys_currency[$sum_period[$n]['bill_currency']] * $sum_period[$n]['sum'];
					$sum = $sum + $temp_sum;
					$sum = round(($sum / 3),2);
					if ($sum < 0)
					{
						$sum = $sum * -1;
					}
				}
			}

			if ($is_checkbox > 0)
			{
				$cats .= "<tr>
					<td colspan='3'>
						<div style='display:none;'>
							<input type=checkbox $checked id='parent_".$prefix."_".$data[$i]['cat_id']."'
									name='".$prefix."[".$data[$i]['cat_id']."][0]'>
						</div>
						<a class='cat_add childrenToggler' rel='parent_".$data[$i]['cat_id']."' href='javascript:void(0)'>".$data[$i]['cat_name']."</a></td></tr>";
					//<strong class='haveChildren'> </strong>
			}else{
				$cats .= "<tr class='cat_add colored'><td class='cat_add parent_padding'>
						<strong>".$data[$i]['cat_name']."</strong></td>
						<div style='display:none;'>
							<input type=checkbox $checked name='".$prefix."[".$data[$i]['cat_id']."][0]'
								id='parent_".$prefix."_".$data[$i]['cat_id']."'>
						</div>
						<td><input type='text' name='".$prefix."[".$data[$i]['cat_id']."][1]'
							class='child_".$prefix."_".$data[$i]['cat_id']."' inp_".$prefix."_".$data[$i]['cat_id']."
							onblur=\"cp(this); $sum_outcome\">
						</td><td class='cat_add'>$sum руб.
					</td></tr>";
			}

			$c = 0;

			for ($j=0; $j<$cnt; $j++)
			{
				if ($data[$j]['cat_parent'] == $data[$i]['cat_id'])
				{
				$sum = 0;
					for ($n=0; $n<$cnt_sum; $n++)
					{


							if ($sum_period[$n]['cat_id'] == $data[$j]['cat_id'])
							{
								$temp_sum = $sys_currency[$sum_period[$n]['bill_currency']] * $sum_period[$n]['sum'];
								$sum = $sum + $temp_sum;
								$sum = round(($sum / 3),2);
								if ($sum < 0)
								{
									$sum = $sum * -1;
								}
							}
					}

					$checked = '';
					$cat_sum = '';

					if (!empty($check_cats))
					{
						foreach ($check_cats as $key=>$value)
						{
							if ($key == $data[$j]['cat_id'])
							{
								$cat_sum = $value;
								$checked = 'checked';
							}
						}
					}


					$cats .= "<tr class='cat_add hidden colored parent_".$data[$j]['cat_parent']."'>
						<td class='child_padding'>
							<div style='display:none;'>
								<input type=checkbox $checked name='".$prefix."[".$data[$j]['cat_id']."][0]'
									id='ch_".$prefix."_".$data[$j]['cat_id']."'>
							</div>
							".$data[$j]['cat_name']."</td>
						<td><input type='text' name='".$prefix."[".$data[$j]['cat_id']."][1]'
							class='child_".$prefix."_".$data[$j]['cat_parent']."' inp_".$prefix."_".$data[$j]['cat_id']."
							onblur=\"cp(this); $sum_outcome\">
						</td><td>$sum руб.</td></tr>";
					$c++;
				}
			}
		}
	}
	return $cats;
  } // getDetalizeIncomeCategories


} // class
?>
