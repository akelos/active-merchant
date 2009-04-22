<?php

require_once(dirname(__FILE__). '/../config.php');
ActiveMerchant::import('gateway', 'base');

class ActiveMerchantGatewayTestCase extends ActiveMerchantUnitTest
{
    public $g;
    public function setup()
    {
        $this->g = new ActiveMerchantGateway();
    }
    public function test_should_detect_if_a_card_is_supported()
    {
        $arr = array(
            'visa', 
            'bogus'
        );
        $this->g->supported_cardtypes = $arr;
        foreach($arr as $cct) {
            $this->assertTrue($this->g->isSupported($cct));
        }
        $this->g->supported_cardtypes = array();
        foreach($arr as $cct) {
            $this->assertFalse($this->g->isSupported($cct));
        }
    }
    public function test_should_gateway_uses_ssl_strict_checking_by_default()
    {
        $this->assertTrue($this->g->ssl_strict);
    }
    public function test_should_be_able_to_look_for_test_mode()
    {
        ActiveMerchantBase::setGatewayMode('test');
        $this->assertTrue($this->g->isTest());
        
        ActiveMerchantBase::setGatewayMode('production');
        $this->assertFalse($this->g->isTest());
    }
    public function test_amount_style()
    {
        $this->assertEqual('10.34', $this->g->getAmount(1034));
        try {
            $this->g->getAmount('10.34');
            $this->assertTrue(false);
        } catch(Exception $e) {
            $this->assertTrue(true);
        }
    }
    public function test_invalid_type()
    {
        $cc = array(
            'type' => 'visa'
        );
        $this->assertEqual('visa', $this->g->getCardBrand($cc));
    }
    public function test_invalid_type2()
    {
        $cc = array(
            'type' => 'String', 
            'brand' => 'visa'
        );
        $this->assertEqual('visa', $this->g->getCardBrand($cc));
    }
    public function test_setting_application_id_outside_the_class_definition()
    {
        $a = new ActiveMerchantSimpleTestGateway();
        $b = new ActiveMerchantSubclassGateway();
        $this->assertEqual($a->getApplicationId(), $b->getApplicationId());
        //$a->application_id = 'asdf';???
        $this->assertEqual($a->getApplicationId(), $b->getApplicationId());
    }
}
class ActiveMerchantSimpleTestGateway extends ActiveMerchantGateway
{}
class ActiveMerchantSubclassGateway extends ActiveMerchantSimpleTestGateway
{}