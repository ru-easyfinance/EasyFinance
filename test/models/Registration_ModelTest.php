<?php
ini_set("include_path", "../models".PATH_SEPARATOR."../../models".PATH_SEPARATOR.ini_get("include_path"));
require_once 'PHPUnit/Framework.php';

require_once 'registration.model.php';

/**
 * Test class for Registration_Model.
 * Generated by PHPUnit on 2009-09-12 at 12:31:04.
 */
class Registration_ModelTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var    Registration_Model
     * @access protected
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     */
    protected function setUp()
    {
        $this->object = new Registration_Model;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @access protected
     */
    protected function tearDown()
    {
    }

    /**
     * @todo Implement testActivate().
     */
    public function testActivate()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testNew_user().
     */
    public function testNew_user()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testValidate_login().
     */
    public function testValidate_login()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }
}
?>
