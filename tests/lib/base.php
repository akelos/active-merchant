<?php

require_once(dirname(__FILE__). '/../config.php');

ActiveMerchant::import('base');

class ActiveMerchantBaseTestCase extends ActiveMerchantUnitTest
{
    public function setup()
    {
        ActiveMerchantBase::$mode = 'test';
    }
    public function teardown()
    {
        ActiveMerchantBase::$mode = 'test';
    }
    public function test_should_return_a_new_gateway_specified_by_symbol_name()
    {
        $this->assertEqual('BogusGateway', ActiveMerchantBase::gateway('bogus'));
        $this->assertEqual('MonerisGateway', ActiveMerchantBase::gateway('moneris'));
        $this->assertEqual('AuthorizeNetGateway', ActiveMerchantBase::gateway('authorize_net'));
        $this->assertEqual('UsaEpayGateway', ActiveMerchantBase::gateway('usa_epay'));
        $this->assertEqual('LinkpointGateway', ActiveMerchantBase::gateway('linkpoint'));
        $this->assertEqual('AuthorizedNetGateway', ActiveMerchantBase::gateway('authorized_net'));
    }
    public function _test_should_return_an_integration_by_name()
    {
        //$chronopay = ActiveMerchantBase::integration('chronopay');
        //$this->assertEqual(Integrations::Chronopay, chronopay)
        //$this->assertIsA($chronopay->notification('name=cody'), 'Integrations::Chronopay::Notification');
    }
    public function test_should_set_modes(){
        //ActiveMerchantBase::$mode = 'test';
        ActiveMerchantBase::mode('test');
        $this->assertEqual('test', ActiveMerchantBase::getMode());
        $this->assertEqual('test', ActiveMerchantBase::getGatewayMode());
        $this->assertEqual('test', ActiveMerchantBase::getIntegrationMode());
    }
}
