<?php

require_once(dirname(__FILE__). '/../config.php');
ActiveMerchant::import('util', 'notification');

class ActiveMerchantNotificationTestCase extends ActiveMerchantUnitTest
{
    public $notification;
    
    public function setup()
    {
        $this->notification = new ActiveMerchantNotification($this->_http_raw_data());
    }
    public function test_raw()
    {
        $this->assertEqual($this->_http_raw_data(), $this->notification->raw);
    }
    public function test_parse()
    {
        $this->assertEqual('500.00', $this->notification->params['mc_gross']);
        $this->assertEqual('confirmed', $this->notification->params['address_status']);
        $this->assertEqual('EVMXCLDZJV77Q', $this->notification->params['payer_id']);
        $this->assertEqual('Completed', $this->notification->params['payment_status']);
        $this->assertEqual(urldecode('15%3A23%3A54+Apr+15%2C+2005+PDT'), $this->notification->params['payment_date']);
    }
    public function test_accessors()
    {
        try {
            $this->notification->getStatus();
            $this->assertTrue(false);
        } catch(ActiveMerchantNotImplementedException $e) {
            $this->assertTrue(true);
        }
        try {
            $this->notification->getGross();
            $this->assertTrue(false);
        } catch(ActiveMerchantNotImplementedException $e) {
            $this->assertTrue(true);
        }
        try {
            $this->notification->getGrossCents();
            $this->assertTrue(false);
        } catch(ActiveMerchantNotImplementedException $e) {
            $this->assertTrue(true);
        }
    }
    public function test_notification_data_with_period()
    {
        $n = new ActiveMerchantNotification($this->_http_raw_data_with_period());
        // WARNING : "." dots are converted to underscores "_" with parse_str!!
        //$this->assertEqual('clicked', $n->params['checkout.x']);
        $this->assertEqual('clicked', $n->params['checkout_x']);
    }
    public function test_valid_sender_always_true_in_testmode()
    {
        ActiveMerchant::import('base');
        ActiveMerchantBase::setIntegrationMode('test');
        $this->assertEqual(ActiveMerchantBase::getIntegrationMode(), 'test');
        $this->assertTrue($this->notification->isValidSender(null));
        $this->assertTrue($this->notification->isValidSender('localhost'));
    }
    public function test_valid_sender_always_true_when_no_ips()
    {
        ActiveMerchant::import('base');
        ActiveMerchantBase::setIntegrationMode('production');
        $this->assertTrue($this->notification->isValidSender(null));
        $this->assertTrue($this->notification->isValidSender('localhost'));
        ActiveMerchantBase::setIntegrationMode('test');
    }
    private function _http_raw_data()
    {
        return 'mc_gross=500.00&address_status=confirmed&payer_id=EVMXCLDZJV77Q&tax=0.00&address_street=164+Waverley+Street&payment_date=15%3A23%3A54+Apr+15%2C+2005+PDT&payment_status=Completed&address_zip=K2P0V6&first_name=Tobias&mc_fee=15.05&address_country_code=CA&address_name=Tobias+Luetke&notify_version=1.7&custom=&payer_status=unverified&business=tobi%40leetsoft.com&address_country=Canada&address_city=Ottawa&quantity=1&payer_email=tobi%40snowdevil.ca&verify_sign=AEt48rmhLYtkZ9VzOGAtwL7rTGxUAoLNsuf7UewmX7UGvcyC3wfUmzJP&txn_id=6G996328CK404320L&payment_type=instant&last_name=Luetke&address_state=Ontario&receiver_email=tobi%40leetsoft.com&payment_fee=&receiver_id=UQ8PDYXJZQD9Y&txn_type=web_accept&item_name=Store+Purchase&mc_currency=CAD&item_number=&test_ipn=1&payment_gross=&shipping=0.00';
    }
    private function _http_raw_data_with_period()
    {
        return 'mc_gross=500.00&address_status=confirmed&payer_id=EVMXCLDZJV77Q&tax=0.00&address_street=164+Waverley+Street&payment_date=15%3A23%3A54+Apr+15%2C+2005+PDT&payment_status=Completed&address_zip=K2P0V6&first_name=Tobias&mc_fee=15.05&address_country_code=CA&address_name=Tobias+Luetke&notify_version=1.7&custom=&payer_status=unverified&business=tobi%40leetsoft.com&address_country=Canada&address_city=Ottawa&quantity=1&payer_email=tobi%40snowdevil.ca&verify_sign=AEt48rmhLYtkZ9VzOGAtwL7rTGxUAoLNsuf7UewmX7UGvcyC3wfUmzJP&txn_id=6G996328CK404320L&payment_type=instant&last_name=Luetke&address_state=Ontario&receiver_email=tobi%40leetsoft.com&payment_fee=&receiver_id=UQ8PDYXJZQD9Y&txn_type=web_accept&item_name=Store+Purchase&mc_currency=CAD&item_number=&test_ipn=1&payment_gross=&shipping=0.00&checkout.x=clicked';
    }
}


