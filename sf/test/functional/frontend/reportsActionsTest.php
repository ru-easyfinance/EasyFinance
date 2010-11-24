<?php
include(dirname(__FILE__).'/../../bootstrap/all.php');

/**
 * Functional test class for module "reports" of application "frontend".
 *
 * Call this test with:
 * phpunit test/functional/frontend/reportsActionsTest.php
 */
class reportsActionsTest extends myFunctionalTestCase
{
    /**
     * Returns application name for this test case. Needed for context creation.
     */
    public function getApplication()
    {
        return 'frontend';
    }

    /**
     * Returns environment name for this test case. Needed for context creation.
     */
    public function getEnvironment()
    {
        return 'test';
    }

    /**
     * Returns sfTestFunctional instance
     * @return sfTestFunctional
     */
    public function getBrowser()
    {
        return $this->browser;
    }

    /**
     * First test method
     */
    public function test1()
    {
        $user = $this->helper->makeUser();
        $this->authenticateUser($user);
        $user->save();

        $browser = $this->getBrowser();

        $browser->
        get('/reports/matrix')->

        with('request')->begin()->
        isParameter('module', 'reports')->
        isParameter('action', 'matrix')->
        end()->

        with('response')->begin()->
        isStatusCode(200)->
        end();
    }
}
