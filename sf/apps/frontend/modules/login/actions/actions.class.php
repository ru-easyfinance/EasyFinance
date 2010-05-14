<?php

/**
 * login actions.
 *
 * @package    EasyFinance
 * @subpackage login
 * @author     EasyFinance
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class loginActions extends sfActions
{
   /**
    * Редирект на страницу авторизации
    *
    * @param sfRequest $request A request object
    */
    public function executeIndex(sfWebRequest $request)
    {
    $this->redirect('/login');
    }
}
