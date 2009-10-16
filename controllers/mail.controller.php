<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера для модуля почты
 * @copyright http://home-money.ru/
 * @version SVN $Id: $
 */

class Mail_Controller extends Template_Controller
{
    /**
     * Ссылка на класс модель
     * @var Mail_Model
     */
    private $model = null;


    private $tpl = null;
    /**
     * Конструктор класса
     * @return void
     */
    function __construct()
    {
        $this->tpl = Core::getInstance()->tpl;
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
            1: {folder: "inbox", unread: true, subject: "Входящее Письмо 1", from: "Эксперт", to: "Jet", date: "21.11.1986"},
            2: {folder: "inbox", unread: false, subject: "Входящее Письмо 2", from: "Любофф", to: "Jet", date: "22.11.1986"},
            3: {folder: "inbox", unread: false, subject: "Входящее Письмо 3", from: "Гуру", to: "Jet", date: "23.11.1986"},
        }';

        die($jsonTest);
     }

    /**
     * @return json список отправленных писем
     */
     function outbox(){
        $jsonTest = '{
            4: {folder: "outbox", subject: "Исходящее Письмо 1", to: "Эксперт", from: "Jet", date: "21.11.1986"},
            5: {folder: "outbox", subject: "Исходящее Письмо 2", to: "Любофф", from: "Jet", date: "22.11.1986"},
            6: {folder: "outbox", subject: "Исходящее Письмо 3", to: "Гуру", from: "Jet", date: "23.11.1986"},
        }';

        die($jsonTest);
     }

    /**
     * @return json список черновиков
     */
     function drafts(){
        $jsonTest = '{
            7: {folder: "drafts", subject: "Черновик 1", to: "Эксперт", from: "Jet", date: "21.11.1986"},
            8: {folder: "drafts", subject: "Черновик 2", to: "Любофф", from: "Jet", date: "22.11.1986"},
            9: {folder: "drafts", subject: "Черновик 3", to: "Гуру", from: "Jet", date: "23.11.1986"},
        }';

        die($jsonTest);
     }

    /**
     * @return json список удалённых писем
     */
     function trash(){
        $jsonTest = '{
            10: {folder: "trash", subject: "Удалённое Письмо 1", to: "Эксперт", from: "Jet", date: "21.11.1986"},
            11: {folder: "trash", subject: "Удалённое Письмо 2", to: "Любофф", from: "Jet", date: "22.11.1986"},
            12: {folder: "trash", subject: "Удалённое Письмо 3", to: "Гуру", from: "Jet", date: "23.11.1986"},
        }';

        die($jsonTest);
     }

    /**
     * отдаёт список писем во всех папка
     * @return json список писем
     */
    function mail_list()
    {
        //die(json_encode($this->model->mail_list()));

        $jsonTest = '{
            inbox: {
                1: {folder: "inbox", unread: true, subject: "Входящее письмо с очень длинным заголовком", from: "Дядя Стёпа Милиционер", to: "Jet", date: "21.11.1986"},
                2: {folder: "inbox", unread: false, subject: "Входящее Письмо 2", from: "Любофф", to: "Jet", date: "22.11.1986"},
                3: {folder: "inbox", unread: false, subject: "Входящее Письмо 3", from: "Гуру", to: "Jet", date: "23.11.1986"},
            },
            outbox: {
                4: {folder: "outbox", unread: false, subject: "Исходящее Письмо 1", to: "На Берлин", from: "Jet", date: "21.11.1986"},
                5: {folder: "outbox", unread: true, subject: "Исходящее Письмо 2", to: "Любимой", from: "Jet", date: "22.11.1986"},
                6: {folder: "outbox", unread: false, subject: "Исходящее Письмо 3", to: "В канцелярию", from: "Jet", date: "23.11.1986"},
            },
            drafts: {
                7: {folder: "drafts", subject: "Черновик 1", to: "Эксперт", from: "Jet", date: "21.11.1986"},
                8: {folder: "drafts", subject: "Черновик 2", to: "Любофф", from: "Jet", date: "22.11.1986"},
                9: {folder: "drafts", subject: "Черновик 3", to: "Гуру", from: "Jet", date: "23.11.1986"},
            },
            trash: {
                10: {folder: "inbox", unread: false, subject: "Удалённое Письмо 1", to: "Эксперт", from: "Jet", date: "21.11.1986"},
                11: {folder: "outbox", unread: false, subject: "Удалённое Письмо 2", to: "Любофф", from: "Jet", date: "22.11.1986"},
                12: {folder: "drafts", unread: true, subject: "Удалённое Письмо 3", to: "Гуру", from: "Jet", date: "23.11.1986"}
            }
        }';

        die($jsonTest);
    }

    /**
     * отдаёт информацию о письме и помечает его как прочитанное
     * @return json письмо
     */
    function mail() {
        $jsonArr = array(
            '{
                id:'. $_POST['id'] .',
                folder: "inbox",
                unread: false,
                subject: "Калькуляция",
                from: "Эксперт",
                to: "Jet",
                date: "21.11.1986",
                text: "Greeting from <b>England</b>, sir!<br><br>Good bye."
            }',
            '{
                id:'. $_POST['id'] .',
                folder: "inbox",
                unread: false,
                subject: "Респект",
                from: "Вова Путин",
                to: "Jet",
                date: "21.11.1986",
                text: "<h1>Здаров, чувак!</h1><br><br>Bye."
            }',
            '{
                id:'. $_POST['id'] .',
                folder: "inbox",
                unread: false,
                subject: "Чмоки",
                from: "Блондинка",
                to: "Jet",
                date: "21.11.1986",
                text: "Greeting from England, <i>sir</i>!<br><br>Kiss you ;)"
            }'
        );

        die($jsonArr[rand(0, 2)]);
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
                from: "Jet",
                to: "'.$_POST['to'].'",
                date: "21.11.1986",
                text: "'.$_POST['text'].'"
            }'
        );
    }

    /**
     * создаёт черновик письма
     * @return json параметры письма
     */
    function create_draft(){
        die(
            '{
                id: 20,
                folder: "drafts",
                unread: false,
                subject: "'.$_POST['subject'].'",
                from: "Jet",
                to: "'.$_POST['to'].'",
                date: "21.11.1986",
                text: "'.$_POST['text'].'"
            }'
        );
    }

    /**
     * обновляет текст черновика
     * @return json параметры письма
     */
    function edit_draft(){
        die(
            '{
                id: '.$_POST['id'].',
                folder: "drafts",
                unread: false,
                subject: "newsubj",
                from: "From",
                to: "To",
                date: "21.11.1986",
                text: "'.$_POST['text'].'"
            }'
        );
    }

    /**
     * обновляет черновик письма и отправляет его
     * @return json параметры письма
     */
    function send_draft(){
        die(
            '{
                id: '.$_POST['id'].',
                folder: "outbox",
                unread: false,
                subject: "newsubj",
                from: "From",
                to: "To",
                date: "21.11.1986",
                text: "'.$_POST['text'].'"
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
        $param['to']=(int)$_POST['to'];
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
    function restore_mail()
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
