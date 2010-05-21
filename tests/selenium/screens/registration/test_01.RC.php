<?php

if (!defined('INDEX')) define('INDEX', 1);
require_once dirname(__FILE__) . '/../../../../include/config.php';
require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

class Example extends PHPUnit_Extensions_SeleniumTestCase
{
  private $userName, $userMail;

  function setUp()
  {
    $this->setBrowser("*firefox");
    $this->setBrowserUrl("https://".URL_ROOT_MAIN);

    $this->userName = "selenium".time();
    $this->userMail = $this->userName."@easyfinance.ru";
  }

  function checkUserLogged()
  {
    try {
        // в верхнем меню должно быть написано имя залогиненного пользователя
        $this->assertTextPresent($this->userName);
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, "Регистрация: " . $e->toString());
    }
  }

  function testMyTestCase()
  {
    $this->open("/registration/");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertEquals("https://".URL_ROOT_MAIN."registration/", $this->getLocation());
        $this->assertTextPresent("Регистрация");
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, "Регистрация: " . $e->toString());
    }

    $this->type("id=name", $this->userName);
    $this->type("id=log", $this->userName);
    $this->type("id=passw", "12345");
    $this->type("id=confirm_password", "12345");
    $this->type("id=mail", $this->userMail);
    $this->type("id=mail_confirm", $this->userMail);

    $this->clickAndWait("id=butt");

    $this->checkUserLogged();

    $this->clickAndWait("id=show_logout");

    try {
        // должен произойти редирект на главную
        $this->assertEquals("https://".URL_ROOT_MAIN, $this->getLocation());
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, "Главная: " . $e->toString());
    }

    $this->clickAndWait("id=linkLogin");

    try {
        $this->assertEquals("https://".URL_ROOT_MAIN."login/", $this->getLocation());
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, "Вход: " . $e->toString());
    }

    $this->type("id=flogin", $this->userName);
    $this->type("id=pass", "12345");

    $this->clickAndWait("id=btnLogin");

    $this->checkUserLogged();
  }
}
?>