<?php

require_once (dirname(__FILE__) . '/../config.php');

ActiveMerchant::import('credit_card', 'world_pay');

class ActiveMerchantFullIntegrationTestCase extends ActiveMerchantUnitTest
{
    public function test_should_create_a_cc_object()
    {
        // Create a new credit card object
        $credit_card = new ActiveMerchantCreditCard(array(
            'number' => '4111111111111111', 
            'month' => '8', 
            'year' => '2009', 
            'first_name' => 'Tobias', 
            'last_name' => 'Luetke', 
            'verification_value' => '123'
        ));
        //print_r($credit_card);
        
        if($credit_card->isValid()) {
            
            // Create a gateway object to the TrustCommerce service
            $gateway = new ActiveMerchantWorldPayGateway(array(
                'login' => 'TestMerchant', 
                'password' => 'password'
            ));
            
            // Authorize for $10 dollars (1000 cents) 
            $response = $gateway->authorize(1000, $credit_card);
            
            if($response->isSuccess()) {
                // Capture the money
                $gateway->capture(1000, $response->getAuthorization());
            } else {
                throw new Exception($response->getMessage);
            }
        }
    }
}
