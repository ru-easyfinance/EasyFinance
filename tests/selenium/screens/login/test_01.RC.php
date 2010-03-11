<?php

require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

class Example extends PHPUnit_Extensions_SeleniumTestCase
{
  function setUp()
  {
    $this->setBrowser("*chrome");
    $this->setBrowserUrl("http://change-this-to-the-site-you-are-testing/");
  }

  function testMyTestCase()
  {
    $this->open("/login/");
    $this->type("flogin", "");
    $this->type("pass", "");
    $this->click("autoLogin");
    $this->click("btnLogin");
    try {
        $this->assertEquals("https://easy/login/", $this->getLocation());
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->type("flogin", "test");
    $this->click("btnLogin");
    try {
        $this->assertEquals("https://easy/login/", $this->getLocation());
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->type("flogin", "");
    $this->type("pass", "test");
    $this->click("btnLogin");
    try {
        $this->assertEquals("https://easy/login/", $this->getLocation());
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Восстановление пароля");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertEquals("https://easy/restore/", $this->getLocation());
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->open("/login/");
    $this->click("link=Регистрация");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertEquals("https://easy/registration/", $this->getLocation());
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->open("/login/");
    $this->type("flogin", "test");
    $this->type("pass", "test");
    $this->click("btnLogin");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertEquals("https://easy/info/", $this->getLocation());
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
  }
}
?>