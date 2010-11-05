<?php
require_once dirname(__FILE__)
    . '/../../../../api/modules/sync/lib/helper/SyncHelper.php';
/**
 * Интеграция с банками
 *
 * @package EasyFinance
 * @author  Anton Minin <anton.a.minin@gmail.com>
 */
class easybankActions extends myBaseFrontendJsonActions
{
    /**
     * Пусто
     */
    public function executeIndex(sfRequest $request)
    {
        return sfView::NONE;
    }

    /**
     * Приём анкеты Сити банка
     */
    public function executeCitiCashBackApplication(sfRequest $request)
    {
        $form = new CitiBankApplicationForm();
        $form->bind($request->getPostParameters());

        if ($form->isValid()) {
            $this->_sendEmail($form->getValues());
            return $this->renderJsonSuccess('Анкета успешно отправлена');
        }

        $errors = $form->getErrorSchema()->getErrors();
        $errorMessages = array();

        //TODO: refactor: все сообщения русским языком должны создаваться на клиенте,
        //сервер должен слать только код поля (например, "email") и сообщение валидатора

        $humanFieldNames = array(
            "email" => "Email",
            "birthday" => "Дата рождения",
            "name" => "Имя",
            "patronymic" => "Отчество",
            "surname" => "Фамилия",
            "mobile_phone" => "Мобильный телефон",
        );

        foreach ($errors as $fieldName => $error) {
            $errorMessages[] = sprintf("%s", $humanFieldNames[$fieldName]);
        }

        return $this->renderJsonError("Пожалуйста, исправьте заполнение полей: \n" . implode(', ', $errorMessages));
    }

    /**
     * Отправляет почту в банк
     * @param array $data
     * @return bool
     */
    private function _sendEmail(array $data)
    {
        $message = Swift_Message::newInstance()
            ->setSubject('Анкета Citi')
            ->setFrom(array('info@easyfinance.ru' => 'Easy Finance'))
            ->setTo(sfConfig::get('app_easybank_mailCardCiti'))
            ->setBody($this->_makeBodyEmail($data))
            ->addPart($this->_makePartXmlEmail($data), 'text/xml');
        return $this->getMailer()->send($message);
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
        foreach ($data as $fieldName => $fieldValue) {
            $string .= sprintf("%s = %s\n", $fieldName, $fieldValue);
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
        $out = '<?xml version="1.0" encoding="UTF-8"?>';
        foreach ($data as $fieldName => $fieldValue) {
            $out .= sprintf("<%s>%s</%s>", $fieldName, esc_xml($fieldValue), $fieldName);
        }

        $out .= '</registration_form>';
        return $out;
    }
}
