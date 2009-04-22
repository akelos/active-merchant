<?php
class ActiveMerchantExpiryDate
{
    public $month, $year;
    public function __construct($month, $year)
    {
        $this->init($month, $year);
    }
    public function init($month, $year)
    {
        $this->month = $month;
        $this->year = $year;
    }
    public function isExpired()
    {
        return mktime() > $this->expiration();
    }
    public function expiration()
    {
        $lastDay = date('t', mktime(0, 0, 0, $this->month, 1, $this->year));
        return mktime(0, 0, 0, $this->month, $lastDay, $this->year);
    }
}
