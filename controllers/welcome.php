<?
/**
 * Класс контроллера для модуля welcome
 * @copyright http://home-money.ru/
 * SVN $Id$
 */
class Welcome_Controller extends Template_Controller {

    /**
     *
     */
    public $welcome;

    /**
     * Страница по умолчанию без параметров
     * @return void
     */
    function index() {
        $tpl = $this->tpl;

        $welcome = new Welcome_Model($this->db, $this->tpl);
        $count = 800 + $welcome->getCountUsers();
        $transaction = $welcome->getAllTransaction();

        $tpl->assign('name_page', 'welcome');
        $tpl->assign("user_count", $count);
        $tpl->assign("money_count", $transaction);
        $tpl->assign('sys_currency', $sys_currency); // XXX

        $welcome->getAtricles();
        $tpl->assign('articles', $row);

        $tpl->assign('et', 'et'); //XXX WTF???

        if ($_GET['wish'] == 'ok') {
            $tpl->assign('wish', 'Спасибо за отзыв!');
        }

        if(!empty($_POST['email']) && !empty($_POST['captcha'])) {
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
            exit;
        }

//        if (!empty($_COOKIE[COOKIE_NAME]) && !empty($_COOKIE[COOKIE_VAL]) && empty($_SESSION['user'])) {
//            if ($user->initUser(html($_COOKIE['autoLogin']), html($_COOKIE['autoPass']))) {
//                header("Location: https://www.home-money.ru/index.php?modules=account");
//            }
//        }

    }

}
