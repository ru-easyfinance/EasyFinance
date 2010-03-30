<?php

/**
 * Base test class for all functional tests
 */
abstract class myFunctionalTestCase extends sfPHPUnitFunctionalTestCase
{
    /**
     * Inject your own functional testers
     *
     * @see sfTestFunctionalBase::setTesters()
     *
     * @return array
     *          'request'  => 'sfTesterRequest',
     *          'response' => 'sfTesterResponse',
     *          'user'     => 'sfTesterUser',
     */
    protected function getFunctionalTesters()
    {
        return array(
            'request'  => 'myFunctionalTesterRequest',
            'response' => 'myFunctionalTesterResponse',
            'model'    => 'sfTesterDoctrine',
        );
    }

}
