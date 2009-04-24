<?php

require_once dirname(__FILE__) . '/../config.php';

ActiveMerchant::import('response');

class ActiveMerchantResponseTestCase extends ActiveMerchantUnitTest
{
    public function test_response_success()
    {
        $r = new ActiveMerchantResponse(true, 'message', array(
            'param' => 'value'
        ));
        $this->assertTrue($r->isSuccess());
        $r = new ActiveMerchantResponse(false, 'message', array(
            'param' => 'value'
        ));
        $this->assertFalse($r->isSuccess());
    }
    public function test_get_params()
    {
        $r = new ActiveMerchantResponse(true, 'message', array(
            'param' => 'value'
        ));
        $this->assertTrue(key_exists('param', $r->params));
    }
    public function _test_avs_result()
    {
        $r = new ActiveMerchantResponse(true, 'message', array(), array(
            'avs_result' => array(
                'code' => 'A', 
                'street' => 'Y', 
                'zip_match' => 'N'
            )
        ));
        $avs_result = $r->avs_result;
        $this->assertEqual('A', $avs_result['code']);
        $this->assertEqual(ActiveMerchantAVSResult::$message['A'], $avs_result['message']);
    }
    public function _test_cvv_result()
    {
        $r = new ActiveMerchantResponse(true, 'message', array(), array(
            'cvv_result' => 'M'
        ));
        $cvv_result = $r->cvv_result;
        $this->assertEqual('M', $cvv_result['code']);
        $this->assertEqual(ActiveMerchantCVVResult::$message['M'], $cvv_result['message']);
    }
}
