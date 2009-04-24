<?php
ActiveMerchant::import('gateway', 'response');
/**
USAGE
-----
$tendollar = 1000;
$creditcard = new ActiveMerchantCreditCard (
    :number => '4111111111111111',
    :month => 8,
    :year => 2006,
    :first_name => 'Longbob',
    :last_name => 'Longsen'
);
$gateway = new ActiveMerchantWorldPayGateway(array('login' => 'TestMerchant', 'password' => 'password'));
$response = $gateway->purchase($tendollar, $creditcard);
if($response->isSuccess()) {
    $m = $response->getMessage();
}
$trandId = response->params['transid'];
 */
class ActiveMerchantWorldPayGateway extends ActiveMerchantGateway
{
    //private $_merchantCode = 'WPACC11112222';
    private $_merchantCode = 'MMMODELMANAG';
    
    public $money;
    public $credit_card;
    public $customer;
    public $options = array();
    
    private $_url = 'https:{login}@{password}//{subdomain}.ims.worldpay.com/jsp/merchant/xml/paymentService.jsp';
    public $test_url = 'https://secure‐test.ims.worldpay.com/jsp/merchant/xml/paymentService.jsp';
    public $live_url = 'https://secure.ims.worldpay.com/jsp/merchant/xml/paymentService.jsp';
    
    private $_action = '';
    private $_login = '';
    private $_password = '';
    private $_xml = '';
    
