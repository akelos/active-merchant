<?php
ActiveMerchant::import('gateway', 'response');
/**
USAGE
-----
$amount = new ActiveMerchantMoney(1000, 'EUR');
$creditcard = new ActiveMerchantCreditCard (
    :number => '4111111111111111',
    :month => 8,
    :year => 2006,
    :name => 'Longbob Longsen',
);
$gateway = new ActiveMerchantWorldPayGateway(array('login' => 'TestMerchant', 'password' => 'password'));
$response = $gateway->purchase($amount, $creditcard);
if($response->isSuccess()) {
    $m = $response->getMessage();
}
$trandId = response->params['transid'];
 */
class ActiveMerchantWorldPayGateway extends ActiveMerchantGateway
{
    private $_merchantCode = 'MMMODELMANAG';
    private $_login = 'MMMODELMANAGM1';
    private $_password = 'Gt54rE32Ws8Uh7';
    private $_account = '233880';
    
    public $money;
    public $credit_card;
    public $customer;
    public $options = array();
    
    private $_url = 'https://{login}:{password}@{subdomain}.ims.worldpay.com/jsp/merchant/xml/paymentService.jsp';
    public $test_url = 'https://secure‐test.worldpay.com/jsp/merchant/xml/paymentService.jsp';
    public $live_url = 'https://secure.worldpay.com/jsp/merchant/xml/paymentService.jsp';
    
    private $_action = '';
    private $_xml = '';
    private $_mode = '';
    
