<?php

if(!defined('INDEX'))
    trigger_error("Index required!", E_USER_WARNING);

/**
 * Класс контроллера для страницы интеграции
 * @copyright http://easyfinance.ru/
 */
class Integration_Controller extends _Core_Controller
{

    /**
     * Ссылка на класс User
     * @var User
     */
    private $_user = null;

    /**
     * Конструктор класса
     * @return void
     */
    function __init()
    {
        $this->_user = Core::getInstance()->user;
        $this->tpl->assign('name_page', 'integration/amt');

    }

    /**
     * Индексная страница
     * @param $args array mixed
     * @return void
     */
    function index()
    {

    }

    /**
     * Привязываем счёт к счёту в банке
     *
     * @return void
     */
    function binding()
    {
        if ($this->_user->getId() == 0) {
            $errorMessage = 'Необходимо авторизироваться';
        }

        if (isset($_POST['account_id'])) {

            $account_id = (int)$_POST['account_id'];

            if ($account_id <= 0) {

                $errorMessage = 'Неверный идентификатор счёта';

            }

        } else {

            $errorMessage = 'Необходимо указать счёт';

        }

        if (!isset($errorMessage)) {
            $debetCard = new Account_DebetCard();

            if (!$debetCard->binding($account_id)) {

                $errorMessage = 'Ошибка при привязывании счёта';

            }
        }

        if (isset($errorMessage)) {

            $this->renderJsonError($errorMessage);

        } else {

            $this->renderJsonSuccess('Счёт успешно привязан');

        }
    }

    /**
     * Печать анкеты
     */
    public function anketa()
    {
        if (!empty($_POST['anketa'])) {

            $data = $this->_preparePrintData();

            $info = 'ФИО: ' .$data['wz_surname'] .' '. $data['wz_name'] .' '. $data['wz_midname']. "\n" .
                'e-mail: ' . $data['wz_mail'] .' моб. тел.: ' . $data['wz_phone_mob'];
            Logs::write(Core::getInstance()->user, 'amt', $info);

            require_once(SYS_DIR_ROOT.'/core/external/pdfAmt/pdf.lib.php');
            createPDF(
                $lname = $data['wz_surname'],
                $fname = $data['wz_name'],
                $mname = $data['wz_midname'],
                $lname_lat = $data['wz_surname_translit'],
                $fname_lat = $data['wz_name_translit'],
                $birth_date = $data['wz_birthdate'],
                $birth_place = $data['wz_birthplace'],
                $gender = $data['wz_sex'],
                $citizenship = $data['wz_citizenship'],
                $inn = $data['wz_inn'],
                $country = $data['wz_reg_country'],
                $zip = $data['wz_reg_index'],
                $region = $data['wz_reg_region'],
                $city = $data['wz_reg_city'],
                $street = $data['wz_reg_street'],
                $house = $data['wz_reg_house'],
                $building = $data['wz_reg_building'],
                $app = $data['wz_reg_appartment'],
                $country_reg = $data['wz_actual_country'],
                $zip_reg = $data['wz_actual_index'],
                $region_reg = $data['wz_actual_region'],
                $city_reg = $data['wz_actual_city'],
                $street_reg = $data['wz_actual_street'],
                $house_reg = $data['wz_actual_house'],
                $building_reg = $data['wz_actual_building'],
                $app_reg = $data['wz_actual_appartment'],
                $passport_serie = $data['wz_rf_id_series'],
                $passport_number = $data['wz_rf_id_number'],
                $passport_given = $data['wz_rf_id_organisation'],
                $passport_code = $data['wz_rf_id_organisation_code'],
                $passport_date = $data['wz_rf_id_date'],
                $doc_title = $data['wz_foreign_id_name'],
                $doc_serie = $data['wz_foreign_id_series'],
                $doc_number = $data['wz_foreign_id_number'],
                $doc_given = $data['wz_foreign_id_organisation'],
                $doc_date = $data['wz_foreign_id_date'],
                $doc_valid = $data['wz_foreign_id_expire'],
                $contact_phone = $data['wz_phone_home'],
                $contact_email = $data['wz_mail'],
                $contact_mobile = $data['wz_phone_mob'],
                $contact_other = $data['wz_other_contacts'],
                $work_company = $data['wz_work_name'],
                $work_title = $data['wz_work_position'],
                $work_address = $data['wz_work_address'],
                $work_phone = $data['wz_work_phone'],
                $card_mode = $data['wz_card_is_main'],
                $card_currency = $data['wz_card_currency'],
                $card_type = $data['wz_card_type'],
                $card_urgency = $data['wz_card_rush'],
                $card_sms = $data['wz_card_sms_info'],
                $card_receipt_office = @$data['wz_card_account_info_to_office'],
                $card_receipt_email = '',
                $card_email = str_replace("@mail.easyfinance.ru", "", $data['wz_card_account_mail']),
                $add_name = $data['wz_addit_card_owner'],
                $add_number = $data['wz_addit_card_sks_number'],
                $add_limit = $data['wz_addit_card_limit'],
                $add_14_type = '',
                $add_14_given = @$data['wz_addit_card14_organisation'],
                $password = $data['wz_password']
            );
        }
        exit();
    }

    /**
     * Подготовка данных для печати в анкете
     * @return array
     */
    private function _preparePrintData()
    {
        $indexes = array_fill_keys(array(
            'wz_surname',
            'wz_name',
            'wz_midname',
            'wz_surname_translit',
            'wz_name_translit',
            'wz_birthdate',
            'wz_birthplace',
            'wz_sex',
            'wz_citizenship',
            'wz_inn',
            'wz_reg_country',
            'wz_reg_index',
            'wz_reg_region',
            'wz_reg_city',
            'wz_reg_street',
            'wz_reg_house',
            'wz_reg_building',
            'wz_reg_appartment',
            'wz_actual_country',
            'wz_actual_index',
            'wz_actual_region',
            'wz_actual_city',
            'wz_actual_street',
            'wz_actual_house',
            'wz_actual_building',
            'wz_rf_id_series',
            'wz_rf_id_number',
            'wz_rf_id_organisation',
            'wz_rf_id_organisation_code',
            'wz_rf_id_date',
            'wz_foreign_id_name',
            'wz_foreign_id_series',
            'wz_foreign_id_number',
            'wz_foreign_id_organisation',
            'wz_foreign_id_date',
            'wz_foreign_id_expire',
            'wz_phone_home',
            'wz_mail',
            'wz_phone_mob',
            'wz_other_contacts',
            'wz_work_name',
            'wz_work_position',
            'wz_work_address',
            'wz_work_phone',
            'wz_card_is_main',
            'wz_card_currency',
            'wz_card_type',
            'wz_card_rush',
            'wz_card_sms_info',
            'wz_card_account_info_to_office',
            'wz_card_account_mail',
            'wz_addit_card_owner',
            'wz_addit_card_sks_number',
            'wz_addit_card_limit',
            'wz_addit_card14_organisation',
            'wz_password',
        ), null);

        $data = array_merge($indexes, $_POST['anketa']);

        foreach ($data as $key => $value) {
            $data[$key] = htmlspecialchars(urldecode($value), ENT_QUOTES);
        }
        return $data;
    }

}