    /**
     * Init instance vars
     *
     * @param array $options
     */
    public function __construct($options = array())
    {
        $this->_login = $options['login'];
        $this->_password = $options['password'];
        $this->options = $options;
    }
    /**
     * Issues an authorization
     *
     * @param ActiveMerchantMoney $money
     * @param ActiveMerchantCreditCard $creditCard
     * @param array $options
     * @return ActiveMerchantResponse
     */
    public function authorize(ActiveMerchantMoney $money, ActiveMerchantCreditCard $creditCard, $options = array())
    {
        $this->money = $money;
        $this->credit_card = $creditCard;
        $this->options = array_merge($this->_defaultOptions(), $options);
        $this->_xml = $this->getXmlOrder();
        return $this->commit('sale', array(
            'xml' => $this->_xml
        ));
    }
    /**
     * Parses given xml depending on the action passed (response, request)
     * and returns an array containing the xml's data
     *
     * @param string $action
     * @param string $xml
     * @return array
     */
    public function parse($action, $xml)
    {
        $ret = array();
        try {
            $x = new SimpleXMLElement($xml);
            if($action == 'response') {
                return $this->_response2array($x);
            } elseif($action == 'request') {
                return $this->_request2array($x);
            }
        } catch(Exception $e) {
            return $ret;
        }
    }
    private function _response2array($x)
    {
        $ret = array();
        if(isset($x->reply)) {
            $ret['status'] = 'SUCCESS';
            $ret['orderCode'] = (string)$x->reply->orderStatus['orderCode'];
            // initial response (step 3)
            if(isset($x->reply->orderStatus->requestInfo)) {
                $ret['paRequest'] = (string)$x->reply->orderStatus->requestInfo->request3DSecure->paRequest;
                $ret['issuerURL'] = (string)$x->reply->orderStatus->requestInfo->request3DSecure->issuerURL;
            }
            // initial response (step 3)
            if(isset($x->reply->orderStatus->echoData)) {
                $ret['echoData'] = (string)$x->reply->orderStatus->echoData;
            }
            // initial response (step 9)
            if(isset($x->reply->orderStatus->payment)) {
                $ret['status'] = (string)$x->reply->orderStatus->payment->lastEvent;
                $ret['paymentMethod'] = (string)$x->reply->orderStatus->payment->paymentMethod;
                $ret['amount'] = (string)$x->reply->orderStatus->payment->amount['value'];
                $ret['currency'] = (string)$x->reply->orderStatus->payment->amount['currencyCode'];
                $ret['tx_type'] = (string)$x->reply->orderStatus->payment->amount['debitCreditIndicator'];
                if(isset($x->reply->orderStatus->payment->balance)) {
                    $balances = array();
                    foreach($x->reply->orderStatus->payment->balance as $balance) {
                        $balances[] = array(
                            'type' => (string)$balance['accountType'], 
                            'amount' => (string)$balance->amount['value'], 
                            'currency' => (string)$balance->amount['currencyCode'], 
                            'tx_type' => (string)$balance->amount['debitCreditIndicator']
                        );
                    }
                    $ret['balance_type'] = $balances;
                }
                $ret['cardNumber'] = (string)$x->reply->orderStatus->payment->cardNumber;
            }
        } elseif(isset($x->lastEvent) && $x->lastEvent == 'REFUSED') {
            $ret['status'] = (string)$x->lastEvent;
            $ret['errorCode'] = (string)$x->ISO8583ReturnCode['code'];
            $ret['errorMsg'] = (string)$x->ISO8583ReturnCode['description'];
        }
        return $ret;
    }
    private function _request2array($x)
    {
        $ret = array();
        $ret['order'] = (string)$x->submit->order['orderCode'];
        $ret['installationId'] = (string)$x->submit->order['installationId'];
        $ret['description'] = (string)$x->submit->order->description;
        $ret['amount'] = (string)$x->submit->order->amount['value'];
        $ret['currency'] = (string)$x->submit->order->amount['currencyCode'];
        $ret['content'] = (string)$x->submit->order->orderContent;
        $ret['cardNumber'] = (string)$x->submit->order->paymentDetails->{'VISA-SSL'}->cardNumber;
        $ret['month'] = (string)$x->submit->order->paymentDetails->{'VISA-SSL'}->expiryDate->date['month'];
        $ret['year'] = (string)$x->submit->order->paymentDetails->{'VISA-SSL'}->expiryDate->date['year'];
        $ret['cardHolderName'] = (string)$x->submit->order->paymentDetails->{'VISA-SSL'}->cardHolderName;
        $ret['cvc'] = (string)$x->submit->order->paymentDetails->{'VISA-SSL'}->cvc;
        $ret['ip'] = (string)$x->submit->order->paymentDetails->session['shopperIPAddress'];
        $ret['session_id'] = (string)$x->submit->order->paymentDetails->session['id'];
        $ret['email'] = (string)$x->submit->order->shopper->shopperEmailAddress;
        return $ret;
    }
    /**
     * Connects to the gateway,
     * posts the petition,
     * parses the results,
     * constructs response object and returns it
     *
     * @param string $action
     * @param array $params
     * @return ActiveMerchantResponse
     */
    public function commit($action, $params = array())
    {
        require_once (AK_VENDOR_DIR . DS . 'pear' . DS . 'HTTP' . DS . 'Request.php');
        $this->_action = $action;
        
        // Send request and parse reponse
        $url = $this->serviceUrl($this->_login, $this->_password);
        $response = $this->_sendRequest($url, $params['xml']);
        unset($params['xml']);
        Ak::getLogger()->message(print_r($response, true));
        $response_params = $this->parse('response', $response);
        /*
        switch($response_params['status']) {
            case 'AUTHORISED':
                break;
            case 'REFUSED':
                break;
            case 'ERROR':
                break;
            default:
                break;
        }
        */
        $success = isset($response_params['status']) && $response_params['status'] == 'AUTHORISED';
        $message = '';
        $options = array(
            'test' => $this->isTest(), 
            'authorization' => isset($response_params['orderCode']) ? $response_params['orderCode'] : ''
        );
        $r = new ActiveMerchantResponse($success, $message, $response_params, $options);
        return $r; //return $r->params;
    }
    /**
     * If url defined, posts xml and returns xml response
     *
     * @param string $url
     * @param string $xml
     * @return string
     */
    private function _sendRequest($url = '', $xml = '')
    {
        $url = $this->serviceUrl($this->_login, $this->_password);
        if(empty($url)) {
            return $this->_testXmlResponse();
        }
        Ak::getLogger()->message($url);
        $request = & new HTTP_Request($url);
        $request->setBody($params['xml']);
        unset($params['xml']);
        $request->addHeader('Content-Tye', 'text/xml');
        return $request->sendRequest();
    }
    /**
     * Returns xml order
     *
     * @return string
     */
    public function getXmlOrder()
    {
        return $this->_xmlOrder();
    }
    /**
     * Layout to paymentService xml
     *
     * @return string
     */
    private function _xmlWpLayout()
    {
        return '<?xml version="1.0"?>
<!DOCTYPE paymentService PUBLIC "-//WorldPay/DTD WorldPay PaymentService v1//EN" "http://dtd.worldpay.com/paymentService_v1.dtd">
<paymentService version="1.4" merchantCode="' . $this->_merchantCode . '">
%s
</paymentService>';
    }
    /**
     * Main xml content for paymentService calls
     *
     * @return string
     */
    private function _xmlOrder()
    {
        $ret = '
    <submit>
        <order orderCode="' . $this->options['order'] . '" installationId="' . $this->options['account'] . '">
            <description>' . $this->options['description'] . '</description>
            <amount value="' . $this->money->getCents() . '" currencyCode="' . $this->money->currency . '" exponent="2" />
            <orderContent>' . $this->options['orderContent'] . '</orderContent>
            ' . $this->_xmlPaymentDetails() . '
            ' . $this->_xmlShopper() . '
            ' . $this->_xmlEchoData() . '
            ' . $this->_xmlAddress('shippingAddress', $this->customer) . '
        </order>
    </submit>';
        return sprintf($this->_xmlWpLayout(), $ret);
    }
    /**
     * Payment details info
     *
     * @return string
     */
    private function _xmlPaymentDetails()
    {
        $ret = '
            <paymentDetails>
                <VISA-SSL>
                    <cardNumber>' . $this->credit_card->number . '</cardNumber>
                    <expiryDate>
                        <date month="' . $this->credit_card->month . '" year="' . $this->credit_card->year . '" />
                    </expiryDate>
                    <cardHolderName>' . $this->credit_card->name . '</cardHolderName>';
        if(!empty($this->credit_card->verification_value)) {
            $ret .= '
                    <cvc>' . $this->credit_card->verification_value . '</cvc>';
        }
        $ret .= $this->_xmlAddress('cardAddress', $this->customer);
        $ret .= '
                </VISA-SSL>
                <session shopperIPAddress="' . $this->options['customer']['ip'] . '" id="' . $this->options['customer']['session_id'] . '" />';
        if($this->_action == '3DSecure2') {
            $ret .= '
                <info3DSecure>
                    <paResponse>somedata</paResponse>
                </info3DSecure>';
        }
        $ret .= '
            </paymentDetails>';
        return $ret;
    }
    /**
     * Shopper's details (email, browser)
     * 
     * @return string
     */
    private function _xmlShopper()
    {
        $ret = '
            <shopper>';
        if($this->_action == '3DSecure') {
            $http_accept = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : 'text/html';
            $http_ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Mozilla/5.0...';
            $ret .= '
                <browser>
                    <acceptHeader>' . $http_accept . '</acceptHeader>
                    <userAgentHeader>' . $http_ua . '</userAgentHeader>
                </browser>';
        }
        if(!empty($this->options['customer']['email'])) {
            $ret .= '
                <shopperEmailAddress>' . $this->options['customer']['email'] . '</shopperEmailAddress>';
        }
        $ret .= '
            </shopper>';
        return $ret;
    }
    /**
     * echoData content (for 3DSecure transactions)
     *
     * @return string
     */
    private function _xmlEchoData()
    {
        if(empty($this->echoData)) {
            return '';
        }
        return '
            <echoData>' . $this->echoData . '</echoData>';
    }
    /**
     * Address tag based on customer array
     *
     * @param array $customer
     * @return string
     */
    private function _xmlAddress($tag = '', $customer = array())
    {
        if(empty($tag) || empty($customer)) {
            return '';
        }
        return '<' . $tag . '>
                <address>
                    <firstName>' . $customer['first_name'] . '</firstName>
                    <lastName>' . $customer['last_name'] . '</lastName>
                    <street>' . $customer['address'] . '</street>
                    <postalCode>' . $customer['zip'] . '</postalCode>
                    <countryCode>' . $customer['countryCode'] . '</countryCode>
                    <telephoneNumber>' . $customer['phone'] . '</telephoneNumber>
                </address>
            <' . $tag . '>';
    }
    /**
     * Returns the full URL for worldpay tx posting
     *
     * @param string $login
     * @param string $password
     * @return string (false if not in production/test mode)
     */
    public function serviceUrl($login = '', $password = '')
    {
        $mode = ActiveMerchantBase::getIntegrationMode();
        if($mode === 'production') {
            return sprintf($this->_url, $login, $password, 'secure');
        } elseif($mode === 'test') {
            return sprintf($this->_url, $login, $password, 'secure-test');
        }
        return false;
        //throw new Exception('Integration mode set to an invalid value: ' . $mode);
    }
    /**
     * Sample successful xml reponse
     *
     * @return string
     */
    private function _testXmlResponse()
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
    /**
     * Test options data
     *
     * @return array
     */
    private function _defaultOptions()
    {
        return array(
            'order' => 1234, 
            'account' => 1234, 
            'description' => 'asdf', 
            'orderContent' => 'asdf', 
            'customer' => array(
                'ip' => '1.1.1.1', 
                'session_id' => '1234234', 
                'email' => 'asdf@asd.com', 
                'first_name' => $this->credit_card->first_name,  // 'asdf', 
                'last_name' => $this->credit_card->last_name,  // 'asdf', 
                'street' => 'asdf', 
                'address' => 'asdf', 
                'zip' => '1234', 
                'countryCode' => 'GB', 
                'phone' => '12341234'
            )
        );
    }
}