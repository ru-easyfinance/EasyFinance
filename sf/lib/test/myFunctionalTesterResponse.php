<?php
/**
 * Расширенный тестер для responce
 *
 * @author Max <maxim.olenik@gmail.com>
 */

class myFunctionalTesterResponse extends sfTesterResponse
{
    /**
     * Проверить, что ответ эквивалентен указанному значению
     *
     * @param  string $text - полный текст ответа
     */
    public function checkEquals($text)
    {
        PHPUnit_Framework_Assert::assertEquals($text, $content = $this->response->getContent(),
            sprintf("Expected response equals\n%s\n\nGot:\n%s", substr($text, 0, 255), substr($content, 0, 255))
        );

        return $this->getObjectToReturn();
    }


    /**
     * Проверить, что ответ содержит указанную строку
     *
     * @param  $text - Искомая строка
     */
    public function checkContains($text)
    {
        PHPUnit_Framework_Assert::assertContains($text, $content = $this->response->getContent(),
            sprintf("Expected response contains `{$text}`")
        );

        return $this->getObjectToReturn();
    }


    /**
     * Check: проверить редирект
     *
     * @param int    $statusCode
     * @param string $uri
     */
    public function checkRedirect($statusCode, $uri)
    {
        return $this->begin()
            ->isStatusCode($statusCode)
            ->isHeader('Location', 'http://localhost'.$uri)
        ->end();
    }

}
