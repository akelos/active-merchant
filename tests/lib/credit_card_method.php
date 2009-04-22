<?php

require_once(dirname(__FILE__). '/../config.php');
ActiveMerchant::import('credit_card_method');

class ActiveMerchantCreditCardMethodTestCase extends ActiveMerchantUnitTest
{
    public function maestro_card_numbers()
    {
        return array(
            '5000000000000000', 
            '5099999999999999', 
            '5600000000000000', 
            '5899999999999999', 
            '6000000000000000', 
            '6999999999999999'
        );
    }
    public function non_maestro_card_numbers()
    {
        return array(
            '4999999999999999', 
            '5100000000000000', 
            '5599999999999999', 
            '5900000000000000', 
            '5999999999999999', 
            '7000000000000000'
        );
    }
    public function test_should_be_able_to_identify_valid_expiry_months()
    {
        $this->assertFalse(ActiveMerchantCreditCardMethod::isValidMonth(-1));
        $this->assertFalse(ActiveMerchantCreditCardMethod::isValidMonth(13));
        $this->assertFalse(ActiveMerchantCreditCardMethod::isValidMonth(null));
        $this->assertFalse(ActiveMerchantCreditCardMethod::isValidMonth(''));
        foreach(range(1, 12) as $m) {
            $this->assertTrue(ActiveMerchantCreditCardMethod::isValidMonth($m));
        }
    }
    public function test_should_be_able_to_identify_valid_expiry_years()
    {
        $this->assertFalse(ActiveMerchantCreditCardMethod::isValidExpiryYear(-1));
        $this->assertFalse(ActiveMerchantCreditCardMethod::isValidExpiryYear(date('Y') + 21));
        foreach(range(0, 20) as $y) {
            $this->assertTrue(ActiveMerchantCreditCardMethod::isValidExpiryYear(date('Y') + $y));
        }
    }
    public function test_should_be_able_to_identify_valid_start_years()
    {
        $this->assertTrue(ActiveMerchantCreditCardMethod::isValidStartYear(1988));
        $this->assertTrue(ActiveMerchantCreditCardMethod::isValidStartYear(2007));
        $this->assertTrue(ActiveMerchantCreditCardMethod::isValidStartYear(3000));
        $this->assertFalse(ActiveMerchantCreditCardMethod::isValidStartYear(1987));
    }
    public function test_should_be_able_to_identify_valid_issue_numbers()
    {
        $this->assertTrue(ActiveMerchantCreditCardMethod::isValidIssueNumber(1));
        $this->assertTrue(ActiveMerchantCreditCardMethod::isValidIssueNumber(10));
        $this->assertTrue(ActiveMerchantCreditCardMethod::isValidIssueNumber('12'));
        $this->assertTrue(ActiveMerchantCreditCardMethod::isValidIssueNumber(0));
        $this->assertFalse(ActiveMerchantCreditCardMethod::isValidIssueNumber(-1));
        $this->assertFalse(ActiveMerchantCreditCardMethod::isValidIssueNumber(123));
        $this->assertFalse(ActiveMerchantCreditCardMethod::isValidIssueNumber('CAT'));
    }
    public function _test_should_ensure_type_from_credit_card_class_is_not_frozen()
    {
        $this->assertFalse(ActiveMerchantCreditCardMethod::isType('4242424242424242')->isFrozen());
    }
    public function test_should_be_different_card_types()
    {
        $this->assertEqual('dankort', ActiveMerchantCreditCardMethod::isType('5019717010103742'));
        $this->assertEqual('visa', ActiveMerchantCreditCardMethod::isType('4175001000000000'));
        $this->assertEqual('visa', ActiveMerchantCreditCardMethod::isType('4175001000000000'));
        $this->assertEqual('diners_club', ActiveMerchantCreditCardMethod::isType('36148010000000'));
        $this->assertEqual('diners_club', ActiveMerchantCreditCardMethod::isType('30401000000000'));
        $this->assertEqual('maestro', ActiveMerchantCreditCardMethod::isType('6769271000000000'));
        $this->assertEqual('master', ActiveMerchantCreditCardMethod::isType('6771890000000000'));
        $this->assertEqual('master', ActiveMerchantCreditCardMethod::isType('5413031000000000'));
        $this->assertEqual('forbrugsforeningen', ActiveMerchantCreditCardMethod::isType('6007221000000000'));
        $this->assertEqual('laser', ActiveMerchantCreditCardMethod::isType('6304985028090561'));
        $this->assertEqual('laser', ActiveMerchantCreditCardMethod::isType('630498502809056151'));
        $this->assertEqual('laser', ActiveMerchantCreditCardMethod::isType('6304985028090561515'));
        
        $this->assertNotEqual('laser', ActiveMerchantCreditCardMethod::isType('63049850280905615'));
        $this->assertNotEqual('laser', ActiveMerchantCreditCardMethod::isType('630498502809056'));
        $this->assertEqual('laser', ActiveMerchantCreditCardMethod::isType('6706950000000000000'));
    }
    public function test_should_detect_maestro_cards()
    {
        $arr = $this->maestro_card_numbers();
        foreach($arr as $number) {
            $this->assertEqual('maestro', ActiveMerchantCreditCardMethod::isType($number));
        }
        $arr = $this->non_maestro_card_numbers();
        foreach($arr as $number) {
            $this->assertNotEqual('maestro', ActiveMerchantCreditCardMethod::isType($number));
        }
    }
    public function test_detecting_full_range_of_maestro_card_numbers()
    {
        $maestro = '50000000000';
        $this->assertEqual(11, strlen($maestro));
        $this->assertNotEqual('maestro', ActiveMerchantCreditCardMethod::isType($maestro));
        while(strlen($maestro) < 19) {
            $maestro .= '0';
            $this->assertEqual('maestro', ActiveMerchantCreditCardMethod::isType($maestro));
        }
    }
    public function test_matching_discover_card()
    {
        $this->assertTrue(ActiveMerchantCreditCardMethod::isMatchingType('6011000000000000', 'discover'));
        $this->assertTrue(ActiveMerchantCreditCardMethod::isMatchingType('6500000000000000', 'discover'));
        $this->assertFalse(ActiveMerchantCreditCardMethod::isMatchingType('6010000000000000', 'discover'));
        $this->assertFalse(ActiveMerchantCreditCardMethod::isMatchingType('6600000000000000', 'discover'));
    }
    public function test_should_detect_when_an_argument_type_does_not_match_calculated_type()
    {
        $this->assertTrue(ActiveMerchantCreditCardMethod::isMatchingType('4175001000000000', 'visa'));
        $this->assertFalse(ActiveMerchantCreditCardMethod::isMatchingType('4175001000000000', 'master'));
    }
    public function test_16_digit_maestro_uk()
    {
        $num = '6759000000000000';
        $this->assertEqual(16, strlen($num));
        $this->assertEqual('switch', ActiveMerchantCreditCardMethod::isType($num));
    }
    public function test_18_digit_maestro_uk()
    {
        $num = '675900000000000000';
        $this->assertEqual(18, strlen($num));
        $this->assertEqual('switch', ActiveMerchantCreditCardMethod::isType($num));
    }
    public function test_19_digit_maestro_uk()
    {
        $num = '6759000000000000000';
        $this->assertEqual(19, strlen($num));
        $this->assertEqual('switch', ActiveMerchantCreditCardMethod::isType($num));
    }
    public function test_last_digits_should_return_last_4_numbers()
    {
        $this->assertEqual('0000', ActiveMerchantCreditCardMethod::lastDigits('6759000000000000000'));
        $this->assertEqual('1234', ActiveMerchantCreditCardMethod::lastDigits('6759000000000001234'));
        $this->assertEqual('123', ActiveMerchantCreditCardMethod::lastDigits('123'));
    }
    public function test_should_mask_cc_number()
    {
        $this->assertEqual('XXXX-XXXX-XXXX-0000', ActiveMerchantCreditCardMethod::mask('6759000000000000000'));
        $this->assertEqual('XXXX-XXXX-XXXX-1234', ActiveMerchantCreditCardMethod::mask('6759000000000001234'));
        $this->assertEqual('XXXX-XXXX-XXXX-123', ActiveMerchantCreditCardMethod::mask('123'));
    }
}
