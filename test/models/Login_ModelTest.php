<?php
ini_set("include_path", "../models".PATH_SEPARATOR."../../models".PATH_SEPARATOR.ini_get("include_path"));
require_once 'PHPUnit/Framework.php';

require_once 'login.model.php';

/**
 * Test class for Login_Model.
 * Generated by PHPUnit on 2009-09-12 at 12:31:05.
 */
class Login_ModelTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var    Login_Model
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
        $this->object = new Login_Model;
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
     * @todo Implement testDefaultCategory().
     */
    public function testDefaultCategory()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testActivate_user().
     */
    public function testActivate_user()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testAuth_user().
     */
    public function testAuth_user()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }
}
?>
