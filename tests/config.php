<?php

define('AK_FRAMEWORK_DIR', '/var/src/akelos');
define('DS', DIRECTORY_SEPARATOR);
define('AK_BASE_DIR', realpath(dirname(__FILE__).str_repeat(DIRECTORY_SEPARATOR.'..', 5)));
define('AK_PLUGINS_DIR', realpath(dirname(__FILE__).str_repeat(DIRECTORY_SEPARATOR.'..', 2)));

//define('AK_APP_DIR', AK_BASE_DIR.DIRECTORY_SEPARATOR.'app');
define('AK_ENVIRONMENT', 'testing');
define('AK_TEST_DIR', str_replace(DIRECTORY_SEPARATOR.'tests'.DIRECTORY_SEPARATOR.'config.php','',__FILE__));
define('AK_TMP_DIR', AK_TEST_DIR.DIRECTORY_SEPARATOR.'tmp');
define('AK_BASE_DIR', str_replace(DS.'config'.DS.'boot.php','',__FILE__));
define('AK_FRAMEWORK_DIR', AK_BASE_DIR);
define('AK_LIB_DIR',AK_FRAMEWORK_DIR.DS.'lib');
define('AK_APP_LOCALES', 'en,es');
define('AK_PUBLIC_LOCALES', AK_APP_LOCALES);

if(!is_file(AK_FRAMEWORK_DIR.DS.'akelos')) {
    trigger_error(
        'Could not find the Akelos framework at ' . '"'.AK_FRAMEWORK_DIR. '"' . PHP_EOL . 
        'Please define AK_FRAMEWORK_DIR in ' . PHP_EOL .
        '"'.__FILE__.'"'
    );
}

require_once(AK_LIB_DIR.DS.'constants.php');
require_once(AK_LIB_DIR.DS.'AkInstaller.php');
require_once(AK_LIB_DIR.DS.'AkPlugin.php');

$Plugin = new AkPluginLoader();
$Plugin->loadPlugins();

define('ACTIVE_MERCHANT_LIB_TESTING_DIR', AK_ACTIVE_MERCHANT_DIR.DS.'tests'.DS.'lib');

require_once(AK_LIB_DIR.DS.'AkUnitTest.php');
class ActiveMerchantUnitTest extends AkUnitTest
{
    public function includeAndInstatiateModels()
    {
        $args = func_get_args();
        return call_user_func_array(array('ActiveMerchant', 'import'), $args);
    }
}

if((!defined('ALL_TESTS_CALL') || !ALL_TESTS_CALL) && preg_match('/lib\/(.*)\.php/', array_shift(get_included_files()), $match)) {
    $___test_case_name = 'ActiveMerchant'.AkInflector::camelize($match[1]).'TestCase';
    function delayed_test(){global $___test_case_name;ak_test($___test_case_name);}
    register_shutdown_function('delayed_test');
}

