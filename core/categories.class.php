<?php

class CategoriesClass {
	
	private $db = NULL;
	private $user_id = NULL;
	public $system_categories = array();
	public $tree = array();
	public $tree_sum_categories = array();
	
	public function __construct($database_descriptor = null, $user_id) 
	{
		if ($database_descriptor) {
			$this->db = $database_descriptor;
		} else {
			$this->db = DbSimple_Generic::connect("mysql://".SYS_DB_USER.":".SYS_DB_PASS."@".SYS_DB_HOST."/".SYS_DB_BASE);
			$this->db->query("SET character_set_client = 'utf8', 
							 character_set_connection = 'utf8', 
							 character_set_results = 'utf8'");
		}
		$this->user_id = $user_id;
		
		$this->loadSystemCategories();
		
		$this->loadCache();
		
		if (!count($this->tree))
		{
			$this->loadUserTree();			
		}
	} // __construct
	
	private function saveCache()
	{
		$_SESSION['categories']	= $this->tree;
		$_SESSION['tree_sum_categories'] = $this->tree_sum_categories;
	}
	
	private function loadCache()
	{
		$this->tree = $_SESSION['categories'];
		$this->tree_sum_categories = $_SESSION['tree_sum_categories'];
	}
	
	/** загружает для пользователя системные категории.	*/
	private function loadSystemCategories()
	{
		$this->system_categories = $this->db->select("SELECT * FROM system_categories WHERE parent_id = 0");		
	}
	
	/** Получает все дерево категорий определнного пользователя. */
	public function loadUserTree()
	{
		$where = $_SESSION['categories_filtr'];
		$forest = $this->db->select("SELECT c.*, c.cat_id AS ARRAY_KEY, c.cat_parent AS PARENT_KEY, sc.system_category_name FROM category c  
						left join system_categories sc on sc.system_category_id = c.system_category_id
						WHERE c.user_id = ? ".$where." and c.cat_active=1 order by cat_name", $this->user_id);
		$this->tree = $forest;
		$this->saveCache();
		//$this->loadTree($forest);
	}
	
	function loadSumCategories($sys_currency, $date="")
	{
		$row = $this->db->select("SELECT sum( m.money ) AS sum, m.cat_id, b.bill_currency FROM `money` m
										LEFT JOIN bill b ON b.bill_id = m.bill_id and b.user_id = ?
									 WHERE m.user_id = ? ".$date."
										GROUP BY m.cat_id, b.bill_currency", $this->user_id, $this->user_id);
		$cnt = count($row);
		
		for ($i=0; $i<$cnt; $i++)
		{
			$id = $row[$i]['cat_id'];
			
			for ($j=0; $j<$cnt; $j++)
			{
				if ($id == $row[$j]['cat_id'])
				{
					if ($row[$j]['currency'] != 1)
					{
						$sum = $row[$j]['sum'] * $sys_currency[$row[$j]['bill_currency']];
					}					
					
					$forest[$id]['sum'] = $forest[$id]['sum'] + $sum;					
					
					$drain = 0;
					
					if ($forest[$id]['sum'] < 0)
					{
						$drain = 1;	
						$forest[$id]['sum'] = $forest[$id]['sum'] * -1;
					}
					
					$forest[$id]['drain'] = $drain;
				}
			}
		}
		
		// считаем общую сумму
		if (!empty($forest))
		{
			foreach($forest as $key=>$value)
			{
				if ($value['drain'] == 0)
				{
					$this->total_sum_categories['income'] = $this->total_sum_categories['income'] + $value['sum'];
				}else{
					$this->total_sum_categories['outcome'] = $this->total_sum_categories['outcome'] + $value['sum'];
				}
			}
		}
		
		// высчитываем процентное отношение и форматируем сумму
		if (!empty($forest))
		{
			foreach($forest as $key=>$value)
			{
				if ($value['sum'] > 0)
				{
					if ($value['drain'] == 0)
					{
						$forest[$key]['percent'] = $value['sum'] * 100 / $this->total_sum_categories['income'];		
						$forest[$key]['drain'] = 0;
					}else{
						$forest[$key]['percent'] = $value['sum'] * 100 / $this->total_sum_categories['outcome'];
						$forest[$key]['drain'] = 1;
					}
				}else{
					$forest[$key]['percent'] = 0;
				}
				$forest[$key]['sum'] = number_format($value['sum'], 2, '.', ' ');
				$forest[$key]['percent'] = number_format($forest[$key]['percent'], 0, '.', ' ');
			}
		}
		
		// вставляем дополнительные параметры в основной массив категорий
		foreach ($this->tree as $key=>$value)
		{
			$this->tree[$key]['sum'] = 0;
			$this->tree[$key]['percent'] = 0;
			$this->tree[$key]['drain'] = 1;
			
			if (!empty($forest))
			{
				foreach ($forest as $f_key => $f_value)
				{
					if ($key == $f_key)
					{
						$this->tree[$key]['sum'] = $f_value['sum'];
						$this->tree[$key]['percent'] = $f_value['percent'];
						$this->tree[$key]['drain'] = $f_value['drain'];
					}
				}
			}
			if (!empty($value['childNodes']))
			{
				foreach ($value['childNodes'] as $c_key=>$c_value)
				{
					$this->tree[$key]['childNodes'][$c_key]['sum'] = 0;
					$this->tree[$key]['childNodes'][$c_key]['percent'] = 0;
					$this->tree[$key]['childNodes'][$c_key]['drain'] = 1;
					
					if (!empty($forest))
					{
						foreach ($forest as $f_key => $f_value)
						{
							if ($c_key == $f_key)
							{
								$this->tree[$key]['childNodes'][$c_key]['sum'] = $f_value['sum'];
								$this->tree[$key]['childNodes'][$c_key]['percent'] = $f_value['percent'];
								$this->tree[$key]['childNodes'][$c_key]['drain'] = $f_value['drain'];
							}
						}
					}
				}
			}
		}
		$this->saveCache();
		//$this->tree_sum_categories = $forest;
	}
	
	public function createNewCategory($category, $dbs = null)
	{
		if (!$dbs) {
			$dbs = DbSimple_Generic::connect("mysql://".SYS_DB_USER.":".SYS_DB_PASS."@".SYS_DB_HOST."/".SYS_DB_BASE);
			$dbs->query("SET character_set_client = 'utf8', 
							 character_set_connection = 'utf8', 
							 character_set_results = 'utf8'");
		}
		
		if (!$dbs->query('INSERT INTO category(?#) VALUES(?a)', array_keys($category), array_values($category)))
		{
			return false;	
		}
		return true;
	}
	
	public function selectCategoryId($id, $dbs = null)
	{
		if (!$dbs) {
			$dbs = DbSimple_Generic::connect("mysql://".SYS_DB_USER.":".SYS_DB_PASS."@".SYS_DB_HOST."/".SYS_DB_BASE);
			$dbs->query("SET character_set_client = 'utf8', 
							 character_set_connection = 'utf8', 
							 character_set_results = 'utf8'");
		}
		
		$row = $dbs->select('select * from category where cat_id = ? and user_id = ?', $id, $this->user_id);
		return $row;
	}
	
	public function updateCategory($category, $dbs = null)
	{
		if (!$dbs) {
			$dbs = DbSimple_Generic::connect("mysql://".SYS_DB_USER.":".SYS_DB_PASS."@".SYS_DB_HOST."/".SYS_DB_BASE);
			$dbs->query("SET character_set_client = 'utf8', 
							 character_set_connection = 'utf8', 
							 character_set_results = 'utf8'");
		}
		
		if (!$this->db->query("UPDATE category SET ?a WHERE cat_id = ?d", $category, $category['cat_id'], $this->user_id))
		{					
			return false;
		}

		if ($category['type'] != 2)
		{
			$this->db->query("UPDATE category SET `type` = ? WHERE cat_parent = ?", $category['type'], $category['cat_id']);
		}
		return true;
	}
	
	public function visibleCategory($id, $visible, $dbs = null)
	{
		if (!$dbs) {
			$dbs = DbSimple_Generic::connect("mysql://".SYS_DB_USER.":".SYS_DB_PASS."@".SYS_DB_HOST."/".SYS_DB_BASE);
			$dbs->query("SET character_set_client = 'utf8', 
							 character_set_connection = 'utf8', 
							 character_set_results = 'utf8'");
		}
		
		if (!$this->db->query("UPDATE category SET visible='".$visible."' WHERE cat_id = ? and user_id = ?", $id, $this->user_id))
		{	
			return false;
		}
		
		$parent_info = $this->getParentInfo($id);

		if ($parent_info[0]['cat_id'] == $id)
		{
			$this->db->query("UPDATE category SET visible='".$visible."' WHERE cat_parent = ? and user_id = ?", $id, $this->user_id);
		}

		return true;
	}
	
	public function deleteCategory($id, $dbs = null)
	{
		if (!$dbs) {
			$dbs = DbSimple_Generic::connect("mysql://".SYS_DB_USER.":".SYS_DB_PASS."@".SYS_DB_HOST."/".SYS_DB_BASE);
			$dbs->query("SET character_set_client = 'utf8', 
							 character_set_connection = 'utf8', 
							 character_set_results = 'utf8'");
		}
		
		$dbs->query("UPDATE category SET cat_active = '0' WHERE cat_parent = ? AND user_id = ?", $id, $this->user_id);
		
		if (!$dbs->query("UPDATE category SET cat_active = '0' WHERE cat_id = ? AND user_id = ?", $id, $this->user_id))
		{
			return false;
		}
		return true;
	}

	private function getParentInfo($id)
	{
		$row = $this->db->select('select * from category where cat_id = ?', $id);

		if ($row[0]['cat_parent'] != 0)
		{
			$row = $this->db->select('select * from category where cat_id = ?', $row[0]['cat_parent']);
		}
		return $row;
	}
}
?>
