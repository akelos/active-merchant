<?php

require_once dirname(__FILE__) . '/../config.php';

ActiveMerchant::import('world_pay', 'money');

require_once dirname(__FILE__) . '/../helpers/credit_card_test_helper.php';

class ActiveMerchantWorldPayTestCase extends ActiveMerchantUnitTest
{
    public $g;
    
    public function setup()
    {
        ActiveMerchantBase::setGatewayMode('test');
        $this->g = new ActiveMerchantWorldPayGateway(array(
            'login' => 'X', 
            'password' => 'Y'
        ));
        $this->m = new ActiveMerchantMoney(1000, 'USD');
        $this->credit_card = ActiveMerchantCreditCardTestHelper::getCreditCard();
        $this->address = ActiveMerchantCreditCardTestHelper::getAddress();
    }
    public function test_order_request_should_return_a_valid_xml_string()
    {
        $this->g->authorize($this->m, $this->credit_card);
        $ret = $this->g->getXmlOrder();
        try {
            $xml = new SimpleXMLElement($ret);
            $this->assertTrue(true);
        } catch(Exception $e) {
            $this->assertTrue(false);
        }
    }
    public function test_xml_request_parsing()
    {
        $this->g->authorize($this->m, $this->credit_card);
        $s = $this->g->getXmlOrder();
        $x = $this->g->parse('request', $s);
        $this->assertEqual($x['order'], '1234');
        $this->assertEqual($x['installationId'], '1234');
        $this->assertEqual($x['description'], 'asdf');
        $this->assertEqual($x['amount'], '1000');
        $this->assertEqual($x['currency'], 'USD');
        $this->assertEqual($x['content'], 'asdf');
        $this->assertEqual($x['cardNumber'], '4242424242424242');
        $this->assertEqual($x['month'], '9');
        $this->assertEqual($x['year'], '2010');
        $this->assertEqual($x['cardHolderName'], 'Longbob Longsen');
        $this->assertEqual($x['cvc'], '123');
        $this->assertEqual($x['ip'], '1.1.1.1');
        $this->assertEqual($x['session_id'], '1234234');
        $this->assertEqual($x['email'], 'asdf@asd.com');
    }
    public function test_order_should_be_successful()
    {
        $ret = $this->g->authorize($this->m, $this->credit_card);
        $this->assertTrue($ret->isSuccess());
        $this->assertTrue(empty($ret->message));
        $this->assertTrue($ret->params['orderCode'] == 'T0211010');
    }
    public function test_xml_response_parsing_01()
    {
        $s = $this->_01xmlResponseSuccess();
        $x = $this->g->parse('response', $s);
        $this->assertEqual($x['status'], 'AUTHORISED');
        $this->assertEqual($x['orderCode'], 'T0211010');
        $this->assertEqual($x['paymentMethod'], 'VISA-SSL');
        $this->assertEqual($x['amount'], '1400');
        $this->assertEqual($x['currency'], 'GBP');
        $this->assertEqual($x['tx_type'], 'credit');
        $this->assertEqual($x['balance_type'][0]['tx_type'], 'credit');
        $this->assertEqual($x['balance_type'][0]['type'], 'IN_PROCESS_AUTHORISED');
        $this->assertEqual($x['balance_type'][0]['amount'], '1400');
        $this->assertEqual($x['balance_type'][0]['currency'], 'GBP');
    }
    public function test_xml_response_parsing_03()
    {
        $s = $this->_03xmlRresponse();
        $x = $this->g->parse('response', $s);
        $this->assertEqual($x['status'], 'SUCCESS');
        $this->assertEqual($x['orderCode'], 'merchantGeneratedOrderCode');
        $this->assertEqual($x['paRequest'], 'somedata');
        $this->assertEqual($x['issuerURL'], 'http://example.issuer.url/3dsec.html');
        $this->assertEqual($x['echoData'], 'somedata');
    }
    public function test_xml_response_parsing_07()
    {
        $s = $this->_07xmlResponseFailed();
        $x = $this->g->parse('response', $s);
        $this->assertEqual($x['status'], 'REFUSED');
        $this->assertEqual($x['errorCode'], '76');
        $this->assertEqual($x['errorMsg'], 'CARD BLOCKED');
    }
    public function test_xml_response_parsing_09()
    {
        $s = $this->_09xmlResponseSuccess();
        $x = $this->g->parse('response', $s);
        $this->assertEqual($x['status'], 'AUTHORISED');
        $this->assertEqual($x['orderCode'], 'merchantGeneratedOrderCode');
        $this->assertEqual($x['paymentMethod'], 'VISA-SSL');
        $this->assertEqual($x['amount'], '10000');
        $this->assertEqual($x['currency'], 'EUR');
        $this->assertEqual($x['tx_type'], 'credit');
        $this->assertEqual($x['cardNumber'], '4111********1111');
        $this->assertEqual(count($x['balance_type']), 2);
        $this->assertEqual($x['balance_type'][0]['tx_type'], 'credit');
        $this->assertEqual($x['balance_type'][0]['type'], 'IN_PROCESS');
        $this->assertEqual($x['balance_type'][0]['amount'], '10000');
        $this->assertEqual($x['balance_type'][0]['currency'], 'EUR');
        $this->assertEqual($x['balance_type'][1]['tx_type'], 'debit');
        $this->assertEqual($x['balance_type'][1]['type'], 'AUTHORISED');
        $this->assertEqual($x['balance_type'][1]['amount'], '10000');
        $this->assertEqual($x['balance_type'][1]['currency'], 'EUR');
    }
    private function _02xmlOrderRequest()
    {
        return '<?xml version="1.0"?>
<!DOCTYPE paymentService PUBLIC "-//WorldPay/DTD WorldPay PaymentService v1//EN" "http://dtd.worldpay.com/paymentService_v1.dtd">
<paymentService version="1.4" merchantCode="MYMERCHANT">
    <submit>
        <order orderCode="merchantGeneratedOrderCode" installationId="12345">
            <description>Description</description>
            <amount currencyCode="GBP" exponent="2" value="5000" />
            <orderContent>Default Order Content</orderContent>
            <paymentDetails>
                <VISA-SSL>
                    <cardNumber>4111111111111111</cardNumber>
                    <expiryDate>
                        <date month="02" year="2008" />
                    </expiryDate>
                    <cardHolderName>3D</cardHolderName>
                </VISA-SSL>
                <session shopperIPAddress="123.123.123.123" id="112233" />
            </paymentDetails>
            <shopper>
                <browser>
                    <acceptHeader>text/html</acceptHeader>
                    <userAgentHeader>Mozilla/5.0 ...</userAgentHeader>
                </browser>
            </shopper>
        </order>
    </submit>
</paymentService>';
    }
    private function _03xmlRresponse()
    {
        return '<?xml version="1.0"?>
<!DOCTYPE paymentService PUBLIC "-//WorldPay/DTD WorldPay PaymentService v1//EN" "http://dtd.worldpay.com/paymentService_v1.dtd">
<paymentService merchantCode="MYMERCHANT" version="1.4">
    <reply>
        <orderStatus orderCode="merchantGeneratedOrderCode">
            <requestInfo>
                <request3DSecure>
                    <paRequest>somedata</paRequest>
                    <issuerURL>http://example.issuer.url/3dsec.html</issuerURL>
                </request3DSecure>
            </requestInfo>
            <echoData>somedata</echoData>
        </orderStatus>
    </reply>
</paymentService>';
    }
    private function _04htmlRedirect()
    {
        return '<html>
<head>
<title>3-D Secure helper page</title>
</head>
<body OnLoad="OnLoadEvent();">
This page should forward you to your own card issuer for identification. If your browser does not start loading the page, press the button you see.
<br/>
After you successfully identify yourself you will be sent back to this site where the payment process will continue as if nothing had happened.<br/>    implemented...
<form name="theForm" method="POST" action="1234" >
<input type="hidden" name="PaReq" value="4321" />
<input type="hidden" name="TermUrl" value="http://www.asdf.com/almostdone" />
<input type="hidden" name="MD" value="sessid_01234" />
<input type="submit" name="Identify yourself" />
</form>
<script language="Javascript">
<!--
function OnLoadEvent() {document.theForm.submit();}
// -->
</script>
</body>
</html>';
    }
    private function _06xmlOrderRequest()
    {
        return '<?xml version="1.0"?>
<!DOCTYPE paymentService PUBLIC "-//WorldPay/DTD WorldPay PaymentService v1//EN" "http://dtd.worldpay.com/paymentService_v1.dtd">
<paymentService merchantCode="MYMERCHANT" version="1.4">
    <submit>
        <order orderCode="merchantGeneratedOrderCode" installationId="12345">
            <description>Description</description>
            <amount currencyCode="GBP" exponent="2" value="5000" />
            <orderContent>Default Order Content</orderContent>
            <paymentDetails>
                <VISA-SSL>
                    <cardNumber>4111111111111111</cardNumber>
                    <expiryDate>
                        <date month="02" year="2006" />
                    </expiryDate>
                    <cardHolderName>3D</cardHolderName>
                </VISA-SSL>
                <session shopperIPAddress="123.123.123.123" id="112233" />
                <info3DSecure>
                    <paResponse>somedata</paResponse>
                </info3DSecure>
            </paymentDetails>
            <shopper>
                <browser>
                    <acceptHeader>text/html</acceptHeader>
                    <userAgentHeader>Mozilla/5.0 ...</userAgentHeader>
                </browser>
            </shopper>
            <echoData>somedata</echoData>
        </order>
    </submit>
</paymentService>';
    }
    private function _07xmlResponseFailed()
    {
        return '<?xml version="1.0"?>
<!DOCTYPE paymentService PUBLIC "-//WorldPay/DTD WorldPay PaymentService v1//EN" "http://dtd.worldpay.com/paymentService_v1.dtd">
<payment>
    <paymentMethod>MAESTRO-SSL</paymentMethod>
    <amount value="2750" currencyCode="GBP" exponent="2" debitCreditIndicator="credit" />
    <lastEvent>REFUSED</lastEvent>
    <ISO8583ReturnCode code="76" description="CARD BLOCKED" />
</payment>';
    }
    private function _09xmlResponseSuccess()
    {
        return '<?xml version="1.0"?>
<!DOCTYPE paymentService PUBLIC "-//WorldPay/DTD WorldPay PaymentService v1//EN" "http://dtd.worldpay.com/paymentService_v1.dtd">
<paymentService merchantCode="MYMERCHANT" version="1.4">
    <reply>
        <orderStatus orderCode="merchantGeneratedOrderCode">
            <payment>
                <paymentMethod>VISA-SSL</paymentMethod>
                <amount currencyCode="EUR" debitCreditIndicator="credit" exponent="2" value="10000" />
                <lastEvent>AUTHORISED</lastEvent>
                <balance accountType="IN_PROCESS">
                    <amount currencyCode="EUR" debitCreditIndicator="credit" exponent="2" value="10000" />
                </balance>
                <balance accountType="AUTHORISED">
                    <amount currencyCode="EUR" debitCreditIndicator="debit" exponent="2" value="10000" />
                </balance>
                <cardNumber>4111********1111</cardNumber>
            </payment>
        </orderStatus>
    </reply>
</paymentService>';
    }
    // -------------------------------------------------------------------------
    // -------------------------------------------------------------------------
    private function _02xmlOrderRequestBESTMATCH()
    {
        return '<?xml version="1.0"?>
<!DOCTYPE paymentService PUBLIC "-//WorldPay/DTD WorldPay PaymentService v1//EN" "http://dtd.worldpay.com/paymentService_v1.dtd">
<paymentService version="1.3">
    <submit>
        <order orderCode="0812114512-MatchBestMerchantCode" installationId="204369">
            <description>IT Products</description>
            <amount value="10000" currencyCode="EUR" exponent="2" />
            <orderContent>Brulaap</orderContent>
            <paymentDetails>
                <VISA-SSL>
                    <cardNumber>4111111111111111</cardNumber>
                    <expiryDate>
                        <date month="02" year="2008" />
                    </expiryDate>
                    <cardHolderName>3D</cardHolderName>
                </VISA-SSL>
                <session shopperIPAddress="123.123.123.123" id="112233" />
            </paymentDetails>
            <shopper>
                <browser>
                    <acceptHeader>text/html</acceptHeader>
                    <userAgentHeader>Mozilla/5.0 ...</userAgentHeader>
                </browser>
            </shopper>
        </order>
    </submit>
</paymentService>';
    }
    private function _09xmlResponseSuccessBESTMATCH()
    {
        return '<?xml version="1.0"?>
<!DOCTYPE paymentService PUBLIC "-//WorldPay/DTD WorldPay PaymentService v1//EN" "http://dtd.worldpay.com/paymentService_v1.dtd">
<paymentService version="1.4" merchantCode="JOEBLOGGS">
    <reply>
        <orderStatus orderCode="0812114512-MatchBestMerchantCode">
            <payment>
                <paymentMethod>VISA-SSL</paymentMethod>
                <amount value="10000" currencyCode="EUR" exponent="2" debitCreditIndicator="credit" />
                <lastEvent>AUTHORISED</lastEvent>
                <balance accountType="IN_PROCESS_AUTHORISED">
                    <amount value="10000" currencyCode="EUR" exponent="2" debitCreditIndicator="credit" />
                </balance>
                <cardNumber>4111********1111</cardNumber>
            </payment>
        </orderStatus>
    </reply>
</paymentService>';
    }
    public function _01xmlOrder()
    {
        return '<?xml version="1.0"?>
<!DOCTYPE paymentService PUBLIC "-//WorldPay/DTD WorldPay PaymentService v1//EN" "http://dtd.worldpay.com/paymentService_v1.dtd">
<paymentService version="1.4" merchantCode="WPACC11112222">
    <submit>
        <order orderCode="T0211010" installationId="12345">
            <description>20 English Roses from MYMERCHANT Webshops</description>
            <amount value="1400" currencyCode="GBP" exponent="2" />
            <orderContent>Default Order Content</orderContent>
            <paymentDetails>
                <VISA-SSL>
                    <cardNumber>4444333322221111</cardNumber>
                    <expiryDate>
                        <date month="09" year="2007" />
                    </expiryDate>
                    <cardHolderName>J. Shopper</cardHolderName>
                    <cvc>123</cvc>
                    <cardAddress>
                        <address>
                            <firstName>John</firstName>
                            <lastName>Shopper</lastName>
                            <street>47A Queensbridge Rd</street>
                            <postalCode>CB94BQ</postalCode>
                            <city>Cambridge</city>
                            <countryCode>GB</countryCode>
                            <telephoneNumber>01234567890</telephoneNumber>
                        </address>
                    </cardAddress>
                </VISA-SSL>
                <session shopperIPAddress="123.123.123.123" id="0215ui8ib1" />
            </paymentDetails>
            <shopper>
                <shopperEmailAddress>jshopper@myprovider.int</shopperEmailAddress>
            </shopper>
            <shippingAddress>
                <address>
                    <firstName>John</firstName>
                    <lastName>Shopper</lastName>
                    <street>47A Queensbridge Rd</street>
                    <postalCode>CB94BQ</postalCode>
                    <countryCode>GB</countryCode>
                    <telephoneNumber>01234567890</telephoneNumber>
                </address>
            </shippingAddress>
        </order>
    </submit>
</paymentService>';
    }
    private function _01xmlResponseSuccess()
    {
        return '<?xml version="1.0"?>
<!DOCTYPE paymentService PUBLIC "-//WorldPay//DTD WorldPay PaymentService v1//EN"
"http://dtd.worldpay.com/paymentService_v1.dtd">
<paymentService merchantCode="WPACC11112222" version="1.4">
  <reply>
    <orderStatus orderCode="T0211010">
      <payment>
        <paymentMethod>VISA-SSL</paymentMethod>
        <amount value="1400" currencyCode="GBP" exponent="2" debitCreditIndicator="credit"/>
        <lastEvent>AUTHORISED</lastEvent>
        <CVCResultCode description="APPROVED"/>
        <balance accountType="IN_PROCESS_AUTHORISED">
           <amount value="1400" currencyCode="GBP" exponent="2" debitCreditIndicator="credit"/>
        </balance>
        <cardNumber>4444********1111</cardNumber>
      </payment>
    </orderStatus>
  </reply>
</paymentService>';
    }
}
