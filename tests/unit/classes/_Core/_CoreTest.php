<?php
    require_once 'PHPUnit/Framework.php';

    require_once dirname ( __FILE__ ) . '/../../../../classes/_Core/_Core.php';

    /**
     * Test class for _Core.
     * Generated by PHPUnit on 2010-03-05 at 11:49:30.
     */
    class _CoreTest extends PHPUnit_Framework_TestCase
    {

        /**
         * @var _Core
         */
        protected $object;

        /**
         * Sets up the fixture, for example, opens a network connection.
         * This method is called before a test is executed.
         */
        protected function setUp ()
        {
            $this->object = new _Core;
        }

        /**
         * Tears down the fixture, for example, closes a network connection.
         * This method is called after a test is executed.
         */
        protected function tearDown ()
        {

        }

        /**
         * @todo Implement test__autoload().
         */
        public function test__autoload ()
        {
            // Remove the following lines when you implement this test.
            $this->markTestIncomplete (
                    'This test has not been implemented yet.'
            );
        }
    }

?>