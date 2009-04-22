<?php
class ActiveMerchantCountryCode
{
    public $value;
    public $format;
    
    /**
     * Getters
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
    public function getFormat()
    {
        return $this->format;
    }
    /**
     * Init class var
     *
     * @param string $value
     */
    public function __construct($value = null)
    {
        if(!is_null($value)) {
            $this->init($value);
        }
    }
    public function init($value)
    {
        $this->value = strtoupper($value);
        $this->_detectFormat();
    }
    /**
     * Returns the self::value var
     *
     * @return string
     */
    public function __toString()
    {
        return $this->value;
    }
    /**
     * Detects the format of the current code
     * @throws CountryCodeFormatException
     */
    private function _detectFormat()
    {
        if(preg_match('/^[[:alpha:]]{2}$/', $this->value)) {
            $this->format = 'alpha2';
        } elseif(preg_match('/^[[:alpha:]]{3}$/', $this->value)) {
            $this->format = 'alpha3';
        } elseif(preg_match('/^[[:digit:]]{3}$/', $this->value)) {
            $this->format = 'numeric';
        } else {
            throw new ActiveMerchantCountryCodeFormatException('The country code is not formatted correctly ' . $this->value);
        }
    }
}
class ActiveMerchantCountryCodeFormatException extends Exception {}
