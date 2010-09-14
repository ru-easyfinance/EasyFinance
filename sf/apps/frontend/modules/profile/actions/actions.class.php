<?php
require_once(dirname(__FILE__) . '/../../../lib/helper/myDateTimezoneHelper.php');

/**
 * Профиль пользователя
 */
class profileActions extends sfActions
{
    /**
     * Форма редактирования
     */
    public function executeIndex(sfRequest $request)
    {
        // TODO дергать профиль для res'а, когда он будет динамическим
        // $user = $this->getUser()->getUserRecord();

        // TODO форму профиля рисовать sfForm + валидация данных

        return sfView::SUCCESS;
    }


    /**
     * Сохранить профиль пользователя
     */
    public function executeSave(sfRequest $request)
    {
        $user = $this->getUser()->getUserRecord();
        $this->form = new UserProfileForm($user);

        $this->getResponse()->setHttpHeader('Content-Type', 'application/json; charset=utf-8');

        $postParameters = $request->getPostParameters();
        $formParameters = array();

        $fields = array(
            'login'             => 'user_login',
            'mailIntegration'   => 'user_service_mail',
            'nickname'          => 'name',
            'mail'              => 'user_mail',
            'pass'              => 'password',
            'newpass'           => 'password_new',
            'getNotify'         => 'notify',
        );

        // поставить куку на гайд...
        if ((int) $postParameters['guide']) {
            // поганый хардкод
            $this->getResponse()->setCookie('guide', 'uyjsdhf', 0, COOKIE_PATH, COOKIE_DOMEN, false);
        // убить куку
        } else {
            $this->getResponse()->setCookie('guide', '', time() - 3600, COOKIE_PATH, COOKIE_DOMEN, false);
        }

        foreach ($fields as $parameter => $field) {
            if (array_key_exists($parameter, $postParameters)) {
                $formParameters[$field] = $postParameters[$parameter];
            }
        }

        $this->form->bind($formParameters, array());
        if ($this->form->isValid()) {
            $this->form->save();

            return $this->renderText(json_encode(array('result' => array('text' => 'Данные успешно сохранены'))));
        }

        $err = $this->form->getErrorSchema();
        $string = '';
        foreach ($err as $errors) {
            if ($errors instanceof sfValidatorErrorSchema) {
                foreach ($errors as $code => $message) {
                    $string .= $message->getMessage() . "<br /><br />\n";
                }
            } elseif ($errors instanceof sfValidatorError) {
                $string .= $errors->getMessage() . "<br /><br />\n";
            } else {
                $string .= $errors . "<br /><br />\n";
            }
        }

        $this->getResponse()->setStatusCode(400);
        return $this->renderText(json_encode(array('error' => array('text' => $string))));
    }


    /**
     * Сохранить настройки напоминаний
     */
    public function executeSaveReminders(sfRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $user = $this->getUser()->getUserRecord();
        $request_params = $request->getParameterHolder()->getAll();

        // отображение имён параметров, пришедших в запросе, на имена полей в БД
        $fields_map = array(
            'timezone'       => 'time_zone',
            'smsPhone'       => 'sms_phone',
            'mailEnabled'    => 'reminder_mail_default_enabled',
            'smsEnabled'     => 'reminder_sms_default_enabled',
            'mailDaysBefore' => 'reminder_mail_days',
            'mailHour'       => 'reminder_mail_hour',
            'mailMinutes'    => 'reminder_mail_minutes',
            'smsDaysBefore'  => 'reminder_sms_days',
            'smsHour'        => 'reminder_sms_hour',
            'smsMinutes'     => 'reminder_sms_minutes',
        );

        $reminders_array = array(); // сюда складываем настройки оповещений для json-ответа

        foreach($fields_map as $parameter_name => $field_name) {
            if(isset($request_params[ $parameter_name ])) {

                /**
                 * в запросе значения checkbox'ов приходят строками ('true', 'false'),
                 * надо менять на integer, чтобы корректно писалось в базу
                 */
                if($request_params[$parameter_name] == 'true') {
                    $request_params[$parameter_name] = 1;
                } elseif ($request_params[$parameter_name] == 'false') {
                    $request_params[$parameter_name] = 0;
                }

                if ('timezone' == $parameter_name) {
                    if (!isset(myDateTimezoneHelper::$zones[$request_params[$parameter_name]])) {
                        continue;
                    }
                }

                $user->set($field_name, $request_params[$parameter_name]);
                $reminders_array[$parameter_name] = $request_params[$parameter_name];
            }
        }

        $user->save();

        $this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');

        $ret = array(
            'result' => array(
                'text' => 'Настройки напоминаний сохранены'
             ),
             'reminders' => $reminders_array
        );
        return $this->renderText(json_encode($ret));
    }


    /**
     * Загрузить данные пользователя, AJAX
     */
    public function executeLoadUserData(sfRequest $request)
    {
        $this->forward404Unless($request->isXmlHttpRequest());

        $result = array(); // будем возвращать
        $user = $this->getUser()->getUserRecord();

        $subscribtion = Doctrine::getTable('ServiceSubscription')->getActiveUserServiceSubscription(
            $user->getId(),
            Service::SERVICE_SMS
        );


        # Svel:
        #       а не лучше ли мапить это во вью?
        $result['profile'] = array(
            'login'     => $user->getUserLogin(),
            'name'      => $user->getUserName(),
            'mail'      => $user->getUserMail(),
            'timezone'  => $user->getTimeZone(),
            'integration' => array(
                'email' => str_replace('@mail.easyfinance.ru', '', $user->getUserServiceMail()),
            ),
            'reminders' => array(
                'enabled'       => (is_object($subscribtion)) ? 1 : 0,
                'mailEnabled'   => $user->getReminderMailDefaultEnabled(),
                'mailDaysBefore'=> $user->getReminderMailDays(),
                'mailHour'      => $user->getReminderMailHour(),
                'mailMinutes'   => $user->getReminderMailMinutes(),
                'smsPhone'      => $user->getSmsPhone(),
                'smsEnabled'    => $user->getReminderSmsDefaultEnabled(),
                'smsDaysBefore' => $user->getReminderSmsDays(),
                'smsHour'       => $user->getReminderSmsHour(),
                'smsMinutes'    => $user->getReminderSmsMinutes()
            )
        );

        $this->getResponse()->setHttpHeader('Content-Type','application/json; charset=utf-8');
        return $this->renderText(json_encode($result));
    }

}
