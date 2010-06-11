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
            'root_id'     => new sfValidatorInteger(array('min' => 1)),
            'parent_id'   => new sfValidatorInteger(array('min' => 0)),
            'name'        => new sfValidatorString(array('max_length' => 255)),
            'type'        => new sfValidatorInteger(array('min' => -1, 'max' => 1)),
            'cat_active'  => new sfValidatorBoolean(),
            'visible'     => new sfValidatorBoolean(),
            'custom'      => new sfValidatorBoolean(),
            'dt_create'   => new myValidatorDatetimeIso8601(),
            'dt_update'   => new myValidatorDatetimeIso8601(),
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
