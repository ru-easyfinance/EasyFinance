<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс-модель для страницы welcome
 * @category welcome
 * @copyright http://easyfinance.ru/
 * @version SVN $Id$
 */
class Welcome_Model
{
   /**
     * Возвращает количество активных пользователей
     * return int
     */
    function getCountusers ()
    {
        return Core::getInstance()->db->selectCell("SELECT count(id) FROM users WHERE user_active='1';");
    }

    /**
     * Возвращает количество всех денег
     * return int
     */
    function getAllTransaction ()
    {
        return Core::getInstance()->db->selectCell("SELECT count(*) FROM operation;");
    }

    /**
     * Возвращает список статей
     * @deprecated ???
     * @return array mixed
     */
    function getAtricles ()
    {
        //return Core::getInstance()->db->query("SELECT title, id FROM articles ORDER BY `date` DESC LIMIT 0,5");
    }

    /**
     *
     */
    function sendFeedBack() {
        $errors = Array();
        // Проверяем Email на валидность
        if (!validate_email(@$_POST['email'])) {
            $errors[] = "Неверный Email";
        }

        // И защитный код
        if (@$_SESSION['captcha'] != @$_POST['captcha']) {
            $errors[] = "Неверный код проверки";
        }

        // Если есть ошибки - выводим их.
        if (count($errors)) {
            //FIXME Убрать разметку
            echo '<img src="/img/error.gif" align="absmiddle"> Ошибка!';
            foreach ($errors as $error) {
                echo "<li>{$error}</li>";
            }
        } else {
            //Отправляем почту
            Core::getInstance()->db->query("INSERT INTO wish (name, text, ip, ts) VALUES (?,?,?,?);",
                @$_POST['email'], htmlspecialchars(@$_POST['text']), $_SERVER['REMOTE_ADDR'], date('Y.m.d H:i:s'));
            $body = "<html><head><title>From home-money.ru</title></head>
                         <body><p>".htmlspecialchars($_POST['text'])."</p></body></html>";
            $headers = "Content-type: text/html; charset=utf-8\n";
            $headers .= "From: ".htmlspecialchars($_POST['email'])."\n";

            mail("support@home-money.ru", "Отзыв на сайте", $body, $headers);
            //FIXME Убрать разметку
            if (mysql_affected_rows()) {
                echo "<img src=\"img/success.gif\" align=\"absmiddle\"> Спасибо за Ваш отзыв!\n";
            } else {
                echo "<img src=\"img/error.gif\" align=\"absmiddle\"> Произошла ошибка на сервере. Приносим свои извинения!\n";
            }
        }
        return true;
    }
}