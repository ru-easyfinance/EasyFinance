<?php

/**
 * EmailParser form.
 *
 * @package    EasyFinance
 * @subpackage form
 * @author     EasyFinance
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class EmailParserForm extends BaseEmailParserForm
{
  public function configure()
  {
  	 $this->widgetSchema['type'] = new sfWidgetFormChoice( array( 'choices' => EmailParser::getTypeChoices() ) );
  	 $this->validatorSchema['description_regexp'] = new sfValidatorString(array('max_length' => 255, 'required' => false ));
     $this->widgetSchema['sample'] = new sfWidgetFormTextarea();
  }
}
