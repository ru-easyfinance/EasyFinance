<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера для модуля почты
 * @copyright http://home-money.ru/
 * @version SVN $Id: $
 */

class Mail_Controller extends _Core_Controller_UserExpert
{
    /**
     * Ссылка на класс модель
     * @var Mail_Model
     */
    private $model = null;

    /**
     * Конструктор класса
     * @return void
     */
    function __init()
    {
        $this->model = new Mail_Model();
    }

    /**
     * обязательно при подключении класса!!!(ускоряет работу с ним)
     * @return void
     */
    function index()
    {
        $this->tpl->assign('name_page', 'mail/mail');
        
        $this->model->index();
        //die();
    }


    /**
     * @return json список входящих писем
     */
     function inbox(){
        $jsonTest = '{
            1: {folder: "inbox", unread: true, subject: "Входящее Письмо 1", senderName: "Эксперт", receiverName: "Jet", senderId: 1, receiverId: 2, date: "21.11.1986"},
            2: {folder: "inbox", unread: false, subject: "Входящее Письмо 2", senderName: "Любофф", receiverName: "Jet", senderId: 1, receiverId: 2, date: "22.11.1986"},
            3: {folder: "inbox", unread: false, subject: "Входящее Письмо 3", senderName: "Гуру", receiverName: "Jet", senderId: 1, receiverId: 2, date: "23.11.1986"},
        }';