    /**
     * Init instance vars
     *
     * @param array $options
     */
    public function __construct($options = array())
    {
        if(isset($options['login'])) {
            $this->_login = $options['login'];
        }
        if(isset($options['password'])) {
            $this->_password = $options['password'];
        }
        if(isset($options['account'])) {
            $this->_account = $options['account'];
        }
        $this->_mode = isset($options['mode']) ? $options['mode'] : 'test_local';
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
    public function purchase(ActiveMerchantMoney $money, ActiveMerchantCreditCard $creditCard, $options = array())
    {
        $this->money = $money;
        if(!$creditCard->isValid()) {
            return $this->_errorResponse($creditCard);
        }
        $this->credit_card = $creditCard;
        //$this->options = array_merge($this->_defaultOptions(), $options);
        $this->options = $options;
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
        }
        catch(Exception $e) {
            return $ret;
        }
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
        $this->_action = $action;
        
        // Send request and parse reponse
        $url = $this->serviceUrl($this->_login, $this->_password);
        $response = $this->_sendRequest($url, $params['xml']);
        unset($params['xml']);
        if($response) {
            $response_params = $this->parse('response', $response);
            $success = isset($response_params['status']) && $response_params['status'] == 'AUTHORISED';
            $message = isset($response_params['errorCode']) && isset($response_params['errorMsg']) ? $response_params['errorCode'] . ' - ' . $response_params['errorMsg'] : '';
            $options = array(
                'test' => $this->isTest(), 
                'authorization' => isset($response_params['orderCode']) ? $response_params['orderCode'] : ''
            );
        } else {
            $success = false;
            $message = '';
            $options = array(
                'test' => $this->isTest(), 
                'authorization' => ''
            );
            $response_params = array();
        }
        return new ActiveMerchantResponse($success, $message, $response_params, $options);
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
     * Returns the full URL for worldpay tx posting
     *
     * @param string $login
     * @param string $password
     * @return string (false if not in production/test mode)
     */
    public function serviceUrl($login = '', $password = '')
    {
        //does not fucking work! :(
        //$this->_mode = ActiveMerchantBase::getIntegrationMode();
        Ak::getLogger()->message(__METHOD__ . ' mode : "' . $this->_mode . '"');
        $s = array(
            '{login}', 
            '{password}', 
            '{subdomain}'
        );
        if($this->_mode === 'production') {
            $r = array(
                $login, 
                $password, 
                'secure'
            );
            return str_replace($s, $r, $this->_url);
        } elseif($this->_mode === 'test') {
            $r = array(
                $login, 
                $password, 
                'secure-test'
            );
            return str_replace($s, $r, $this->_url);
        }
        return false;
        //throw new Exception('Integration mode set to an invalid value: ' . $mode);
    }
    private function _response2array($x)
    {
        $ret = array();
        if(isset($x->reply)) {
            if(isset($x->reply->error)) {
                $ret['status'] = 'ERROR';
                $ret['errorCode'] = (string)$x->reply->error['code'];
                $ret['errorMsg'] = (string)$x->reply->error;
                return $ret;
            } elseif(isset($x->reply->orderStatus->error)) {
                $ret['status'] = 'ERROR';
                $ret['errorCode'] = (string)$x->reply->orderStatus->error['code'];
                $ret['errorMsg'] = (string)$x->reply->orderStatus->error;
                $ret['orderCode'] = (string)$x->reply->orderStatus['orderCode'];
                return $ret;
            }
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
            $ret['status'] = 'REFUSED';
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
     * If url defined, posts xml and returns xml response
     *
     * @param string $url
     * @param string $xml
     * @return string
     */
    private function _sendRequest($url = '', $xml = '')
    {
        if(empty($url)) {
            return $this->_testXmlResponse();
        }
        Ak::getLogger()->message('------------------------REQUEST------------------------');
        Ak::getLogger()->message($url);
        Ak::getLogger()->message($xml);
        Ak::getLogger()->message('-----------------------/REQUEST------------------------');
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($ch, CURLOPT_HTTPHEADER, Array(
            'Content-Type: text/xml'
        )); //'Content-Length : ' . strlen($xml)
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_NOPROGRESS, 0);
        $result = curl_exec($ch);
        if($result == false) {
            Ak::getLogger()->message('Curl error: ' . curl_error($ch));
        }
        Ak::getLogger()->message('------------------------RESPONSE------------------------');
        Ak::getLogger()->message($result);
        Ak::getLogger()->message('-----------------------/RESPONSE------------------------');
        curl_close($ch);
        return $result;
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
<paymentService version="1.4" merchantCode="' . $this->_login . '">
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
        <order orderCode="' . $this->options['order'] . '" installationId="' . $this->_account . '">
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
        $brandName = $this->_cardTypeBrandNameXref($this->credit_card->type);
        $ret = '
            <paymentDetails>
                <' . $brandName . '>
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
                </' . $brandName . '>
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
        //        if($this->_action == '3DSecure') {
        $http_accept = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : 'text/html';
        $http_ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Mozilla/5.0...';
        $ret .= '
                <browser>
                    <acceptHeader>' . $http_accept . '</acceptHeader>
                    <userAgentHeader>' . $http_ua . '</userAgentHeader>
                </browser>';
        //        }
        //        if(!empty($this->options['customer']['email'])) {
        //            $ret .= '
        //                <shopperEmailAddress>' . $this->options['customer']['email'] . '</shopperEmailAddress>';
        //        }
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
     * Sample successful xml reponse
     *
     * @return string
     */
    private function _testXmlResponse()
    {
        $f = 'testResponse';
        $ret = file_get_contents(dirname(__FILE__) . '/world_pay/' . $f . '.xml');
        $s = array(
            '{account}'
        );
        $r = array(
            $this->_login
        );
        return str_replace($s, $r, $ret);
    }
    /**
     * load xml file and replaces credencials info
     *
     * @return unknown
     */
    private function _testXmlRequest()
    {
        $f = 'wp_3d_02-init';
        //        $f = 'wp_01-order-full';
        //        $f = 'wp_01-order';
        //        $f = 'testOrder';
        $ret = file_get_contents(dirname(__FILE__) . '/world_pay/' . $f . '.xml');
        $s = array(
            '{login}', 
            '{account}'
        );
        $r = array(
            $this->_login, 
            $this->_account
        );
        return str_replace($s, $r, $ret);
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
                'first_name' => 'asdf', 
                'last_name' => 'asdf', 
                'address' => 'asdf', 
                'zip' => '1234', 
                'countryCode' => 'GB', 
                'phone' => '12341234'
            )
        );
    }
    /**
     * Returns a reponse object with the error messages gotten from the credit card object
     *
     * @param ActiveMerchantCreditCard $creditCard
     * @return ActiveMerchantResponse
     */
    private function _errorResponse(ActiveMerchantCreditCard $creditCard)
    {
        $success = false;
        $message = $creditCard->getErrors();
        $response_params = array();
        $options = array(
            'test' => $this->isTest(), 
            'authorization' => ''
        );
        return new ActiveMerchantResponse($success, $message, $response_params, $options);
    }
    private function _cardTypeBrandNameXref($type = null)
    {
        if(is_null($type)) {
            return false;
        }
        $arr = array(
            'american_express' => 'AMEX-SSL', 
            'dankort' => 'DANKORT-SSL', 
            'diners_club' => 'DINERS-SSL', 
            'discover' => 'DISCOVER-SSL', 
            'jcb' => 'JCB-SSL', 
            'laser' => 'LASER-SSL', 
            'maestro' => 'MAESTRO-SSL', 
            'master' => 'ECMC-SSL', 
            'solo' => 'SOLO_GB-SSL', 
            'switch' => 'MAESTRO-SSL', 
            'visa_delta' => 'VISA-SSL', 
            'visa electron' => 'VISA-SSL', 
            'visa' => 'VISA-SSL'
        );
        if(!isset($arr[$type])) {
            return false;
        }
        return $arr[$type];
        // NOT supported by WorldPay
    //'forbrugsforeningen' => '/^600722\d{10}$/',
    }
    private function _brandName()
    {
        return array(
            'AIRPLUS-SSL', 
            'AMEX-SSL', 
            'AURORE-SSL', 
            'CARTEBLEUE-SSL', 
            'CASH-DELIVERY', 
            'CB-SSL', 
            'CHEQUE-BANK', 
            'CHEQUE_GB-BANK', 
            'COMLINE-BANK', 
            'DANKORT-SSL', 
            'DB24-BANK', 
            'DINERS-SSL', 
            'DISCOVER-SSL', 
            'DRESDNER-BANK', 
            'EBETALNING-SSL', 
            'ECMC-SSL', 
            'ELBA-SSL', 
            'ELV-SSL', 
            'ENETS-SSL', 
            'GECAPITAL-SSL', 
            'HANSABANK-SSL', 
            'HOMEPAY-SSL', 
            'ICCHEQUE-SSL', 
            'IDEAL-SSL', 
            'INCASSO_DE-FAX', 
            'INCASSO_NL-FAX', 
            'JCB-SSL', 
            'KBC-BANK', 
            'LASER-SSL', 
            'NETPAY-SSL', 
            'ONLINE_TRANSFER_DE-SSL', 
            'PAYBOX-SSL', 
            'PAYNOVA-SSL', 
            'PAYOUT-BANK', 
            'PERMANENT_SIGNED_DD_NL-FAX', 
            'POP-SSL', 
            'SINGLE_SIGNED_DD_ES-SSL', 
            'SINGLE_UNSIGNED_DD_ES-SSL', 
            'SINGLE_UNSIGNED_DD_FR-SSL', 
            'SINGLE_UNSIGNED_DD_NL-SSL', 
            'SOLO-SSL', 
            'SOLO_GB-SSL', 
            'SWITCH-SSL', 
            'TRANSFER_AT-BANK', 
            'TRANSFER_BE-BANK', 
            'TRANSFER_CH-BANK', 
            'TRANSFER_DE-BANK', 
            'TRANSFER_DK-BANK', 
            'TRANSFER_ES-BANK', 
            'TRANSFER_FI-BANK', 
            'TRANSFER_FR-BANK', 
            'TRANSFER_GB-BANK', 
            'TRANSFER_GR-BANK', 
            'TRANSFER_IT-BANK', 
            'TRANSFER_JP-BANK', 
            'TRANSFER_LU-BANK', 
            'TRANSFER_NL-BANK', 
            'TRANSFER_NO-BANK', 
            'TRANSFER_SE-BANK', 
            'UATP-SSL', 
            'UHISBANK-SSL', 
            'VISA-SSL'
        );
    }
}