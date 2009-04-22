<?php

class ActiveMerchantCreditCardTestHelper
{
    public static function getCreditCard($number = '4242424242424242', Array $options = array())
    {
        $defaults = array(
            'number' => $number, 
            'month' => 9, 
            'year' => date('Y') + 1, 
            'first_name' => 'Longbob', 
            'last_name' => 'Longsen',
            'verification_value' => '123', 
            'type' => 'visa'
        );
        ActiveMerchant::import('credit_card');
        return new ActiveMerchantCreditCard(array_merge($defaults, $options));
    }
    /*
    public function assertValid($validateable) {
    }
    
    def assert_valid(validateable)
      clean_backtrace do
        assert validateable.valid?, "Expected to be valid"
      end
    end
    
    def assert_not_valid(validateable)
      clean_backtrace do
        assert_false validateable.valid?, "Expected to not be valid"
      end
    end
    */
}
