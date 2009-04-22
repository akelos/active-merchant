<?php

require_once(dirname(__FILE__). '/../config.php');

ActiveMerchant::import('country_code');

class ActiveMerchantCountryCodeTestCase extends ActiveMerchantUnitTest
{
    public function test_alpha2_country_code()
    {
        $code = new ActiveMerchantCountryCode('CA');
        $this->assertEqual('CA', $code->getValue());
        $this->assertEqual('CA', $code);
        $this->assertEqual('alpha2', $code->getFormat());
    }
    public function test_lower_alpha2_country_code()
    {
        $code = new ActiveMerchantCountryCode('ca');
        $this->assertEqual('CA', $code->getValue());
        $this->assertEqual('CA', $code);
        $this->assertEqual('alpha2', $code->getFormat());
    }
    public function test_alpha3_country_code()
    {
        $code = new ActiveMerchantCountryCode('CAN');
        $this->assertEqual('alpha3', $code->getFormat());
    }
    public function test_numeric_code()
    {
        $code = new ActiveMerchantCountryCode('004');
        $this->assertEqual('numeric', $code->getFormat());
    }
    public function test_invalid_code_format()
    {
        try {
            new ActiveMerchantCountryCode('Canada');
            $this->assertTrue(false);
        } catch(ActiveMerchantCountryCodeFormatException $e) {
            $this->assertTrue(true);
            $this->assertEqual('The country code is not formatted correctly CANADA', $e->getMessage());
        }
    }
}
