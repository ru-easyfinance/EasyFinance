<?php
/**
 * Форма для редактирования статьи бюджета
 */
class BudgetCategoryEditForm extends BaseFormDoctrine
{
    public function setup()
    {
        $this->setValidators(array(
            'category_id' => new sfValidatorInteger(array('required' => true)),
            'type'        => new sfValidatorChoice(
                array(
                    'choices'     => array('1', '0'),
                    'empty_value' => '0'
                )
            ),
            'start'       => new sfValidatorDate(array('required' => true)),
            'value' => new sfValidatorNumber(array('required' => false))
        ));

        $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

        parent::setup();
    }


    /**
     * Config
     */
    public function configure()
    {
        $this->validatorSchema->setOption('allow_extra_fields', true);
        $this->disableLocalCSRFProtection();
    }

    public function getModelName()
    {
        return 'BudgetCategory';
    }


    public function getObject()
    {
        $class  = $this->getModelName();

        if (!$this->isValid())
            return new $class();

        $values = $this->getValues();
        $userId = $this->getUser()->getUserRecord()->getId();
        $key    = "{$userId}-{$values['category_id']}-{$values['type']}";
        $key   .= "-{$values['start']}";

        $object = Doctrine::getTable($class)->find($key);

        if ($object === false) {
            $object = new $class();
            $object->setKey($key);
            $object->setUserId($userId);
            $object->setCategoryId($values['category_id']);
            $object->setType($values['type']);
            $object->setDateStart($values['start']);
        }

        return $object;
    }


    private function getUser()
    {
        return sfContext::getInstance()->getUser();
    }
}
?>
