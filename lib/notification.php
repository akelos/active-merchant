<?php

ActiveMerchant::import('money');
class ActiveMerchantNotification
{
    public $params;
    public $raw;
    public $options;
    // set in the subclass, to specify which IPs are allowed to send requests
    public $production_ips = array();
    
    public function __construct($post, $options = array())
    {
        $this->init($post, $options = array());
    }
    public function init($post, $options = array())
    {
        $this->options = $options;
        $this->params = array();
        $this->raw = '';
        $this->parse($post);
    }
    public function parse($post)
    {
        $this->raw = (string)$post;
        // @WARNING : "." dots are converted to underscores "_" with parse_str!!
        parse_str($this->raw, $this->params);
    }
    /**
     * ...
     * @throws NotImplementedException
     */
    public function getStatus()
    {
        throw new ActiveMerchantNotImplementedException('Must implement this method in the subclass');
    }
    /**
     * The money amount we received in X.2 decimal.
     * @throws NotImplementedException
     */
    public function getGross()
    {
        throw new ActiveMerchantNotImplementedException('Must implement this method in the subclass');
    }
    public function getGrossCents()
    {
        return round((float)$this->getGross() * 100);
    }
    /**
     * This combines the gross and currency and returns a proper Money object
     * this requires the money library located at http://dist.leetsoft.com/api/money
     *
     * @return Money
     */
    public function getAmount()
    {
        try {
            return new ActiveMerchantMoney($this->getGrossCents(), $this->getCurrency());
        } catch(Exception $e) {
            // maybe you have an own money object which doesn't take a currency?
            return new ActiveMerchantMoney($this->getGrossCents());
        }
    }
    /**
     * Check if the request comes from an official IP
     *
     * @param string $ip
     * @return boolean
     */
    public function isValidSender($ip)
    {
        if(ActiveMerchantBase::getIntegrationMode() === 'test' || empty($this->production_ips)) {
            return true;
        }
        return in_array($ip, $this->production_ips);
    }
}
class ActiveMerchantNotImplementedException extends Exception
{}
