<?php
/**
 * == Description
 * The Gateway class is the base class for all ActiveMerchant gateway implementations. 
 * 
 * The standard list of gateway functions that most concrete gateway subclasses implement is:
 * 
 * <tt>purchase(money, creditcard, options = {})</tt>
 * <tt>authorize(money, creditcard, options = {})</tt>
 * <tt>capture(money, authorization, options = {})</tt>
 * <tt>void(identification, options = {})</tt>
 * <tt>credit(money, identification, options = {})</tt>
 *
 * Some gateways include features for recurring billing
 *
 * <tt>recurring(money, creditcard, options = {})</tt>
 *
 * Some gateways also support features for storing credit cards:
 *
 * <tt>store(creditcard, options = {})</tt>
 * <tt>unstore(identification, options = {})</tt>
 * 
 * === Gateway Options
 * The options hash consists of the following options:
 *
 * <tt>:order_id</tt> - The order number
 * <tt>:ip</tt> - The IP address of the customer making the purchase
 * <tt>:customer</tt> - The name, customer number, or other information that identifies the customer
 * <tt>:invoice</tt> - The invoice number
 * <tt>:merchant</tt> - The name or description of the merchant offering the product
 * <tt>:description</tt> - A description of the transaction
 * <tt>:email</tt> - The email address of the customer
 * <tt>:currency</tt> - The currency of the transaction.  Only important when you are using a currency that is not the default with a gateway that supports multiple currencies.
 * <tt>:billing_address</tt> - A hash containing the billing address of the customer.
 * <tt>:shipping_address</tt> - A hash containing the shipping address of the customer.
 * 
 * The <tt>:billing_address</tt>, and <tt>:shipping_address</tt> hashes can have the following keys:
 * 
 * <tt>:name</tt> - The full name of the customer.
 * <tt>:company</tt> - The company name of the customer.
 * <tt>:address1</tt> - The primary street address of the customer.
 * <tt>:address2</tt> - Additional line of address information.
 * <tt>:city</tt> - The city of the customer.
 * <tt>:state</tt> - The state of the customer.  The 2 digit code for US and Canadian addresses. The full name of the state or province for foreign addresses.
 * <tt>:country</tt> - The [ISO 3166-1-alpha-2 code](http://www.iso.org/iso/country_codes/iso_3166_code_lists/english_country_names_and_code_elements.htm) for the customer.
 * <tt>:zip</tt> - The zip or postal code of the customer.
 * <tt>:phone</tt> - The phone number of the customer.
 *
 * == Implmenting new gateways
 *
 * See the {ActiveMerchant Guide to Contributing}[http://code.google.com/p/activemerchant/wiki/Contributing]
 */
ActiveMerchant::import('base');
class ActiveMerchantGateway
{
    # The format of the amounts used by the gateway
    # :dollars => '12.50'
    # :cents => '1250'
    public $money_format = 'dollars';
    
    # The default currency for the transactions if no currency is provided
    public $default_currency;
    
    # The countries of merchants the gateway supports
    public $supported_countries = array();
    
    public $supported_cardtypes = array();
    
    public $homepage_url;
    public $display_name;
    protected $application_id = 'ActiveMerchant';
    
    public $ssl_strict = true;
    
    public static $debit_cards = array(
        'switch', 
        'solo'
    );
    /**
     * Checks if the given card type is supported
     *
     * @param string $type
     * @return boolean
     */
    public function isSupported($type)
    {
        return in_array($type, $this->supported_cardtypes);
    }
    /**
     * Checks if we are in test mode
     *
     * @return boolean
     */
    public function isTest()
    {
        return ActiveMerchantBase::getGatewayMode() === 'test';
    }
    public function getApplicationId()
    {
        return $this->application_id;
    }
    /**
     * Return a String with the amount in the appropriate format
     *
     * @param mixed $money -- The amount to be authorized. Either an Integer value in cents or a Money object.
     * @return string
     * @throws Exception
     */
    public function getAmount($money = null)
    {
        if(is_null($money)) {
            return;
        }
        if(is_string($money) || (int)$money <= 0) {
            throw new Exception('money amount must be either a Money object or a positive integer in cents.');
        }
        if($this->money_format == 'cents') {
            return $money;
        } else {
            return sprintf('%.2f', $money / 100);
        }
    }
    /**
     * Ascertains the currency to be used on the money supplied.
     *
     * @param mixed $money -- ActiveMerchantMoney object or Array
     * @return string
     */
    public function getCurrency($money)
    {
        if(isset($money['currency'])) {
            return $money['currency'];
        }
        if($money instanceof ActiveMerchantMoney && isset($money->currency)) {
            return $money->currency;
        }
        return $this->default_currency;
    }
    /**
     * Returns the card brand of the given source
     *
     * @param array $source
     * @return string
     */
    public function getCardBrand($source = array())
    {
        if(empty($source)) {
            return;
        }
        if(isset($source['brand'])) {
            return strtolower($source['brand']);
        }
        if(isset($source['type'])) {
            return strtolower($source['type']);
        }
        return;
    }
    /**
     * Checks if start date or issue number is required
     *
     * @param array ?? $credit_card
     * @return boolean
     */
    public function isStartDateOrIssueNumberRequired($credit_card)
    {
        if(empty($credit_card)) {
            return false;
        }
        $ccbrand = $this->getCardBrand($credit_card);
        return in_array($ccbrand, self::$debit_cards);
    }
}