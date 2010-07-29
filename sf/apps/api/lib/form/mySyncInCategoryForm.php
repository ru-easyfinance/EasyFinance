<?php

/**
 * Форма для обработки запросов на синхронизацию счетов
 */
class mySyncInCategoryForm extends BaseFormDoctrine
{
    /**
     * Config
     */
    public function configure()
    {
        $this->setValidators(array(
            'system_id'   => new sfValidatorInteger(array('min' => 1)),
            'parent_id'   => new sfValidatorInteger(array('min' => 0)),
            'name'        => new sfValidatorString(array('max_length' => 255)),
            'type'        => new sfValidatorInteger(array('min' => -1, 'max' => 1)),
            'created_at'  => new myValidatorDatetimeIso8601(),
            'updated_at'  => new myValidatorDatetimeIso8601(),
            'deleted_at'  => new myValidatorDatetimeIso8601(array('required' => false)),
        ));

        if ($this->isNew()) {
            $this->setValidator('user_id', new sfValidatorPass());
        }

        $this->validatorSchema->setOption('allow_extra_fields', true);

        $this->widgetSchema->setNameFormat('%s');
        $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);
        $this->disableLocalCSRFProtection();
    }

    /**
     * @return string Имя связанной модели
     */
    public function getModelName()
    {
        return 'Category';
    }


}
