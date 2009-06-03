<?php
class ActiveMerchantWorldPayGateway extends ActiveMerchantGateway
{
    private function _testCardHolderName()
    {
        $arr = array(
            '3D',  // Test environment will act as if the credit card is participating in 3‐D Secure (that is, the 3‐D Security Directory would respond with enrolled) 
            'NO3D',  // Test environment will act as if the credit card is not participating in 3‐D Secure (that is, the Directory would respond with not‐enrolled)
            'whatever',  // Test environment will act as if it is a normal e‐commerce transaction (that is, no lookup with the Directory)
            

            'REFUSED',  // will simulate a refused payment 
            'REFERRED',  // will simulate a refusal with the refusal reason ‘referredʹ 
            'FRAUD',  // will simulate a refusal with the refusal reason ‘fraud suspicionʹ 
            'ERROR' // will simulate a payment that ends in error.
        );
    }
    private function _testPaResponse()
    {
        $arr = array(
            'IDENTIFIED',  // The shopperʹs identity is successfully verified 
            'NOT_IDENTIFIED',  // The shopperʹs identification could not be verified
            'UNKNOWN_IDENTITY',  // The authentication failed
            'whatever' // The verification process itself failed
        );
    }
    private function _testCcNumber()
    {
        $arr = array(
            '4484070000000000',  // Visa 
            '5100080000000000',  // Mastercard  
            '4406080400000000',  // Visa Delta ‐ UK  
            '4462030000000000',  // Visa Delta ‐ Non‐UK  
            '4911830000000',  // Visa  
            '4917610000000000',  // Visa
            '370000200000000',  // American Express
            '36700102000000',  // Diners  
            '3528000700000000',  // JCB  
            '4917300800000000',  // Visa Electron  
            '6334580500000000',  // Solo  
            '633473060000000000',  // Solo
            '6011000400000000',  // Discover card  
            '630495060000000000',  // Laser  
            '5700000000000000000' // Maestro  
        );
    }
    private function _testGermanElv()
    {
        $arr = array(
            array(
                'bank_code' => '20030000', 
                'account_number' => '92441196'
            ), 
            array(
                'bank_code' => '43050001', 
                'account_number' => '122108525'
            ), 
            array(
                'bank_code' => '30070024', 
                'account_number' => '5929120'
            )
        );
    }
}