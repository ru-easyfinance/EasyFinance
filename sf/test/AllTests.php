<?php
require_once(dirname(__FILE__).'/bootstrap/all.php');


class AllSymfonyAppTests extends PHPUnit_Framework_TestSuite
{
    /**
     * SetUp
     */
    public function setup()
    {
        // Clear logs
        sfToolkit::clearDirectory(sfConfig::get('sf_log_dir'));

        // Remove current app cache
        sfToolkit::clearDirectory(sfConfig::get('sf_cache_dir'));

        // TODO: Rebuild DB
    }


    /**
     * TestSuite
     */
    public static function suite()
    {
        $suite = new AllSymfonyAppTests('EasyFinance Symfony Branch');

        $base  = dirname(__FILE__);
        $files = sfFinder::type('file')->name('*Test.php')->in(array(
            // $base.'/../plugins/sfPhpunitPlugin/test',
            $base.'/unit',
            $base.'/functional',
        ));

        foreach ($files as $file) {
            $suite->addTestFile($file);
        }

        return $suite;
    }

}
