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
     * Проверить, что JSON содержит ключ с указанным заначением
     */
    public function checkJsonContains($key, $value, $message = null)
    {
        $json = json_decode($this->response->getContent(), true);

        $message = $message . "\n%s\n\nJSON:\n" . var_export($json, true);
        PHPUnit_Framework_Assert::assertTrue(isset($json[$key]), sprintf($message, "Expected JSON contains key: {$key}"));
        PHPUnit_Framework_Assert::assertEquals($value, $json[$key], sprintf($message, "Expected JSON key `{$key}` contains value"));

        return $this->getObjectToReturn();
    }


    /**
     * Check: проверить редирект
     *
     * @param int    $statusCode
     * @param string $uri
     */
    public function checkRedirect($statusCode, $uri, $abs = false)
    {
        if (!$abs) {
            $uri = 'http://localhost' . $uri;
        }

        return $this->begin()
            ->isStatusCode($statusCode)
            ->isHeader('Location', $uri)
        ->end();
    }

}
