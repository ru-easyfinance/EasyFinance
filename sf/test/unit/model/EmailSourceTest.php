<?php
require_once dirname(__FILE__).'/../../bootstrap/all.php';


/**
 * Счета
 */
class model_EmailSourceTest extends myUnitTestCase
{

    /**
     * Тест метода getParserBySubject
     *
     */
    public function testGetParserBySubject()
    {
        // Создаем отправителя
        $source = new EmailSource();
        $source->setName("test");
        $source->setEmailList("anytest@test.tst");
        $source->save();

        // Создаем правильный парсер
        $parser1 = new EmailParser();
        $parser1->setEmailSourceId( $source->getId() );
        $parser1->setName( "parser1" );
        $parser1->setSubjectRegexp( "описание операции \(Снятие наличных\/Платеж\)" );
        $parser1->save();

        // Создаем неправильный парсер
        $parser2 = new EmailParser();
        $parser2->setEmailSourceId( $source->getId() );
        $parser2->setName( "parser2" );
        $parser2->setSubjectRegexp( "это неправильный регексп" );
        $parser2->save();

        $testParser = $source->getParserBySubject( "fwd: описание операции (Снятие наличных/Платеж) xxx" );

        $this->assertModels( $parser1, $testParser );
    }
}