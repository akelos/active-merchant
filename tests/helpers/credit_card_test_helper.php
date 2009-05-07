<?php

class ActiveMerchantCreditCardTestHelper
{
    public static function getCreditCard($number = '4242424242424242', Array $options = array())
    {
        $defaults = array(
            'number' => $number, 
            'month' => 9, 
            'year' => date('Y') + 1, 
            'name' => 'Longbob Longsen', 
            //            'first_name' => 'Longbob', 
            //            'last_name' => 'Longsen', 
            'verification_value' => '123', 
            'type' => 'visa'
        );
        ActiveMerchant::import('credit_card');
        return new ActiveMerchantCreditCard(array_merge($defaults, $options));
    }
    public static function getAddress($options = array())
    {
        $defaults = array(
            'name' => 'Jim Smith', 
            'address1' => '1234 My Street', 
            'address2' => 'Apt 1', 
            'company' => 'Widgets Inc', 
            'city' => 'Ottawa', 
            'state' => 'ON', 
            'zip' => 'K1C2N6', 
            'country' => 'CA', 
            'phone' => '(555)555-5555', 
            'fax' => '(555)555-6666'
        );
        return array_merge($defaults, $options);
    }
}
