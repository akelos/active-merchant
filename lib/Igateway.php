<?php
interface IActiveMerchantGateway
{
    /**
     public $options = array(
        'order_id', // The order number
        'ip', // The IP address of the customer making the purchase
        'customer' => array( // A hash containing information that identifies the customer
            'name',
            'customer_number'
            ...
        ),
        'invoice', // The invoice number
        'merchant', // The name or description of the merchant offering the product
        'description', // A description of the transaction
        'email', // The email address of the customer
        'currency', // The currency of the transaction.  Only important when you are using a currency that is not the default with a gateway that supports multiple currencies.
        'billing_address' => array( // A hash containing the billing address of the customer.
            'name', // The full name of the customer.
            'company', // The company name of the customer.
            'address1', // The primary street address of the customer.
            'address2', // Additional line of address information.
            'city', // The city of the customer.
            'state', // The state of the customer.  The 2 digit code for US and Canadian addresses. The full name of the state or province for foreign addresses.
            'country', // The [ISO 3166-1-alpha-2 code](http://www.iso.org/iso/country_codes/iso_3166_code_lists/english_country_names_and_code_elements.htm) for the customer.
            'zip', // The zip or postal code of the customer.
            'phone', // The phone number of the customer.
        ),
        'shipping_address' => array( // A hash containing the shipping address of the customer.
            'name', // The full name of the customer.
            'company', // The company name of the customer.
            'address1', // The primary street address of the customer.
            'address2', // Additional line of address information.
            'city', // The city of the customer.
            'state', // The state of the customer.  The 2 digit code for US and Canadian addresses. The full name of the state or province for foreign addresses.
            'country', // The [ISO 3166-1-alpha-2 code](http://www.iso.org/iso/country_codes/iso_3166_code_lists/english_country_names_and_code_elements.htm) for the customer.
            'zip', // The zip or postal code of the customer.
            'phone', // The phone number of the customer.
        )
    );
    */
    public function authorize($money, $creditcard, $options = array());
    /*
     * $this->post = array()
     * $this->_addInvoice($post, $options)
     * $this->_addCreditcard($post, $creditcard)
     * $this->_addAddress($post, $creditcard, $options)
     * $this->_addCustomerData($post, $options)
     * $this->_commit('authonly', $money, $post)
     */
    public function purchase($money, $creditcard, $options = array());
    /*
     * $this->post = array()
     * $this->_addInvoice($post, $options)
     * $this->_addCreditcard($post, $creditcard)
     * $this->_addAddress($post, $creditcard, $options)
     * $this->_addCustomerData($post, $options)
     * $this->_commit('sale', $money, $post)
     */
    public function capture($money, $authorization, $options = array());
    /*
     * $this->_commit('capture', $money, $post)
     */
    public function void($identification, $options = array());
    public function credit($money, $identification, $options = array());
    
    public function recurring($money, $identification, $options = array());
    
    public function store($creditcard, $options = array());
    public function unstore($identification, $options = array());
    
    private function _addCustomerData($post, $options);
    private function _addAddress($post, $creditcard, $options);
    private function _addInvoice($post, $options);
    private function _addCreditcard($post, $creditcard);
    private function _commit($action, $money, $post);
    private function _parse($body);
    private function _messageFrom($response);
    private function _postData($action, $parameters = array());
}