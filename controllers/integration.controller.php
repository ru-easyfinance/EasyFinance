<?php
/**
 * Класс контроллера для страницы интеграции
 *
 * @copyright http://easyfinance.ru/
 */
class Integration_Controller extends _Core_Controller
{

    /**
     * Ссылка на класс User
     *
     * @var User
     */
    private $_user = null;

    /**
     * Конструктор класса
     *
     * @return void
     */
    function __init()
    {
        $this->_user = Core::getInstance()->user;
        $this->tpl->assign('name_page', 'integration/amt');

    }

    /**
     * Индексная страница
     *
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
        Logs::write(Core::getInstance()->user, 'amt', null);

        if (!isset($_POST['anketa'])) {
            throw new Exception('No personal data');
        }

        $data = $this->_prepareData($_POST['anketa']);

        $parsedData = $this->_parseData($data);

        $this->_sendEmail($parsedData);

        $this->renderJsonSuccess("Анкета успешно отправлена!");
    }

    /**
     * Отправляет почту в банк
     * @param array $data
     * @return bool
     */
    private function _sendEmail(array $data)
    {
        $message = Swift_Message::newInstance()
            ->setSubject('Анкета АМТ')
            ->setFrom(array('info@easyfinance.ru' => 'Easy Finance'))
            //->setTo(array('card.statement@amtbank.com'))
            ->setTo(array('test@easyfinance.ru'))
            ->setBody($this->_makeBodyEmail($data))
            ->addPart($this->_makePartXmlEmail($data), 'text/xml');
        return Core::getInstance()->mailer->send($message);
    }

    /**
     * Создаёт тело письма из поступивших данных
     *
     * @param array $data
     * @return string
     */
    private function _makeBodyEmail(array $data)
    {
        $string = "";
        foreach ($data as $parentKey => $parentValue) {
            $string .= "\n[{$parentKey}]\n";
            foreach ($parentValue as $childKey => $childValue) {
                $string .= "{$childKey} = {$childValue}\n";
            }
        }
        return $string;
    }

    /**
     * Создаёт XML документ для альтернативной части письма
     *
     * @param array $data
     * @return string
     */
    private function _makePartXmlEmail(array $data)
    {
        $writer = new XMLWriter();
        $writer->openMemory();
        $writer->startDocument('1.0', 'utf-8');
        $writer->startElement('registration_form');
        foreach ($data as $parentKey => $parentValue) {
            $writer->startElement($parentKey);
            foreach ($parentValue as $childKey => $childValue) {
                $writer->writeElement($childKey, $childValue);
            }
            $writer->endElement();
        }
        $writer->endElement();
        return $writer->flush();
    }

    /**
     * Подготавливает пришедшие данные от пользователя и возвращает их
     *
     * @param array $data
     * @return array
     */
    private function _prepareData(array $data)
    {
        // Массив с ожидаемыми от клиента полями
        $expected = array(
            'wz_surname','wz_name','wz_midname','wz_surname_translit','wz_name_translit','wz_birthdate',
            'wz_birthplace','wz_sex','wz_citizenship','wz_inn','wz_reg_country','wz_reg_index','wz_reg_region',
            'wz_reg_city','wz_reg_street','wz_reg_house','wz_reg_building','wz_reg_appartment','wz_actual_country',
            'wz_actual_index','wz_actual_region','wz_actual_city','wz_actual_street','wz_actual_house',
            'wz_actual_building','wz_actual_appartment','wz_doc_title','wz_rf_id_series','wz_rf_id_number',
            'wz_rf_id_organisation','wz_rf_id_organisation_code','wz_rf_id_date','wz_rf_expiration_date',
            'wz_residence_title','wz_foreign_id_series','wz_foreign_id_number','wz_foreign_id_date',
            'wz_foreign_id_expire','wz_migration_id_number','wz_migration_id_date','wz_migration_id_expire',
            'wz_phone_home','wz_phone_mob','wz_other_contacts','wz_work_name','wz_work_position','wz_work_address',
            'wz_work_phone','wz_card_is_main','wz_card_currency','wz_card_expiration','wz_card_type','wz_card_rush',
            'wz_card_sms_info','wz_card_account_info_to','wz_card_account_mail','wz_addit_card_owner',
            'wz_addit_card_sks_number','wz_addit_card_limit','wz_addit_card14_agreement','wz_addit_card14_document',
            'wz_addit_card14_organisation','wz_password'
        );

        $expected = array_combine(array_values($expected), array_fill(0, count($expected), ''));

        return array_merge($expected, $data);
    }

