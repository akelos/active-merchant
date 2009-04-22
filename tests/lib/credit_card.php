<?php

require_once(dirname(__FILE__). '/../config.php');
include_once AK_ACTIVE_MERCHANT_DIR . DS . 'tests' . DS. 'helpers' . DS . 'credit_card_test_helper.php';

ActiveMerchant::import('credit_card');

class ActiveMerchantCreditCardTestCase extends ActiveMerchantUnitTest
{
    public $visa, $solo;
    
    public function setup()
    {
        ActiveMerchantCreditCard::setRequireVerificationValue(false);
        $this->visa = ActiveMerchantCreditCardTestHelper::getCreditCard('4779139500118580', array(
            'type' => 'visa'
        ));
        $this->solo = ActiveMerchantCreditCardTestHelper::getCreditCard('676700000000000000', array(
            'type' => 'solo', 
            'issue_number' => '01'
        ));
    }
    public function teardown()
    {
        ActiveMerchantCreditCard::setRequireVerificationValue(false);
    }
    function test_setup()
    {
        $this->includeAndInstatiateModels('ActiveMerchantCreditCard');
    }
    public function test_constructor_should_properly_assign_values()
    {
        $c = ActiveMerchantCreditCardTestHelper::getCreditCard();
        $this->assertEqual('4242424242424242', $c->number);
        $this->assertEqual(9, $c->month);
        $this->assertEqual('Longbob Longsen', $c->name);
        $this->assertEqual('visa', $c->type);
        $this->assertTrue($c->isValid());
    }
    public function test_new_credit_card_should_not_be_valid()
    {
        $c = new ActiveMerchantCreditCard();
        $this->assertFalse($c->isValid());
        $this->assertFalse(!$c->hasErrors());
    }
    public function test_should_be_a_valid_visa_card()
    {
        $this->assertTrue($this->visa->isValid());
        $this->assertTrue(!$this->visa->hasErrors());
    }
    public function test_should_be_a_valid_solo_card()
    {
        $this->assertTrue($this->solo->isValid());
        $this->assertFalse($this->solo->hasErrors());
    }
    public function test_cards_with_empty_names_should_not_be_valid()
    {
        $this->visa->first_name = '';
        $this->visa->last_name = '';
        $this->assertFalse($this->visa->isValid());
        $this->assertTrue($this->visa->hasErrors());
    }
    public function test_should_be_able_to_access_errors_indifferently()
    {
        $this->visa->first_name = '';
        $this->assertFalse($this->visa->isValid());
        $this->assertTrue($this->visa->hasErrors());
        $this->assertTrue($this->visa->getErrorsOn('first_name'));
    }
    public function test_should_be_able_to_liberate_a_bogus_card()
    {
        $c = ActiveMerchantCreditCardTestHelper::getCreditCard('', array('type' => 'bogus'));
        $this->assertTrue($c->isValid());
        $c->type = 'visa';
        $this->assertFalse($c->isValid());
    }
    public function test_should_be_able_to_identify_invalid_card_numbers()
    {
        $this->visa->number = null;
        $this->assertFalse($this->visa->isValid());

        $this->visa->number = '11112222333344ff';
        $this->assertFalse($this->visa->isValid());
        // BOTH type AND number have errors...
        //$this->assertFalse($this->visa->getErrorsOn('type'));
        $this->assertTrue($this->visa->getErrorsOn('number'));

        $this->visa->number = '111122223333444';
        $this->assertFalse($this->visa->isValid());
        //$this->assertFalse($this->visa->getErrorsOn('type'));
        $this->assertTrue($this->visa->getErrorsOn('number'));

        $this->visa->number = '11112222333344444';
        $this->assertFalse($this->visa->isValid());
        //$this->assertFalse($this->visa->getErrorsOn('type'));
        $this->assertTrue($this->visa->getErrorsOn('number'));
    }
    public function test_should_have_errors_with_invalid_card_type_for_otherwise_correct_number()
    {
        $this->visa->type = 'master';
        $this->assertFalse($this->visa->isValid());
        $this->assertNotEqual($this->visa->getErrorsOn('number'), $this->visa->getErrorsOn('type'));
    }
    public function test_should_be_invalid_when_type_cannot_be_detected()
    {
        $this->visa->number = null;
        $this->visa->type = null;
        $this->assertFalse($this->visa->isValid());
        $this->assertTrue($this->visa->getErrorsOn('type'));
        //type has 2 errors!!
        //$this->assertPattern('/is required/', $this->visa->getErrorsOn('type'));
    }
    public function test_should_be_a_valid_card_number()
    {
        $this->visa->number = '4242424242424242';
        $this->assertTrue($this->visa->isValid());
    }
    public function test_should_require_a_valid_card_month()
    {
        $this->visa->month = date('n');
        $this->visa->year = date('Y');
        $this->assertTrue($this->visa->isValid());
    }
    public function test_should_not_be_valid_with_empty_month()
    {
        $this->visa->month = '';
        $this->assertFalse($this->visa->isValid());
        $this->assertTrue($this->visa->getErrorsOn('month'));
    }
    public function test_should_not_be_valid_for_edge_month_cases()
    {
        $this->visa->month = 13;
        $this->visa->year = date('Y');
        $this->assertFalse($this->visa->isValid());
        $this->assertTrue($this->visa->getErrorsOn('month'));
        
        $this->visa->month = 0;
        $this->visa->year = date('Y');
        $this->assertFalse($this->visa->isValid());
        $this->assertTrue($this->visa->getErrorsOn('month'));
    }
    public function test_should_be_invalid_with_empty_year()
    {
        $this->visa->year = '';
        $this->assertFalse($this->visa->isValid());
        $this->assertTrue($this->visa->getErrorsOn('year'));
    }
    public function test_should_not_be_valid_for_edge_year_cases()
    {
        $this->visa->year = date('Y')-1;
        $this->assertFalse($this->visa->isValid());
        $this->assertTrue($this->visa->getErrorsOn('year'));
        
        $this->visa->year = date('Y')+21;
        $this->assertFalse($this->visa->isValid());
        $this->assertTrue($this->visa->getErrorsOn('year'));
    }
    public function test_should_be_a_valid_future_year()
    {
        $this->visa->year = date('Y')+1;
        $this->assertTrue($this->visa->isValid());
    }
    public function test_should_identify_wrong_cardtype()
    {
        $c = ActiveMerchantCreditCardTestHelper::getCreditCard(null, array('type' => 'master'));
        $this->assertFalse($c->isValid());
    }
    public function test_should_display_number() {
        $this->assertEqual('XXXX-XXXX-XXXX-1234', ActiveMerchantCreditCardTestHelper::getCreditCard('1111222233331234')->displayNumber());
        $this->assertEqual('XXXX-XXXX-XXXX-1234', ActiveMerchantCreditCardTestHelper::getCreditCard('111222233331234')->displayNumber());
        $this->assertEqual('XXXX-XXXX-XXXX-1234', ActiveMerchantCreditCardTestHelper::getCreditCard('1112223331234')->displayNumber());
        
        $c = new ActiveMerchantCreditCard(array('number' => null));
        $this->assertEqual('XXXX-XXXX-XXXX-', $c->displayNumber());
        $c = new ActiveMerchantCreditCard(array('number' => ''));
        $this->assertEqual('XXXX-XXXX-XXXX-', $c->displayNumber());
        $c = new ActiveMerchantCreditCard(array('number' => '123'));
        $this->assertEqual('XXXX-XXXX-XXXX-123', $c->displayNumber());
        $c = new ActiveMerchantCreditCard(array('number' => '1234'));
        $this->assertEqual('XXXX-XXXX-XXXX-1234', $c->displayNumber());
        $c = new ActiveMerchantCreditCard(array('number' => '01234'));
        $this->assertEqual('XXXX-XXXX-XXXX-1234', $c->displayNumber());
    }
    public function test_should_correctly_identify_card_type()
    {
        $this->assertEqual('visa', ActiveMerchantCreditCard::isType('4242424242424242'));
        $this->assertEqual('american_express', ActiveMerchantCreditCard::isType('341111111111111'));
        $this->assertNull(ActiveMerchantCreditCard::isType(''));
    }
    public function test_should_be_able_to_require_a_verification_value()
    {
        ActiveMerchantCreditCard::setRequireVerificationValue(true);
        $this->assertTrue(ActiveMerchantCreditCard::isRequireVerificationValue());
        ActiveMerchantCreditCard::setRequireVerificationValue(false);
        $this->assertFalse(ActiveMerchantCreditCard::isRequireVerificationValue());
    }
    public function _test_should_not_be_valid_when_requiring_a_verification_value()
    {
        ActiveMerchantCreditCard::setRequireVerificationValue(true);
        $card = ActiveMerchantCreditCardTestHelper::getCreditCard('4242424242424242', array('verification_value' => null));
        $this->assertNotValid($card);
        $card->verification_value = 123;
        $this->assertValid($card);
    }
    public function test_should_return_last_four_digits_of_card_number()
    {
        $ccn = new ActiveMerchantCreditCard(array('number' => '4779139500118580'));
        $this->assertEqual('8580', $ccn->lastDigits());
    }
    public function test_should_be_true_when_credit_card_has_a_first_name()
    {
        $c = new ActiveMerchantCreditCard();
        $this->assertFalse($c->isFirstName());
        $c = new ActiveMerchantCreditCard(array('first_name' => 'James'));
        $this->assertTrue($c->isFirstName());
    }
    public function test_should_be_true_when_credit_card_has_a_last_name()
    {
        $c = new ActiveMerchantCreditCard();
        $this->assertFalse($c->isLastName());
        $c = new ActiveMerchantCreditCard(array('last_name' => 'Herdman'));
        $this->assertTrue($c->isLastName());
    }
    public function test_should_be_true_when_credit_card_has_a_full_name()
    {
        $c = new ActiveMerchantCreditCard();
        $this->assertFalse($c->isName());
        $c = new ActiveMerchantCreditCard(array('first_name' => 'James', 'last_name' => 'Herdman'));
        $this->assertTrue($c->isName());
    }
    public function test_validate_new_card()
    {
        $cc = new ActiveMerchantCreditCard();
        $cc->validate();
    }
    public function test_create_and_validate_credit_card_from_type()
    {
        $cc = new ActiveMerchantCreditCard(array('type' => ActiveMerchantCreditCardMethod::isType('4242424242424242')));
        $cc->isValid();
    }
    public function test_autodetection_of_credit_card_type()
    {
        $cc = new ActiveMerchantCreditCard(array('number' => '4242424242424242'));
        $cc->isValid();
        $this->assertEqual('visa', $cc->type);
    }
    public function test_card_type_should_not_be_autodetected_when_provided()
    {
        $cc = new ActiveMerchantCreditCard(array('number' => '4242424242424242', 'type' => 'master'));
        $cc->isValid();
        $this->assertEqual('master', $cc->type);
    }
    public function test_should_detect_bogus_card()
    {
        ActiveMerchantBase::mode('test');
        $cc = new ActiveMerchantCreditCard(array('number' => '1'));
        $cc->isValid();
        $this->assertEqual('bogus', $cc->type);
    }
    public function test_should_validate_bogus_card()
    {
        ActiveMerchantBase::mode('test');
        $cc = ActiveMerchantCreditCardTestHelper::getCreditCard('1', array('type' => null));
        $this->assertTrue($cc->isValid());
    }
    public function test_mask_number()
    {
        $this->assertEqual('XXXX-XXXX-XXXX-5100', ActiveMerchantCreditCardMethod::mask('5105105105105100'));
    }
    public function test_should_strip_non_digit_characters()
    {
        $cc = ActiveMerchantCreditCardTestHelper::getCreditCard('4242-4242      %%%%%%4242......4242');
        $this->assertTrue($cc->isValid());
        $this->assertEqual('4242424242424242', $cc->number);
    }
    public function test_before_validate_should_handle_blank_number()
    {
        $cc = ActiveMerchantCreditCardTestHelper::getCreditCard(null);
        $this->assertFalse($cc->isValid());
        $this->assertEqual('', $cc->number);
    }
}
