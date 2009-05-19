<?php
/**
 * Класс работы со статьями
 */
    
Class Article
{
    /**
    * Объект БД
    *
    * @var object Database   
    */
    private $dbs;
    
    /**
    * Количество статей на странице архива
    *
    * @var integer   
    */
    private $max_per_page = 10;
    
    /**
    * Контейнер с сборкой по статьям
    *
    * @var array 
    */
    private $articles;
    
    /**
    * Производит инициализацию объектов
    *
    * @param array $conf Хэш с конфигурационными параметрами
    */
    public function __construct($conf=array()) 
    {
        if (!isset($conf['dbs']))
        {
            $conf['dbs'] = DbSimple_Generic::connect("mysql://".SYS_DB_USER.":".SYS_DB_PASS."@".SYS_DB_HOST."/".SYS_DB_BASE);
            $this->db->query("SET character_set_client = 'utf8', character_set_connection = 'utf8', character_set_results = 'utf8'");
        }
  	
  	$this->dbs = $conf['dbs'];
    } // __construct
    
    
    /**
    * Производит сбор конфигураций для статей и выдает массив данных
    *
    * @param array $conf Хэш с конфигурационными параметрами
    * @return array listArticles
    */
    public function getDataArticles($conf=array())
    {
        //получаем общий тотал
        $this->articles['articles_count'] = $this->getCountArticles(); 
        $this->articles['pages_count'] = ceil($this->articles['articles_count']/$this->max_per_page);
        $this->articles['prev_page'] = 0;
        if ($this->articles['pages_count'] > 0)
        {
            if (isset($conf['page']) && $conf['page'] < $this->articles['pages_count']) 
            {
                if ($conf['page'] > 0)
                {
                    $this->articles['prev_page'] = $conf['page']-1;
                }
                if ($conf['page'] < ($this->articles['pages_count'] - 1))
                {
                    $this->articles['next_page'] = $conf['page']+1;
                }
                
                $conf['limit_start'] = ($conf['page'] * $this->max_per_page)+1;
                $conf['limit_end'] = (($conf['page']+1) * $this->max_per_page);
            }
        }
       
        $this->articles['list_articles'] = $this->getListArticles($conf);
        return $this->articles;
    } // getDataArticles
    
    /**
    * Производит подсчет общего количества статей
    *
    * @return integer
    */
    public function getCountArticles($conf=array())
    {        
        $cnt = $this->dbs->select("select count(*) as cnt from articles");
        return $cnt[0]['cnt'];
    } // getCountArticles
    
    /**
    * Производит выбор статей по параметрам
    *
    * @return integer
    */
    public function getListArticles($conf=array())
    {           
        return $this->dbs->select("select id, title, description, DATE_FORMAT(date,'%d.%m.%Y') as date from articles order by date desc limit ".$conf['limit_start'].",".$conf['limit_end']);        
    } // getListArticles
}
?>