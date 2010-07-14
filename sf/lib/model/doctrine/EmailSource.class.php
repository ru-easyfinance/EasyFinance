<?php

/**
 * EmailSource
 */
class EmailSource extends BaseEmailSource
{
    /**
     * Возвращает парсер по теме письма
     *
     * @see    EmailParserTable::getOneBySubjectAndSource
     * @param  string $subject тема письма
     * @return EmailParser|false
     */
    public function getParserBySubject($subject)
    {
        return Doctrine_Core::getTable("EmailParser")
            ->getOneBySubjectAndSource($this, $subject);
    }

}
