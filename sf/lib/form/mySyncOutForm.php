<?php

/**
 * Форма для обработки запроса на синхронизацию
 * Проверяет даты
 */
class mySyncOutForm extends BaseForm
{
    /**
     * Config
     */
    public function configure()
    {
        // No wiggets

        $this->setValidators(array(
            'from' => new myValidatorDatetimeIso8601(array('required' => true)),
            'to'   => new myValidatorDatetimeIso8601(array('required' => true)),
        ));

        $this->validatorSchema->setPostValidator(
           new sfValidatorDateRange(array(
                'from_date'  => new sfValidatorDateTime(array('required' => false)),
                'to_date'    => new sfValidatorDateTime(array('required' => false)),
            ), array(
                'invalid' => 'Invalid date/time range. Expected `start date` is less than `end date`.'
            ))
        );

        // см. doBind()
        $this->validatorSchema->setOption('allow_extra_fields', true);


        $this->widgetSchema->setNameFormat('%s');
        $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);
        $this->disableLocalCSRFProtection();
    }


    /**
     * Получить интервал из двух дат
     *
     * @return myDatetimeRange
     */
    public function getDatetimeRange()
    {
        if (!$this->isBound) {
            throw new Exception(__METHOD__.": Form is NOT bound");
        }
        return new myDatetimeRange(new DateTime($this->getValue('from')), new DateTime($this->getValue('to')));
    }

}
