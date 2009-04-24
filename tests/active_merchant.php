<?php

define('ALL_TESTS_CALL', true);
define('ALL_TESTS_RUNNER',true);

require_once(dirname(__FILE__). '/config.php');

$test = &new GroupTest('Active Merchant Tests');

$test_files = array(
    'base', 
    'country_code', 
    'country', 
    'credit_card_formatting', 
    'credit_card_method', 
    'credit_card', 
    'expiry_date', 
    'gateway', 
    'notification', 
    'util', 
/**/
);

foreach($test_files as $file) {
   $test->addTestFile(ACTIVE_MERCHANT_LIB_TESTING_DIR.DS . $file . '.php');
}

if(TextReporter::inCli()) {
    exit($test->run(new TextReporter()) ? 0 : 1);
}