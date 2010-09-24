<?php
require_once dirname(__FILE__).'/../../bootstrap/all.php';
/**
 * myBaseQuery
 */

class myBaseQueryTest extends myUnitTestCase
{
    /**
     * Часть havingIn запроса
     */
    public function testHavingIn()
    {
        $expectedDql = "User.id IN (?, ?)";

        $q = myBaseQuery::create()
            ->select('User.id')
            ->from('User')
            ->havingIn("User.id", $expectedParams = array(4, 5));

        $this->assertNotNull($q->getDqlPart('having'));
        $this->assertEquals(1, count($q->getDqlPart('having')), 'one having query part');

        $actualHavingDql = $q->getDqlPart('having');
        $this->assertEquals($expectedDql, $actualHavingDql['0']);

        $params = $q->getParams();
        $this->assertType('array', $params);
        $this->assertType('array', $params['having']);
        $this->assertEquals($expectedParams, $params['having']);
    }

}
