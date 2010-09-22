<?php
/**
 * Форма: анкета сити банка
 *
 * @package    EasyFinance
 * @subpackage form
 * @author     Anton Minin <anton.a.minin@gmail.com>
 */
class CitiBankApplicationForm extends BaseForm
{
    public function configure()
    {
        $cities = array(
            'Москва',
            'Московская область',
            'Санкт-Петербург',
            'Ленинградская область',
            'Екатеринбург',
            'Самара',
            'Ростов-на-Дону',
            'Уфа',
            'Волгоград',
            'Новосибирск',
            'Казань',
            'Нижний Новгород'
        );

        $employments = array(
            'Работаю по постоянному контракту',
            'Работаю по временному трудовому соглашению',
            'Военнослужащий',
            'Владелец предприятия',
            'Агент на комиссионном договоре',
            'Индивидуальный предприниматель',
            'Не работаю'
        );

        $this->disableLocalCSRFProtection();

        $years = range(date('Y') - 22, date('Y') - 60, 1);

        $this->setWidgets(
            array(
                'city' => new sfWidgetFormChoice(
                    array(
                        'label'   => 'Город',
                        'choices' => array_combine($cities, $cities)
                    )
                ),
                'employment' => new sfWidgetFormChoice(
                    array(
                        'label'   => 'Форма трудоустройства',
                        'choices' => array_combine($employments, $employments)
                    )
                ),
                'birthday' => new sfWidgetFormDate(
                    array(
                        'label'  => 'Дата рождения',
                        'format' => '%year%-%month%-%day%',
                        'years'  => array_combine($years, $years),
                        'can_be_empty' => false
                    )
                ),
                'name' => new sfWidgetFormInputText(
                    array('label' => 'Имя')
                ),
                'patronymic' => new sfWidgetFormInputText(
                    array('label' => 'Отчество')
                ),
                'surname' => new sfWidgetFormInputText(
                    array('label' => 'Фамилия')
                ),
                'mobile_code' => new sfWidgetFormInputText(
                    array('label' => 'Код мобильного телефона')
                ),
                'mobile_phone' => new sfWidgetFormInputText(
                    array('label' => 'Мобильный телефон')
                ),
                'email' => new sfWidgetFormInputText(
                    array('label' => 'Email')
                ),
            )
        );

        $this->setValidators(
            array(
                'city'       => new sfValidatorChoice(
                    array(
                        'choices'  => $cities,
                        'required' => true,
                    )
                ),
                'employment' => new sfValidatorChoice(
                    array(
                        'choices'  => $employments,
                        'required' => true,
                    )
                ),
                'birthday'   => new sfValidatorDate(
                    array(
                        'date_format' =>
                            '~(?P<year>\d{4})-(?P<month>\d{2})-(?P<day>\d{2})~',
                        'max'       => date('Y-m-d', strtotime('22 years ago')),
                        'min'       => date('Y-m-d', strtotime('60 years ago')),
                    )
                ),
                'name' => new sfValidatorRegex(
                    array(
                        'pattern'  => '~[А-Я][а-я]+~',
                        'trim'     => true,
                        'required' => true,
                    )
                ),
                'patronymic' => new sfValidatorRegex(
                    array(
                        'pattern'  => '~[А-Я][а-я]+~',
                        'trim'     => true,
                        'required' => false,
                    )
                ),
                'surname'    => new sfValidatorRegex(
                    array(
                        'pattern'  => '~[А-Я][а-я]+~',
                        'trim'     => true,
                        'required' => true,
                    )
                ),
                'mobile_code' => new sfValidatorRegex(
                    array(
                        'pattern'  => '~\d{3}~',
                        'trim'     => true,
                        'required' => true,
                    )
                ),
                'mobile_phone' => new sfValidatorRegex(
                    array(
                        'pattern'  => '~\d{7}~',
                        'trim'     => true,
                        'required' => true,
                    )
                ),
                'email' => new sfValidatorEmail(),
            )
        );

        $this->widgetSchema->setNameFormat('%s');

        $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);
    }


    /**
     * Получить название модели
     *
     * @return string
     */
    public function getModelName()
    {
        return null;
    }

}
