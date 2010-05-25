<?php
require_once(dirname(__FILE__) . '/../../sf/lib/vendor/symfony/lib/util/sfFinder.class.php');


/**
 * Все тесты старого EasyFinance
 */
class EasyFinance_Unit_AllTests extends PHPUnit_Framework_TestSuite
{
    /**
     * TestSuite
     */
    public static function suite()
    {
        $suite = new EasyFinance_Unit_AllTests('Origin EasyFinance');

        $files = sfFinder::type('file')->name('*Test.php')->in(array(
            dirname(__FILE__),
        ));

        foreach ($files as $file) {
            $suite->addTestFile($file);
        }

        return $suite;
    }

}
