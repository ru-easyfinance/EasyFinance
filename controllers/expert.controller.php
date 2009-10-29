<?php if (!defined('INDEX')) trigger_error("Index required!",E_USER_WARNING);
/**
 * Класс контроллера для модуля экспертов
 * @copyright http://easyfinance.ru/
 * @version SVN $Id: accounts.controller.php 232 2009-08-21 14:34:45Z rewle $
 */

class Expert_Controller extends Template_Controller
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

        $this->tpl->assign('name_page', 'expert/expert');

        $this->tpl->assign('desktop',($this->model->get_desktop()));//main div class
        $js_list = $this->model->js_list();//array js with indeficators;



    }

    function editInfo()
    {
        die('{shortInfo: "'.$_POST['profile-short'].'", fullInfo: "'.$_POST['profile-long'].'"}');
    }

//////////////////////////////////experts///////////////////////////////////////

    function get_desktop_fields()//only experts//todo after dl
    {
        return false;
    }

    /** обновляет значения для панельки эксперта
     *
     */
    function get_desktop_field()
    {
        $field_id = $_POST['field_id'];
        if (!$field_id)
            die();
        die(json_encode($this->model->get_desktop_field($field_id)));
    }

    /** редактирование профиля эксперта
     *
     */
    function update_expert()
    {
        $param['mini_desc'] = $_POST['mini_desc'];//str
        $param['description'] = $_POST['description'];//str
        $param['services'] = $_POST['services'];////array
        $param['themes'] = $_POST['themes'];//array
        $param['sertificat'] = $_POST['sertificat'];//str
        die($this->model->update_expert());
    }


    function add()//reit question etc//@todo after mail/
    {

    }

/////////////////////////////////users//////////////////////////////////////////

    /*
     * список всех экспертов с возможностью сортировки
     * @todo возможно стоит хранить этот список в куки?будующее
     */
    function get_experts_list()//expert light info//only user
    {
        $order = $_POST['order'];
        if (!$order)
            $order = 'id';
        die(json_encode($this->model->get_experts_list($order)));
    }

    /*
     * полная инфа о эксперте.возможна сортировка.
     */
    function getProfile()//expert foolinfo
    {
        die ('{
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
                2: {checked: false, title: "Тренинг", comment: "Комментарий", price: 0, days: 0}
            }
        }');

        $expert_id = $_POST['expert_id'];
        if (!$expert_id)
            die();
        die(json_encode($this->model->get_expert($expert_id)));
    }

    function deletePhoto(){
        die ('{
            photo: ""
        }');
    }

    function uploadPhoto(){
        die ('{
            photo: "/upload/photo.jpg"
        }');
    }

    function getCertificates(){
        $jsonTest = '{
            1: {image: "/upload/cert1.jpg", smallImage: "/upload/cert1.jpg", comment: "Комментарий", processed: true, accepted: true, status: "Одобрен 21.12.2009"},
            2: {image: "/upload/cert2.jpg", smallImage: "/upload/cert2.jpg", comment: "Примечание", processed: false, accepted: false, status: "Рассматривается"},
            3: {image: "/upload/cert3.jpg", smallImage: "/upload/cert3.jpg", comment: "Заметка", processed: true, accepted: false, status: "Отвергут. Плохое качество."}
        }';

        die($jsonTest);
    }

    function deleteCertificate(){
        $jsonTest = '{
            1: {image: "/upload/cert1.jpg", smallImage: "/upload/cert1.jpg", comment: "Комментарий", processed: true, accepted: true, status: "Одобрен 21.12.2009"},
            2: {image: "/upload/cert2.jpg", smallImage: "/upload/cert2.jpg", comment: "Примечание", processed: false, accepted: false, status: "Рассматривается"}
        }';

        die($jsonTest);
    }

    function addCertificate(){
        $jsonTest = '{
            1: {image: "/upload/cert1.jpg", smallImage: "/upload/cert1.jpg", comment: "Комментарий", processed: true, accepted: true, status: "Одобрен 21.12.2009"},
            2: {image: "/upload/cert2.jpg", smallImage: "/upload/cert2.jpg", comment: "Примечание", processed: false, accepted: false, status: "Рассматривается"},
            3: {image: "/upload/cert3.jpg", smallImage: "/upload/cert3.jpg", comment: "Заметка", processed: true, accepted: false, status: "Отвергут. Плохое качество."},
            4: {image: "/upload/photo.jpg", smallImage: "/upload/photo.jpg", comment: "Заметка", processed: false, accepted: false, status: "Рассматривается"}
        }';

        die($jsonTest);
    }

    function editServices(){
        $jsonTest = '{
            1: {checked: true, title: "Консультация", comment: "Комментарий", price: 300, days: 2},
            2: {checked: true, title: "Тренинг", comment: "Комментарий", price: 10000, days: 10}
        }';

        die($jsonTest);
    }
}