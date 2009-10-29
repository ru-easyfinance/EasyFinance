<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера для модуля экспертов
 * @copyright http://easyfinance.ru/
 * @version SVN $Id: accounts.controller.php 232 2009-08-21 14:34:45Z rewle $
 */

class ExpertsList_Controller extends Template_Controller
{
    /**
     * Ссылка на класс Smarty
     * @var <Smarty>
     */
    private $tpl = null;

    /**
     * Ссылка на класс модель
     * @var <Accounts_Model>
     */
    private $model = null;

    /**
     * Конструктор класса
     * @return void
     */
    function __construct()
    {
        $this->tpl   = Core::getInstance()->tpl;
        $this->model = new Experts_Model();
    }

    /**
     * Индексная страница
     * @return void
     */
    function index()
    {
        $this->model->index();

        $this->tpl->assign('name_page', 'services/experts');

        $this->tpl->assign('desktop',($this->model->get_desktop()));//main div class
        $js_list = $this->model->js_list();//array js with indeficators;
    }

/////////////////////////////////users//////////////////////////////////////////

    /*
     * список всех экспертов с возможностью сортировки
     * @todo возможно стоит хранить этот список в куки?будующее
     */
    function getExpertsList()//expert light info//only user
    {
        die ('{
            1: {
                id: 1,
                fio: "Иванова Елена Геннадьевна",
                shortInfo: "Short info",
                rating: 4,
                votes: 12,
                smallPhoto: "/upload/photo.jpg"
            },
            2: {
                id: 2,
                fio: "Семецкий Антон Юрьевич",
                shortInfo: "Short info",
                rating: 3,
                votes: 10,
                smallPhoto: "/upload/cert1.jpg"
            },
            3: {
                id: 3,
                fio: "Скрудж Макдак",
                shortInfo: ";)",
                rating: 5,
                votes: 10,
                smallPhoto: "/upload/cert2.jpg"
            }
        }');

        /*
        $order = $_POST['order'];
        if (!$order)
            $order = 'id';
        die(json_encode($this->model->get_experts_list($order)));
         *
         */
    }

    function getProfileById()//expert foolinfo
    {
        die ('{
            id: 3,
            fio: "Скрудж Макдак",
            shortInfo: "Short info",
            fullInfo: "Full info.\n Multiline with <b>formatting</b>!",
            rating: 4,
            votes: 12,
            photo: "/upload/photo.jpg",
            smallPhoto: "/upload/photo.jpg",

            certificates: {
                1: {image: "/upload/cert1.jpg", smallImage: "/upload/cert1.jpg", comment: "Комментарий", processed: true, accepted: true, status: "Одобрен 21.12.2009"},
                2: {image: "/upload/cert2.jpg", smallImage: "/upload/cert2.jpg", comment: "Примечание", processed: false, accepted: false, status: "Рассматривается"},
                3: {image: "/upload/cert3.jpg", smallImage: "/upload/cert3.jpg", comment: "Заметка", processed: true, accepted: false, status: "Отвергут. Плохое качество."}
            },

            services: {
                1: {checked: true, title: "Консультация", comment: "Комментарий", price: 100, days: 2},
                2: {checked: true, title: "Тренинг", comment: "Комментарий", price: 10000, days: 10}
            }
        }');

        $expert_id = $_POST['expert_id'];
        if (!$expert_id)
            die();
        die(json_encode($this->model->get_expert($expert_id)));
    }
}