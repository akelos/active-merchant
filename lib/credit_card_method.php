<?php
/**
 * Convenience methods that can be included into a custom Credit Card object, 
 * such as an ActiveRecord based Credit Card object
 */
class ActiveMerchantCreditCardMethod
{
    public static $card_companies = array(
        'visa' => '/^4\d{12}(\d{3})?$/', 
        'master' => '/^(5[1-5]\d{4}|677189)\d{10}$/', 
        'discover' => '/^(6011|65\d{2})\d{12}$/', 
        'american_express' => '/^3[47]\d{13}$/', 
        'diners_club' => '/^3(0[0-5]|[68]\d)\d{11}$/', 
        'jcb' => '/^3528\d{12}$/', 
        'switch' => '/^6759\d{12}(\d{2,3})?$/', 
        'solo' => '/^6767\d{12}(\d{2,3})?$/', 
        'dankort' => '/^5019\d{12}$/', 
        'maestro' => '/^(5[06-8]|6\d)\d{10,17}$/', 
        'forbrugsforeningen' => '/^600722\d{10}$/', 
        'laser' => '/^(6304[89]\d{11}(\d{2,3})?|670695\d{13})$/'
    );
    /**
     * Validates a month
     *
     * @param int $month
     * @return boolean
     */
    public static function isValidMonth($month)
    {
        return in_array($month, range(1, 12));
    }
    /**
     * Validates expiry year (from now to 20 years from now)
     *
     * @param int $year
     * @return boolean
     */
    public static function isValidExpiryYear($year)
    {
        return in_array($year, range(date('Y'), date('Y') + 20));
    }
    /**
     * Validates the start year (format + greater than 1987)
     *
     * @param int $year
     * @return boolean
     */
    public static function isValidStartYear($year)
    {
        return strlen($year) == 4 && $year > 1987;
    }
    /**
     * Validates the issue number
     *
     * @param int $number
     * @return boolean
     */
    public static function isValidIssueNumber($number)
    {
        return is_int((int)$number) && (int)$number >= 0 && in_array(strlen($number), array(1, 2));
    }
    /**
     * Validates the test card mode number
     *
     * @param int $number
     * @return boolean
     */
    public static function isValidTestModeCardNumber($number)
    {
        ActiveMerchant::import('base');
        return ActiveMerchantBase::isTest()
            && in_array((string)$number, array('1', '2', '3', 'success', 'failure', 'error'));
    }
    /**
     * Validates cc number
     *
     * @param string $number
     * @return boolean
     */
    public static function isValidNumber($number)
    {
        return self::isValidTestModeCardNumber($number)
            || self::isValidCardNumberLength($number)
            && self::isValidChecksum($number);
    }
    /**
     * Validates cc number's length
     *
     * @param string $number
     * @return boolean
     */
    public static function isValidCardNumberLength($number)
    {
        return strlen($number) >= 12;
    }
    /**
     * Checks the validity of a card number by use of the the Luhn Algorithm
     * Please see http://en.wikipedia.org/wiki/Luhn_algorithm for details
     *
     * @param string $number
     * @return boolean
     */
    public static function isValidChecksum($number)
    {
        $number_length = strlen($number);
        $parity = $number_length % 2;
        $total = 0;
        for($i = 0;$i < $number_length;$i++) {
            $digit = $number[$i];
            if($i % 2 == $parity) {
                $digit *= 2;
                if($digit > 9) {
                    $digit -= 9;
                }
            }
            $total += $digit;
        }
        return ($total % 10 == 0);
    }
    /**
     * Regular expressions for the known card companies
     * References: 
     * - http://en.wikipedia.org/wiki/Credit_card_number 
     * - http://www.barclaycardbusiness.co.uk/information_zone/processing/bin_rules.html
     * 
     * @return array
     */
    public static function cardCompanies()
    {
        return self::$card_companies;
    }
    /**
     * Returns a string containing the type of card from the list of known information below.
     * Need to check the cards in a particular order, as there is some overlap of the allowable ranges
     *
     * @param string $number
     * @return string
     */
    public static function isType($number)
    {
        if(self::isValidTestModeCardNumber($number)) {
            return 'bogus';
        }
        $arr = self::cardCompanies();
        foreach($arr as $c => $p) {
            if($c == 'maestro') {
                continue;
            }
            if(preg_match($p, $number)) {
                return $c;
            }
        }
        if(preg_match($arr['maestro'], $number)) {
            return 'maestro';
        }
        return null;
    }
    /**
     * Checks to see if the calculated type matches the specified type
     *
     * @param string $number
     * @param string $type
     * @return boolean
     */
    public static function isMatchingType($number, $type)
    {
        return self::isType($number) === $type;
    }
    /**
     * Masks the first 9 numbers of the given cc number
     *
     * @param string $number
     * @return string
     */
    public static function mask($number)
    {
        return 'XXXX-XXXX-XXXX-' . self::lastDigits($number);
    }
    /**
     * Returns the last 4 digits of the given cc number
     *
     * @param string $number
     * @return string
     */
    public static function lastDigits($number)
    {
        if(strlen($number) <= 4) {
            return $number;
        }
        return substr($number, -4);
    }
}
