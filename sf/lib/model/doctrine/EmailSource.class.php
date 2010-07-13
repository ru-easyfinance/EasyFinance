<?php

/**
 * EmailSource
 *
 */
class EmailSource extends BaseEmailSource
{
    /**
     * Возвращает парсер, регексп которого подходит под данную тему
     *
     * @param string $subject тема
     * @return EmailParser или false в случае если парсер не найден
     */
    public function getParserBySubject( $subject )
    {
        return
        Doctrine_Query::create()
            ->select('*')
            ->from('EmailParser')
            ->where('email_source_id = ?', $this->getId() )
            ->andWhere('? RLIKE subject_regexp', $subject )
            ->execute()
            ->getFirst();
    }
}
