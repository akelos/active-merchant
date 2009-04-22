<?php

require_once(dirname(__FILE__). '/../config.php');

ActiveMerchant::import('credit_card_formatting');

class ActiveMerchantCreditCardFormattingTestCase extends ActiveMerchantUnitTest
{
    public function test_should_format_number_by_rule()
    {
        $this->assertEqual(2005, ActiveMerchantCreditCardFormatting::format(2005, array('steven_colbert')));
        
        $this->assertEqual('0005', ActiveMerchantCreditCardFormatting::format(5, array('four_digits')));
        $this->assertEqual('0005', ActiveMerchantCreditCardFormatting::format('5', array('four_digits')));
        $this->assertEqual('0005', ActiveMerchantCreditCardFormatting::format(05, array('four_digits')));
        $this->assertEqual('0005', ActiveMerchantCreditCardFormatting::format('05', array('four_digits')));
        $this->assertEqual('2005', ActiveMerchantCreditCardFormatting::format(2005, array('four_digits')));
        $this->assertEqual('2005', ActiveMerchantCreditCardFormatting::format('2005', array('four_digits')));
        $this->assertEqual('2005', ActiveMerchantCreditCardFormatting::format(22005, array('four_digits')));
        $this->assertEqual('2005', ActiveMerchantCreditCardFormatting::format('22005', array('four_digits')));
        
        $this->assertEqual('05', ActiveMerchantCreditCardFormatting::format(5, array('two_digits')));
        $this->assertEqual('05', ActiveMerchantCreditCardFormatting::format('5', array('two_digits')));
        $this->assertEqual('05', ActiveMerchantCreditCardFormatting::format(05, array('two_digits')));
        $this->assertEqual('05', ActiveMerchantCreditCardFormatting::format('05', array('two_digits')));
        $this->assertEqual('05', ActiveMerchantCreditCardFormatting::format(2005, array('two_digits')));
        $this->assertEqual('05', ActiveMerchantCreditCardFormatting::format('2005', array('two_digits')));
        $this->assertEqual('05', ActiveMerchantCreditCardFormatting::format(22005, array('two_digits')));
        $this->assertEqual('05', ActiveMerchantCreditCardFormatting::format('22005', array('two_digits')));
        
        $this->assertEqual('', ActiveMerchantCreditCardFormatting::format(null, array('two_digits')));
        $this->assertEqual('', ActiveMerchantCreditCardFormatting::format('', array('two_digits')));
    }
}
