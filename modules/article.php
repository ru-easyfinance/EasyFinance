<?php
$tpl->assign('name_page', 'article');

// подключаем все необходимые библиотеки
require_once (SYS_DIR_LIBS . "external/DBSimple/Mysql.php");
require_once (SYS_DIR_LIBS . "/article/article.class.php");

// Инициируем контроллер категорий. Всё в нём.
try 
{	
    // создаем объект класса DB_Simple
    $dbs = DbSimple_Generic::connect("mysql://".SYS_DB_USER.":".SYS_DB_PASS."@".SYS_DB_HOST."/".SYS_DB_BASE);
    $dbs->query("SET character_set_client = 'utf8', character_set_connection = 'utf8', character_set_results = 'utf8'");

    $conf['dbs'] = $dbs;

    $articles = new Article($conf);
	
} catch (Exception $e) {
	message_error(CRITICAL_ERROR,'',$e->getMessage(),$e->getLine(),$e->getFile(),'');
}
    
$action = html($g_action);

    switch ($action)
    {
        case "archive":
            $conf = false;
            $conf['page'] = 0;
            if (isset($_GET['page']) && $_GET['page'] != "")
            {
                $conf['page'] = html($_GET['page']);
            }

            $data_articles = $articles->getDataArticles($conf);
            for ($i=0; $i<$data_articles['pages_count']; $i++)
            {
                if ($conf['page'] == $i)
                {
                    $data_articles['current_page'] = $i;
                    $data_articles['param_pages'][$i]['page'] = "<td class='article_archive_page_active'>$i</td>";
                }else{
                    $data_articles['param_pages'][$i]['page'] = "<td class='article_archive_page'><a href='index.php?modules=article&action=archive&page=".$i."'>$i</a></td>";
                }
            }
            //pre($data_articles);
            $tpl->assign("data_articles", $data_articles);
        break;
        
        default:
            $id = html($g_id);
            $sql = "select * from articles where id = '".$id."'";
            $result = $db->sql_query($sql);
            $row = $db->sql_fetchrow($result);
            
            $tpl->assign("article",$row);
        break;
    }

?>