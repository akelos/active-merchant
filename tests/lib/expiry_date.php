<?php

require_once(dirname(__FILE__). '/../config.php');
ActiveMerchant::import('expiry_date');

class ActiveMerchantExpiryDateTestCase extends ActiveMerchantUnitTest
{
    public function test_should_be_expired()
    {
        $lastmonth = mktime(0, 0, 0, date('m') - 1, date('d'), date('Y'));
        $d = new ActiveMerchantExpiryDate(date('m', $lastmonth), date('Y', $lastmonth));
        $this->assertTrue($d->isExpired());
    }
    public function test_today_should_not_be_expired()
    {
        $d = new ActiveMerchantExpiryDate(date('m'), date('Y'));
        $this->assertFalse($d->isExpired());
    }
    public function test_dates_in_the_future_should_not_be_expired()
    {
        $nextmonth = mktime(0, 0, 0, date('m') + 1, date('d'), date('Y'));
        $d = new ActiveMerchantExpiryDate(date('m', $nextmonth), date('Y', $nextmonth));
        $this->assertFalse($d->isExpired());
    }
}
