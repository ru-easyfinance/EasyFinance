<?php
require_once dirname(__FILE__).'/bootstrap/all.php';


// Suite
require_once(dirname(__FILE__).'/../../tests/unit/AllTests.php');


/**
 * Все тесты EasyFinance
 */
class AllTests extends PHPUnit_Framework_TestSuite
{
    /**
     * SetUp
     */
    public function setUp()
    {
        $dir = getcwd();
        chdir(sfConfig::get('sf_root_dir'));

        // Clear logs
        sfToolkit::clearDirectory(sfConfig::get('sf_log_dir'));

        // Remove cache
        sfToolkit::clearDirectory(sfConfig::get('sf_cache_dir'));

        // Rebuild DB
        $task = new sfDoctrineBuildTask(new sfEventDispatcher, new sfFormatter);
        $task->run($args = array(), $options = array(
            'env' => 'test',
            'no-confirmation' => true,
            'db' => true,
            'and-migrate' => true,
            'application' => 'frontend'
        ));
        // Таск создает свой конфиг, после чего в изоляции может молча умереть
        ProjectConfiguration::getApplicationConfiguration('frontend', 'test', $debug = true);

        chdir($dir);
    }


    /**
     * TestSuite
     */
    public static function suite()
    {
        $suite = new AllTests('EasyFinance');

        $base  = dirname(__FILE__);
        $files = sfFinder::type('file')->name('*Test.php')->in(array(
            $base.'/unit',
            $base.'/functional',
        ));

        $suite->addTest(EasyFinance_Unit_AllTests::suite());
        foreach ($files as $file) {
            $suite->addTestFile($file);
        }

        return $suite;
    }

}
