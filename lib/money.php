<?php
class ActiveMerchantMoney /*extends ActiveRecord*/
{
    public $default_currency = 'USD';
    public $cents;
    public $currency;
    
    public function __construct($cents, $currency = null)
    {
        $this->init($cents, $currency);
    }
    public function init($cents, $currency = null)
    {
        $this->cents = $cents;
        $this->currency = is_null($currency) ? $this->default_currency : $currency;
    }
    /**
     * Checks if given money object is equal the current instance
     *
     * @param ActiveMerchantMoney $other_money
     * @return boolean
     */
    public function isEqual(ActiveMerchantMoney $other_money)
    {
        return ($this->cents == $other_money->cents) && ($this->currency == $other_money->currency);
    }
    /**
     * Get the cents value of the object
     *
     * @return int
     */
    public function getCents()
    {
        return (int)$this->cents;
    }
    /**
     * Test if the money amount is zero
     * 
     * @return boolean
     */
    public function isZero()
    {
        return $this->cents == 0;
    }
    /**
     * Format the price according to several rules
     * Currently supported are :with_currency, :no_cents and :html
     * 
     * with_currency: 
     *  Money.ca_dollar(0).format => "free"
     *  Money.ca_dollar(100).format => "$1.00"
     *  Money.ca_dollar(100).format(:with_currency) => "$1.00 CAD"
     *  Money.us_dollar(85).format(:with_currency) => "$0.85 USD"
     * 
     * no_cents:  
     *  Money.ca_dollar(100).format(:no_cents) => "$1"
     *  Money.ca_dollar(599).format(:no_cents) => "$5"
     *  
     *  Money.ca_dollar(570).format(:no_cents, :with_currency) => "$5 CAD"
     *  Money.ca_dollar(39000).format(:no_cents) => "$390"
     * 
     * html:
     *  Money.ca_dollar(570).format(:html, :with_currency) =>  "$5.70 <span class=\"currency\">CAD</span>"
     *
     * @param array $rules
     * @return string
     */
    public function format($rules)
    {
        if($this->cents == 0) {
            return 'free';
        }
        if(in_array('no_cents', $rules)) {
            $formatted = sprintf('$%d', $this->cents / 100);
        } else {
            $formatted = sprintf('$%.2f', $this->cents / 100);
        }
        if(in_array('with_currency', $rules)) {
            if(in_array('html', $rules)) {
                $formatted .= ' <span class="currency">' . $this->currency . '</span>';
            } else {
                $formatted .= ' ' . $this->currency;
            }
        }
        return $formatted;
    }
    public function __toString()
    {
        return sprintf('%.2f', $this->cents / 100);
    }

}
class ActiveMerchantMoneyException extends Exception
{}