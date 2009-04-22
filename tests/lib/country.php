<?php

require_once(dirname(__FILE__). '/../config.php');

ActiveMerchant::import('country');

class ActiveMerchantCountryTestCase extends ActiveMerchantUnitTest
{
    public function test_country_from_hash()
    {
        $country = new ActiveMerchantCountry(array(
            'name' => 'Canada', 
            'alpha2' => 'CA', 
            'alpha3' => 'CAN', 
            'numeric' => '124'
        ));
        $this->assertEqual('CA', $country->code('alpha2'));
        $this->assertEqual('CAN', $country->code('alpha3'));
        $this->assertEqual('124', $country->code('numeric'));
        $this->assertEqual('Canada', $country);
    }
    public function test_country_for_alpha2_code()
    {
        $country = ActiveMerchantCountry::find('CA');
        $this->assertEqual('CA', $country->code('alpha2'));
        $this->assertEqual('CAN', $country->code('alpha3'));
        $this->assertEqual('124', $country->code('numeric'));
        $this->assertEqual('Canada', $country);
    }
    public function test_country_for_alpha3_code()
    {
        $country = ActiveMerchantCountry::find('CAN');
        $this->assertEqual('Canada', $country);
    }
    public function test_country_for_numeric_code()
    {
        $country = ActiveMerchantCountry::find('124');
        $this->assertEqual('Canada', $country);
    }
    public function test_find_country_by_name()
    {
        $country = ActiveMerchantCountry::find('Canada');
        $this->assertEqual('Canada', $country);
    }
    public function test_find_unknown_country_name()
    {
        try {
            $country = ActiveMerchantCountry::find('Asskickistan');
            $this->assertTrue(false);
        } catch(ActiveMerchantInvalidCountryCodeException $e) {
            $this->assertTrue(true);
            $this->assertEqual('No country could be found for the country Asskickistan', $e->getMessage());
        }
    }
    public function test_find_australia()
    {
        $country = ActiveMerchantCountry::find('AU');
        $this->assertEqual('AU', $country->code('alpha2'));
        
        $country = ActiveMerchantCountry::find('Australia');
        $this->assertEqual('AU', $country->code('alpha2'));
    }
    public function test_find_united_kingdom()
    {
        $country = ActiveMerchantCountry::find('GB');
        $this->assertEqual('GB', $country->code('alpha2'));
        
        $country = ActiveMerchantCountry::find('United Kingdom');
        $this->assertEqual('GB', $country->code('alpha2'));
    }
    public function test_raise_on_null_name()
    {
        try {
            $country = ActiveMerchantCountry::find(null);
            $this->assertTrue(false);
        } catch(ActiveMerchantInvalidCountryCodeException $e) {
            $this->assertTrue(true);
            $this->assertEqual('Cannot lookup country for an empty name', $e->getMessage());
        }
    }
}
