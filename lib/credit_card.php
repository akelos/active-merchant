<?php

ActiveMerchant::import('credit_card_method');

class ActiveMerchantCreditCard extends ActiveRecord
{
    public static $requireVerificationValue = true;
    public $number, $month, $year, $type, $first_name, $last_name, $name;
    public $start_month, $start_year, $issue_number;
    public $verification_value;
    
    public function __construct($options = null)
    {
        if(is_null($options)) {
            return;
        }
        $this->setAttributes($options);
    }
    public static function getRequireVerificationValue()
    {
        return self::$requireVerificationValue;
    }
    public static function setRequireVerificationValue($value)
    {
        self::$requireVerificationValue = $value;
    }
    public static function isRequireVerificationValue()
    {
        return self::getRequireVerificationValue() === true;
    }
    /**
     * sets the attributes of the class
     *
     * @param array $attributes
     */
    public function setAttributes(Array $attributes = array())
    {
        $attrs = get_class_vars(get_class($this));
        foreach(array_keys($attrs) as $attr) {
            if(isset($attributes[$attr])) {
                $this->$attr = $attributes[$attr];
            }
        }
        $this->name = $this->first_name . ' ' . $this->last_name;
    }
    /**
     * Show the card number, with all but last 4 numbers replace with "X".
     * (XXXX-XXXX-XXXX-4338)
     * @return string
     */
    public function displayNumber()
    {
        return ActiveMerchantCreditCardMethod::mask($this->number);
    }
    /**
     * Returns the cc type for the given cc number
     *
     * @param string $number
     * @return string
     */
    public static function isType($number)
    {
        return ActiveMerchantCreditCardMethod::isType($number);
    }
    /**
     * Returns the last digits of the cc number
     *
     * @return string
     */
    public function lastDigits()
    {
        return ActiveMerchantCreditCardMethod::lastDigits($this->number);
    }
    public function isFirstName()
    {
        return strlen($this->first_name) > 0;
    }
    public function isLastName()
    {
        return strlen($this->last_name) > 0;
    }
    public function isName()
    {
        return strlen($this->name) > 0;
    }
    public function isVerificationValue()
    {
        return !empty($this->verification_value);
        //return strlen($this->verification_value) > 0;
    }
    public function beforeValidate()
    {
        $this->month = (int)$this->month;
        $this->year = (int)$this->year;
        $this->number = preg_replace('/[^\d]/', '', $this->number);
        if(isset($this->type)) {
            $this->type = strtolower($this->type);
        }
        if(empty($this->type)) {
            $this->type = ActiveMerchantCreditCardMethod::isType($this->number);
        }
        return true;
    }
    public function validate()
    {
        $this->validateEssentialAttributes();
        if($this->type === 'bogus') {
            return;
        }
        $this->validateCardType();
        $this->validateCardNumber();
        $this->validateVerificationValue();
        $this->validateSwitchOrSoloAttributes();
    }
    /**
     * Validates first_name, last_name, month and year
     */
    public function validateEssentialAttributes()
    {
        $this->addErrorOnBlank('first_name', 'cannot be empty' . $this->first_name);
        $this->addErrorOnBlank('last_name', 'cannot be empty');
        if(!ActiveMerchantCreditCardMethod::isValidMonth($this->month)) {
            $this->addError('month', 'is not a valid month');
        }
        if($this->isExpired()) {
            $this->addError('year', 'expired');
        }
        if(!ActiveMerchantCreditCardMethod::isValidExpiryYear($this->year)) {
            $this->addError('year', 'is not a valid year');
        }
    }
    /**
     * Validates the cc type
     */
    public function validateCardType()
    {
        if(empty($this->type)) {
            $this->addError('type', 'is required');
        }
        if(!in_array($this->type, array_keys(ActiveMerchantCreditCardMethod::cardCompanies()))) {
            $this->addError('type', 'is invalid');
        }
    }
    /**
     * Validates the cc number
     */
    public function validateCardNumber()
    {
        if(!ActiveMerchantCreditCardMethod::isValidNumber($this->number)) {
            $this->addError('number', 'is not a valid credit card number');
        }
        if(!ActiveMerchantCreditCardMethod::isMatchingType($this->number, $this->type)) {
            $this->addError('type', 'is not the correct card type');
        }
    }
    public function validateVerificationValue()
    {
        if(self::isRequireVerificationValue()) {
            if(!self::isVerificationValue()) {
                $this->addError('verification_value', 'is required');
            }
        }
    }
    public function validateSwitchOrSoloAttributes()
    {
        if(!in_array($this->type, array(
            'switch', 
            'solo'
        ))) {
            return;
        }
        if((!ActiveMerchantCreditCardMethod::isValidMonth($this->start_month) && !ActiveMerchantCreditCardMethod::isValidStartYear($this->start_year)) || !ActiveMerchantCreditCardMethod::isValidIssueNumber($this->issue_number)) {
            return;
        }
        if(!ActiveMerchantCreditCardMethod::isValidMonth($this->start_month)) {
            $this->addError('start_month', 'is invalid');
        }
        if(!ActiveMerchantCreditCardMethod::isValidStartYear($this->start_year)) {
            $this->addError('start_year', 'is invalid');
        }
        if(!ActiveMerchantCreditCardMethod::isValidIssueNumber($this->issue_number)) {
            $this->addError('issue_number', 'cannot be empty');
        }
    }
    /**
     * Checks if current expiry date is expired
     *
     * @return boolean
     */
    public function isExpired()
    {
        ActiveMerchant::import('expiry_date');
        $d = new ActiveMerchantExpiryDate($this->month, $this->year);
        return $d->isExpired();
    }
    /**
     * Checks if current activerecord has errors
     *
     * @return boolean
     */
    public function isValid()
    {
        $this->beforeValidate();
        $this->validate();
        return !$this->hasErrors();
    }
}