        die($jsonTest);
     }

    /**
     * @return json список отправленных писем
     */
     function outbox(){
        $jsonTest = '{
            4: {folder: "outbox", subject: "Исходящее Письмо 1", receiverName: "Эксперт", senderName: "Jet", senderId: 1, receiverId: 2, date: "21.11.1986"},
            5: {folder: "outbox", subject: "Исходящее Письмо 2", receiverName: "Любофф", senderName: "Jet", senderId: 1, receiverId: 2, date: "22.11.1986"},
            6: {folder: "outbox", subject: "Исходящее Письмо 3", receiverName: "Гуру", senderName: "Jet", senderId: 1, receiverId: 2, date: "23.11.1986"},
        }';

        die($jsonTest);
     }

    /**
     * @return json список черновиков
     */
     function drafts(){
        $jsonTest = '{
            7: {folder: "drafts", subject: "Черновик 1", receiverName: "Эксперт", senderName: "Jet", senderId: 1, receiverId: 2, date: "21.11.1986"},
            8: {folder: "drafts", subject: "Черновик 2", receiverName: "Любофф", senderName: "Jet", senderId: 1, receiverId: 2, date: "22.11.1986"},
            9: {folder: "drafts", subject: "Черновик 3", receiverName: "Гуру", senderName: "Jet", senderId: 1, receiverId: 2, date: "23.11.1986"},
        }';

        die($jsonTest);
     }

    /**
     * @return json список удалённых писем
     */
     function trash(){
        $jsonTest = '{
            10: {folder: "trash", subject: "Удалённое Письмо 1", receiverName: "Эксперт", senderName: "Jet", senderId: 1, receiverId: 2, date: "21.11.1986"},
            11: {folder: "trash", subject: "Удалённое Письмо 2", receiverName: "Любофф", senderName: "Jet", senderId: 1, receiverId: 2, date: "22.11.1986"},
            12: {folder: "trash", subject: "Удалённое Письмо 3", receiverName: "Гуру", senderName: "Jet", senderId: 1, receiverId: 2, date: "23.11.1986"},
        }';

        die($jsonTest);
     }

    /**
     * отдаёт список писем во всех папка
     * @return json список писем
     */
    function listall()
    {
        //die(json_encode($this->model->mail_list()));

        $jsonTest = '{
            inbox: {
                0: {id: 1, unread: true, subject: "Входящее письмо с очень длинным заголовком", senderName: "Дядя Стёпа Милиционер", receiverName: "Jet", senderId: 1, receiverId: 2, date: "21.11.1986", body: "bod-bod-bod"},
                1: {id: 2, unread: false, subject: "Входящее Письмо 2", senderName: "Любофф", receiverName: "Jet", senderId: 1, receiverId: 2, date: "22.11.1986", body: "bod-bod-bod"},
                2: {id: 3, unread: false, subject: "Входящее Письмо 3", senderName: "Гуру", receiverName: "Jet", senderId: 1, receiverId: 2, date: "23.11.1986", body: "bod-bod-bod"},
            },
            outbox: {
                0: {id: 4, unread: false, subject: "Исходящее Письмо 1", receiverName: "На Берлин", senderName: "Jet", senderId: 1, receiverId: 2, date: "21.11.1986", body: "bod-bod-bod"},
                1: {id: 5, unread: true, subject: "Исходящее Письмо 2", receiverName: "Любимой", senderName: "Jet", senderId: 1, receiverId: 2, date: "22.11.1986", body: "bod-bod-bod"},
                2: {id: 6, unread: false, subject: "Исходящее Письмо 3", receiverName: "В канцелярию", senderName: "Jet", senderId: 1, receiverId: 2, date: "23.11.1986", body: "bod-bod-bod"},
            },
            drafts: {
                0: {id: 7, subject: "Черновик 1", receiverName: "Эксперт", senderName: "Jet", senderId: 1, receiverId: 2, date: "21.11.1986", body: "bod-bod-bod"},
                1: {id: 8, subject: "Черновик 2", receiverName: "Любофф", senderName: "Jet", senderId: 1, receiverId: 2, date: "22.11.1986", body: "bod-bod-bod"},
                2: {id: 9, subject: "Черновик 3", receiverName: "Гуру", senderName: "Jet", senderId: 1, receiverId: 2, date: "23.11.1986", body: "bod-bod-bod"},
            },
            trash: {
                0: {id: 10, folder: "inbox", unread: false, subject: "Удалённое Письмо 1", receiverName: "Эксперт", senderName: "Jet", senderId: 1, receiverId: 2, date: "21.11.1986", body: "bod-bod-bod"},
                1: {id: 11, folder: "outbox", unread: false, subject: "Удалённое Письмо 2", receiverName: "Любофф", senderName: "Jet", senderId: 1, receiverId: 2, date: "22.11.1986", body: "bod-bod-bod"},
                2: {id: 12, folder: "drafts", unread: true, subject: "Удалённое Письмо 3", receiverName: "Гуру", senderName: "Jet", senderId: 1, receiverId: 2, date: "23.11.1986", body: "bod-bod-bod"}
            }
        }';

        die($jsonTest);
    }

    /**
     * отдаёт информацию о письме и помечает его как прочитанное
     * @return json письмо
     */
    function get() {
        /*
        $jsonArr = array(
            '{
                id:'. $_POST['id'] .',
                folder: "inbox",
                unread: false,
                subject: "Калькуляция",
                senderName: "Эксперт",
                receiverName: "Jet",
                senderId: 1,
                receiverId: 2,
                date: "21.11.1986",
                body: "Greeting senderName <b>England</b>, sir!<br><br>Good bye."
            }',
            '{
                id:'. $_POST['id'] .',
                folder: "inbox",
                unread: false,
                subject: "Респект",
                senderName: "Вова Путин",
                receiverName: "Jet",
                senderId: 1,
                receiverId: 2,
                date: "21.11.1986",
                body: "<h1>Здаров, чувак!</h1><br><br>Bye."
            }',
            '{
                id:'. $_POST['id'] .',
                folder: "inbox",
                unread: false,
                subject: "Чмоки",
                senderName: "Блондинка",
                receiverName: "Jet",
                senderId: 1,
                receiverId: 2,
                date: "21.11.1986",
                body: "Greeting senderName England, <i>sir</i>!<br><br>Kiss you ;)"
            }'
        );

        die($jsonArr[rand(0, 2)]);
         */

        die('{result: {text: ""}}');
    }

    /**
     * создаёт и отправляет письмо
     * @return json параметры письма
     */
    function send_mail(){
        die(
            '{
                id: 20,
                folder: "outbox",
                unread: false,
                subject: "'.$_POST['subject'].'",
                senderName: "Jet",
                receiverName: "'.$_POST['receiverName'].'",
                senderId: 1,
                receiverId: 2,
                date: "21.11.1986",
                body: "'.$_POST['text'].'"
            }'
        );
    }

    /**
     * обновляет текст черновика
     * @return json параметры письма
     */
    function save_draft(){
        die(
            '{
                id: '.$_POST['id'].',
                folder: "drafts",
                unread: false,
                subject: "newsubj",
                senderName: "senderName",
                receiverName: "receiverName",
                senderId: 1,
                receiverId: 2, 
                date: "21.11.1986",
                body: "'.$_POST['text'].'"
            }'
        );
    }

    /**
     * добавляет письмо в базу данных
     * @return json ид вставленного письма
     */
    function add_mail()
    {
        $param['text']=htmlspecialchars($_POST['text']);
        $param['category']=htmlspecialchars($_POST['category']);
        if(!$param['category'])
            die(0);
        $param['title']=htmlspecialchars($_POST['title']);
        $param['receiverName']=(int)$_POST['receiverName'];
        //$param['date'] = formatRussianDate2MysqlDate($_POST['date']);
        die(json_encode($this->model->add_mail($param)));
    }

    /**
     * помещает письмо в папку trash
     * @return 0 1 успешность операции
     */
    function trash_mail()
    {
        die('{' . str_replace(',', ': true, ', $_POST['ids']) . ': true}');
        //$param['ids']=explode(',', $_POST['ids']);
        //die($this->model->del_mail($param));
    }

    /**
     * восстанавливает письмо из папки trash
     * @return 0 1 успешность операции
     */
    function restore()
    {
        die('{' . str_replace(',', ': true, ', $_POST['ids']) . ': true}');
        //$param['ids']=explode(',', $_POST['ids']);
        //die($this->model->del_mail($param));
    }

    /**
     * удаляет письмо из базы
     * @return 0 1 успешность операции
     */
    function del_mail()
    {
        die('{' . str_replace(',', ': true, ', $_POST['ids']) . ': true}');
        //$param['ids']=explode(',', $_POST['ids']);
        //die($this->model->del_mail($param));
    }

    /**
     * помечает письмо как прочитанное
     * @return 0 1 успешность операции
     */
    function read_mail()
    {
        $param['id']=$_POST['id'];
        die($this->model->read_mail($param));
    }

}
