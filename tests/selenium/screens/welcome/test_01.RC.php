<?php

require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

class Example extends PHPUnit_Extensions_SeleniumTestCase
{
  function setUp()
  {
    $this->setBrowser("*chrome");
    $this->setBrowserUrl("http://easy/");
  }

  function testMyTestCase()
  {
    $this->open("/");
    $this->click("link=зарегистрироваться");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertEquals("http://easy/registration/", $this->getLocation());
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->open("/");
    $this->click("link=ВХОД");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertEquals("https://easy/login/", $this->getLocation());
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->open("/");
    $this->click("review");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertEquals("http://easy/review/", $this->getLocation());
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->open("/");
    $this->click("feed");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertEquals("http://easy/feedback/", $this->getLocation());
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->open("/");
    $this->click("articles");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertEquals("http://easy/articles/", $this->getLocation());
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->open("/");
    $this->click("pda");
    for ($second = 0; ; $second++) {
        if ($second >= 60) $this->fail("timeout");
        try {
            if ("5000" == $this->getLocation()) break;
        } catch (Exception $e) {}
        sleep(1);
    }

    try {
        $this->assertEquals("https://m.easy/", $this->getLocation());
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->open("/");
    $this->click("help");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertEquals("https://easy/articles/12", $this->getLocation());
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->open("/");
    $this->clickAt("link=Правила использования", "");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertEquals("http://easy/rules/", $this->getLocation());
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->open("/");
    $this->click("link=О компании");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertEquals("http://easy/about/", $this->getLocation());
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->open("/");
    $this->click("link=Безопасность");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertEquals("http://easy/security/", $this->getLocation());
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->open("/");
  }
}
?>