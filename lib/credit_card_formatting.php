<?php
class ActiveMerchantCreditCardFormatting
{
    /**
     * This method is used to format numerical information pertaining to credit cards
     * CreditCardFormatting::format(2005, :two_digits)  # => "05"
     * CreditCardFormatting::format(05,   :four_digits) # => "0005"
     *
     * @param string $number
     * @param array $option
     * @return string
     */
    public static function format($number = null, Array $option = array())
    {
        if(!isset($number) || empty($number)) {
            return '';
        }
        if(empty($option)) {
            return $number;
        }
        if(isset($option[0]) && ($option[0] === 'two_digits' || $option[0] === 'four_digits')) {
            $nb_digits = $option[0] === 'two_digits' ? 2 : 4;
            return str_pad(strlen($number)>$nb_digits ? substr($number, -$nb_digits) : $number, $nb_digits, '0', STR_PAD_LEFT);
        }
        return $number;
    }
}