    /**
     * Разбираем пришедшие от пользователя данные
     *
     * @param array $data
     * @return array
     */
    private function _parseData(array $data)
    {
        $anketa = array();
        $currencies =  array('RUR', 'USD', 'EUR');

        // Личные данные пользователя
        $anketa['personal']['last_name']                = (string)$data['wz_surname'];
        $anketa['personal']['last_name_lat_trans']      = (string)$data['wz_surname_translit'];
        $anketa['personal']['first_name']               = (string)$data['wz_name'];
        $anketa['personal']['first_name_lat_trans']     = (string)$data['wz_name_translit'];
        $anketa['personal']['second_name']              = (string)$data['wz_midname'];
        $anketa['personal']['control_name']             = (string)$data['wz_password'];
        $anketa['personal']['birth_place']              = (string)$data['wz_birthplace'];
        $anketa['personal']['birth_date']               = (string)$data['wz_birthdate'];
        $anketa['personal']['gender']                   = ((int)$data['wz_sex'] == 1)? "Ж" : "М" ;
        $anketa['personal']['nationality']              = (string)$data['wz_citizenship'];
        $anketa['personal']['inn']                      = (string)$data['wz_inn'];

        // Адрес прописки / регистрации
        $anketa['registration_address']['country']      = (string)$data['wz_reg_country'];
        $anketa['registration_address']['region']       = (string)$data['wz_reg_region'];
        $anketa['registration_address']['city']         = (string)$data['wz_reg_city'];
        $anketa['registration_address']['index']        = (string)$data['wz_reg_index'];
        $anketa['registration_address']['street']       = (string)$data['wz_reg_street'];
        $anketa['registration_address']['house_number'] = (string)$data['wz_reg_house'];
        $anketa['registration_address']['bulk_number']  = (string)$data['wz_reg_building'];
        $anketa['registration_address']['room_number']  = (string)$data['wz_reg_appartment'];

        // Адрес проживания (для переписки)
        $anketa['live_address']['country']              = (string)$data['wz_actual_country'];
        $anketa['live_address']['region']               = (string)$data['wz_actual_region'];
        $anketa['live_address']['city']                 = (string)$data['wz_actual_city'];
        $anketa['live_address']['index']                = (string)$data['wz_actual_index'];
        $anketa['live_address']['street']               = (string)$data['wz_actual_street'];
        $anketa['live_address']['house_number']         = (string)$data['wz_actual_house'];
        $anketa['live_address']['bulk_number']          = (string)$data['wz_actual_building'];
        $anketa['live_address']['room_number']          = (string)$data['wz_actual_appartment'];

        // Данные основного документа клиента
        $anketa['main_doc']['title']                    = (string)$data['wz_doc_title'];
        $anketa['main_doc']['serial']                   = (string)$data['wz_rf_id_series'];
        $anketa['main_doc']['number']                   = (string)$data['wz_rf_id_number'];
        $anketa['main_doc']['who_delivery']             = (string)$data['wz_rf_id_organisation'];
        $anketa['main_doc']['issue_date']               = (string)$data['wz_rf_id_date'];
        $anketa['main_doc']['expiration_date']          = (string)$data['wz_rf_expiration_date'];
        $anketa['main_doc']['unit_code']                = (string)$data['wz_rf_id_organisation_code'];

        // Миграционная карта
        $anketa['migratory_card']['number']             = (string)$data['wz_migration_id_number'];
        $anketa['migratory_card']['issue_date']         = (string)$data['wz_migration_id_date'];
        $anketa['migratory_card']['expiration_date']    = (string)$data['wz_migration_id_expire'];

        // Данные документа подтверждающего право на жительство
        $anketa['residence_doc']['title']               = (string)$data['wz_residence_title'];
        $anketa['residence_doc']['serial']              = (string)$data['wz_foreign_id_series'];
        $anketa['residence_doc']['number']              = (string)$data['wz_foreign_id_number'];
        $anketa['residence_doc']['issue_date']          = (string)$data['wz_foreign_id_date'];
        $anketa['residence_doc']['expiration_date']     = (string)$data['wz_foreign_id_expire'];

        // Контактная информация
        $anketa['contacts']['home_phone']               = (string)$data['wz_phone_home'];
        $anketa['contacts']['mobile_phone']             = (string)$data['wz_phone_mob'];
        $anketa['contacts']['email']                    = (string)$data['wz_card_account_mail'];
        $anketa['contacts']['other']                    = (string)$data['wz_other_contacts'];

        // Место работы
        $anketa['work_place']['organisation_name']      = (string)$data['wz_work_name'];
        $anketa['work_place']['organization_address']   = (string)$data['wz_work_address'];
        $anketa['work_place']['character_position']     = (string)$data['wz_work_position'];
        $anketa['work_place']['phone']                  = (string)$data['wz_work_phone'];

        // Международная банковская карта
        $anketa['card']['is_main']                      = ((string)$data['wz_card_is_main'])? 'true' : 'false';
        $anketa['card']['currency']                     = @$currencies[$data['wz_card_currency']];
        $anketa['card']['type']                         = (string)$data['wz_card_type'];
        $anketa['card']['is_planning']                  = ($data['wz_card_rush'])? 'true' : 'false';
        $anketa['card']['expiration_time']              = (string)$data['wz_card_expiration'];
        $anketa['card']['informSms']                    = ($data['wz_card_sms_info'])? 'true' : 'false';
        $anketa['card']['report_type']                  = (string)$data['wz_card_account_info_to'];
        $anketa['card']['report_email']                 = (string)$data['wz_card_account_mail'];

        // Информация по доп. карте
        $anketa['additionalCard']['lastName']           = (string)$data['wz_addit_card_lastname'];
        $anketa['additionalCard']['firstName']          = (string)$data['wz_addit_card_firstname'];
        $anketa['additionalCard']['secondName']         = (string)$data['wz_addit_card_secondname'];
        $anketa['additionalCard']['mainSKS']            = (string)$data['wz_addit_card_sks_number'];
        $anketa['additionalCard']['choiseLimits']       = (string)$data['wz_addit_card_limit'];
//        $anketa['additionalCard']['document']           = $data['']; //@TODO

        // Если заказывается дополнительная карта для лица в возрасте до 14 лет
        $anketa['additionalDocument']['type']           = (string)$data['wz_addit_card14_document'];
        $anketa['additionalDocument']['who_delivery']   = (string)$data['wz_addit_card14_organisation'];
        $anketa['additionalDocument']['delivery_date']  = (string)$data['wz_addit_card14_date'];

        return $anketa;
    }

}
