<?
/**
 * file: welcam.php
 * author: Roman Korostov
 * date: 28/03/07
 **/
$tpl->assign('name_page', 'welcome');
$count = 800 + $user->getCountUsers();
$transaction = $user->getAllTransaction();

$tpl->assign("user_count", $count);
$tpl->assign("money_count", $transaction);

$date_start = "30.03.2007";

$date_now = date("d.m.Y");

$date_dif = $date_now - $date_start;

$tpl->assign('sys_currency', $sys_currency);

//$news_date = date("Y.m.d");
//$tpl->assign('news', $news->getTitleNews(10, $news_date));

//FIXME Убрать всю логику, запросы из модулей, перенести её в классы
//$sql = "select title, id from articles order by `date` desc limit 0,5";
//$result = $db->sql_query($sql);
//$row = $db->sql_fetchrowset($result);
$row = $db->query("SELECT title, id FROM articles ORDER BY `date` DESC LIMIT 0,5");

$tpl->assign('articles', $row);

$tpl->assign('et', 'et');

if ($_GET['wish'] == 'ok')
{
    $tpl->assign('wish', 'Спасибо за отзыв!');
}

if(!empty($_POST['email']) && !empty($_POST['captcha']))
{
    $errors = Array();
    // Проверяем Email на валидность
    if (!preg_match("(^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,6}$)i", $_POST['email'])) {
        $errors[] = "Неверный Email";
    }
    // И защитный код
    if ($_SESSION['captcha'] != $_POST['captcha']) {
        $errors[] = "Неверный код проверки";
    }

    // Если есть ошибки - выводим их.
    if (count($errors)) {
        echo "<img src=\"img/error.gif\" align=\"absmiddle\"> Ошибка!\n";
        foreach ($errors as $error) {
            echo "<li>".$error."</li>\n";
        }

    } else {
        $charset = "utf8";
        $db->sql_query("SET character_set_client = '$charset', character_set_connection = '$charset', character_set_results = '$charset'");
        $res = $db->sql_query("INSERT INTO wish (name, text, ip, ts)
									  VALUES ('".mysql_escape_string($_POST['email'])."', '".mysql_real_escape_string(html($_POST['text']))."', '".$_SERVER['REMOTE_ADDR']."', '".date('Y.m.d H:i:s')."')");
        $body = "<html><head><title>From home-money.ru</title></head>
					 <body>
					 <p>".html($_POST['text'])."</p>
					 </body>
					 </html>";
        $headers = "Content-type: text/html; charset=utf-8\n";
        $headers .= "From: ".html($_POST['email'])."\n";

        mail("support@home-money.ru", "Отзыв на сайте", $body, $headers);

        if (mysql_affected_rows()) {
            echo "<img src=\"img/success.gif\" align=\"absmiddle\"> Спасибо за Ваш отзыв!\n";
        } else {
            echo "<img src=\"img/error.gif\" align=\"absmiddle\"> Произошла ошибка на сервере. Приносим свои извинения!\n";
        }
    }
    //pre($_POST);
    exit;
}

if (!empty($_COOKIE['autoLogin']) && !empty($_COOKIE['autoPass']) && empty($_SESSION['user']))
{
    if ($user->initUser(html($_COOKIE['autoLogin']),html($_COOKIE['autoPass'])))
    {
        header("Location: https://www.home-money.ru/index.php?modules=account");
    }
}
?>