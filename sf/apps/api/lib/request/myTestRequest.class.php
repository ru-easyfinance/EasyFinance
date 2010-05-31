<?php
/**
 * Объект запроса для тестового окружения
 */
class myTestRequest extends sfWebRequest
{

    /**
     * Возвращает псевдо-тело запроса
     *
     * @return string|Boolean Содержимое или false
     */
    public function getContent()
    {
        if (null === $this->content) {
            if (0 === strlen(trim($this->content = $this->getParameter('body')))) {
                $this->content = false;
            }
        }

        return $this->content;
    }

}
