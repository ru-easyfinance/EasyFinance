<?php
require_once dirname(__FILE__).'/../../bootstrap/all.php';


/**
 * Парсеры email
 */
class model_EmailParserTest extends myUnitTestCase
{
    /**
     * Отношения
     *
     */
    public function testRelations()
    {
        $ep = new EmailParser;
        // Отправитель
        $this->assertType('EmailSource', $ep->EmailSource);
    }


    /**
     * Тесты каскадов
     *
     */
    public function testCascade()
    {
        $source = new EmailSource();
        $source->setName('name');
        $source->setEmailList('email');
        $source->save();
        $id = $source->getId();

        $parser = new EmailParser();
        $parser->setName('parser');
        $parser->setEmailSourceId( $id );
        $parser->setSubjectRegexp("subj");
        $parser->save();

        $parserId = $parser->getId();

        $source->delete();

        $findParser = Doctrine::getTable("EmailParser")->find($parserId);
        $this->assertEquals( $findParser, null );
    }
}
