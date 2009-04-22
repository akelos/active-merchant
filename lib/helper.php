<?php
ActiveMerchant::import('country');
class ActiveMerchantHelper
{
    public $mappings = array();
    public $fields = array();
    public $service_url;
    public $country_format = 'alpha2';
    public $application_id = 'ActiveMerchant';
    
    //public $order;
    //public $account;
    //public $amount;
    //public $currency;
    /*
        parent::mapping('account', '');
        parent::mapping('amount', '');

        parent::mapping('order', '');

        parent::mapping('customer', array(
            'first_name' => '', 
            'last_name' => ''
            'email' => '', 
            'phone' => ''
        ));

        parent::mapping('billing_address', array(
            'city' => '',
            'address1' => '', 
            'address2' => ''
            'state' => ''
            'zip' => ''
            'country' => ''
        ));

        parent::mapping('notify_url', '');
        parent::mapping('return_url', '');
        parent::mapping('cancel_return_url', '');
        parent::mapping('description', '');
        parent::mapping('tax', '');
        parent::mapping('shipping', '');
*/
    /**
     * Init class vars
     *
     * @param mixed $order
     * @param mixed $account
     * @param array $options
     */
    public function __construct($order_id, $account_id, $options = array())
    {
        $this->init($order_id, $account_id, $options);
    }
    public function init($order, $account, $options = array())
    {
        $arr = array(
            'amount', 
            'currency', 
            'test'
        );
        $this->assertValidKeys($options, $arr);
        $this->order = $order;
        $this->account = $account;
        foreach($options as $key => $value) {
            if($key[0] != '_') {
                $this->$key = $value;
            }
        }
    }
    
    public function assertValidKeys(Array $options = array(), Array $keys = array())
    {
        $arr = array_diff(array_keys($options), $keys);
        return !empty($arr);
    }
    /**
     * Add a new array of options to self::mappings
     *
     * @param mixed $attributes
     * @param mixed $options
     */
    public function mapping($attributes, $options = array())
    {
        $this->mappings[$attributes] = $options;
    }
    /**
     * Adds a new key/value to self::fields
     *
     * @param mixed $name
     * @param mixed $value
     */
    public function addField($name = null, $value = null)
    {
        if(is_null($name)) {
            return;
        }
        $this->fields[(string)$name] = (string)$value;
    }
    /**
     * Adds an associative array of data to self::fields
     *
     * @param mixed $subkey
     * @param array $params
     */
    public function addFields($subkey = null, Array $params = array())
    {
        foreach($params as $k => $v) {
            $field = $this->mappings[$subkey][$k];
            if(!empty($field)) {
                $this->addField($field, $v);
            }
        }
    }
    /**
     * Adds the billing address to to the address class var
     *
     * @param array $param
     */
    public function setBillingAddress(Array $param = array())
    {
        $this->_addAddress('billing_address', $param);
    }
    /**
     * Adds the shipping address to to the address class var
     *
     * @param array $param
     */
    public function setShippingAddress(Array $param = array())
    {
        $this->_addAddress('shipping_address', $param);
    }
    public function setCustomer($params = array())
    {}
    /**
     * ???
     * @return unknown
     */
    public function getFormField($key = null)
    {
        if(!is_null($key) && isset($this->fields[$key])) {
            return $this->fields[$key];
        }
        //return $this->fields;
    }
    /**
     * Adds a address to the self::fields class var
     *
     * @param mixed $key
     * @param mixed $params
     */
    private function _addAddress($key, $params)
    {
        if(is_null($this->mappings[$key])) {
            return;
        }
        $code = $this->_lookupCountryCode($params['country']);
        unset($params['country']);
        $this->addField($this->mappings[$key]['country'], $code);
        $this->addFields($key, $params);
        //$this->$key = $params;
    }
    /**
     * Validates the given ISO 2 country code
     *
     * @param string $name_or_code
     */
    protected function _lookupCountryCode($name_or_code)
    {
        try {
            $tmp = new ActiveMerchantCountry();
            $country = $tmp->find($name_or_code);
            $countryCode = $country->code($this->country_format);
            return $countryCode;
        } catch(InvalidCountryCodeException $e) {
            return $name_or_code;
        }
    }
    
    public function __set($name, $value)
    {
        if(!is_array($value)) {
            if(isset($this->mappings[$name])) {
                $this->fields[$this->mappings[$name]] = $value;
            }
            $this->$name = $value;
            return;
        }
        foreach($value as $k => $v) {
            if(isset($this->mappings[$name])) { // && isset($this->mappings[$name][$k])) {
                $this->fields[$name][$k] = $v;
                //$this->fields[] = $value;
            }
            $this->$k = $v;
        }
        // make sure we call the special ones!
        if($name == 'billing_address') {
            $this->setBillingAddress($value);
        }
        if($name == 'shipping_address') {
            $this->setShippingAddress($value);
        }
        if($name == 'customer') {
            $this->setCustomer($value);
        }
    }
    
    public function __get($name)
    {
        if(isset($this->$name)) {
            return $this->$name;
        }
        // if in 1st level mapping
        if(in_array($name, $this->mappings)) {
            return $this->fields[$name];
        }
        // if in 2nd level mapping
        foreach($this->mappings as $k => $v) {
            if(is_array($v)) {
                if(in_array($name, $v)) {
                    return $this->fields[$k][array_search($name, $v)];
                }
            }
        }
    }
}
