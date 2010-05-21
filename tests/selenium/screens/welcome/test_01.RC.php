<?php

if (!defined('INDEX')) define('INDEX', 1);
require_once dirname(__FILE__) . '/../../../../include/config.php';
require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

class Example extends PHPUnit_Extensions_SeleniumTestCase
{
  function setUp()
  {
    $this->setBrowser("*firefox");
    $this->setBrowserUrl("https://".URL_ROOT_MAIN);
  }

  function testMyTestCase()
  {
    // открываем главную страницу сайта
    $this->open("/");

    // проверяем SEO-тексты
    try {
        $this->assertTextPresent("Семейный бюджет и личные финансы");
        $this->assertTextPresent("Соотношение доходов и расходов");
        $this->assertTextPresent("Этапы формирования семейного бюджета");
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, "SEO: " . $e->toString());
    }

    // проверяем ссылки верхнего меню
    $this->click("link=Обзор");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertEquals("https://".URL_ROOT_MAIN."review/", $this->getLocation());
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, "Обзор: " . $e->toString());
    }

    $this->open("/");
    $this->click("link=Отзывы");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertEquals("https://".URL_ROOT_MAIN."feedback/", $this->getLocation());
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, "Отзывы: " . $e->toString());
    }

    /*
    $this->open("/");
    $this->click("link=Блог");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertEquals("http://easyfinance-ru.livejournal.com/", $this->getLocation());
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, "Блог: " . $e->toString());
    }
     */

    $this->open("/");
    $this->click("link=Статьи");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertEquals("https://".URL_ROOT_MAIN."articles/", $this->getLocation());
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, "Статьи: " . $e->toString());
    }

    /*
    $this->open("/");
    $this->click("link=Мобильная версия");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertEquals("http://m.".URL_ROOT_MAIN."/", $this->getLocation());
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, "Мобильная версия: " . $e->toString());
    }
     */

    $this->open("/");
    $this->click("link=Помощь");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertEquals("https://".URL_ROOT_MAIN."articles/12", $this->getLocation());
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, "Помощь: " . $e->toString());
    }

    $this->open("/");
    $this->click("link=Интеграция");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertEquals("https://".URL_ROOT_MAIN."integration", $this->getLocation());
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, "Интеграция: " . $e->toString());
    }
  }
}
?>