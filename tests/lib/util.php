<?php

require_once(dirname(__FILE__). '/../config.php');

ActiveMerchant::import('util');

class ActiveMerchantUtilTestCase extends ActiveMerchantUnitTest
{
    public function test_unique_id_should_be_32_chars_and_alphanumeric()
    {
        $this->assertTrue(preg_match('/^\w{32}$/', ActiveMerchantUtil::generate_unique_id()));
    }
}
