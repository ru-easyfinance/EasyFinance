<?php
    require_once 'PHPUnit/Framework.php';

    require_once dirname ( __FILE__ ) . '/../../../../classes/Feedback/Feedback.php';

    /**
     * Test class for Feedback.
     * Generated by PHPUnit on 2010-03-05 at 11:49:35.
     */
    class FeedbackTest extends PHPUnit_Framework_TestCase
    {

        /**
         * @var Feedback
         */
        protected $object;

        /**
         * Sets up the fixture, for example, opens a network connection.
         * This method is called before a test is executed.
         */
        protected function setUp ()
        {
            $this->object = new Feedback;
        }

        /**
         * Tears down the fixture, for example, closes a network connection.
         * This method is called after a test is executed.
         */
        protected function tearDown ()
        {

        }

        /**
         * @todo Implement testAdd_message().
         */
        public function testAdd_message ()
        {
            // Remove the following lines when you implement this test.
            $this->markTestIncomplete (
                    'This test has not been implemented yet.'
            );
        }
    }

?>