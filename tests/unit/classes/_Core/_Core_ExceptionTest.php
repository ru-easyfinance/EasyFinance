<?php
    require_once 'PHPUnit/Framework.php';

    require_once dirname ( __FILE__ ) . '/../../../../classes/_Core/Exception.php';

    /**
     * Test class for _Core_Exception.
     * Generated by PHPUnit on 2010-03-05 at 11:49:30.
     */
    class _Core_ExceptionTest extends PHPUnit_Framework_TestCase
    {

        /**
         * @var _Core_Exception
         */
        protected $object;

        /**
         * Sets up the fixture, for example, opens a network connection.
         * This method is called before a test is executed.
         */
        protected function setUp ()
        {
            $this->object = new _Core_Exception;
        }

        /**
         * Tears down the fixture, for example, closes a network connection.
         * This method is called after a test is executed.
         */
        protected function tearDown ()
        {

        }

        /**
         * @todo Implement testTypeErrorMessage().
         */
        public function testTypeErrorMessage ()
        {
            // Remove the following lines when you implement this test.
            $this->markTestIncomplete (
                    'This test has not been implemented yet.'
            );
        }
    }

?>
