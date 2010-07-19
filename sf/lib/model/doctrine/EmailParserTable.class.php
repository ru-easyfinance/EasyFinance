<?php

/**
 * Таблица: "парсеры" корреспонденции
 */
class EmailParserTable extends Doctrine_Table
{
    /**
     * Найти парсер по теме и идентификатору источника
     *
     * @param  EmailSource $source
     * @param  string      $subject
     * @return EmailParser|false
     */
    public function getOneBySubjectAndSource(EmailSource $source, $subject)
    {
        $q = $this->createQuery('p')
            ->andWhere('p.email_source_id = ?', $source->getId())
            ->andWhere('? RLIKE p.subject_regexp', $subject)
            ->limit(1);

            return $q->fetchOne();
    }

}
