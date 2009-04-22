<?php
class ActiveMerchantBase
{
    public static $gatewayMode;
    public static $integrationMode;
    public static $mode = 'production';
    /**
     * Set ActiveMerchant gateways in test mode
     *
     * @param mixed $mode
     */
    public static function setGatewayMode($mode)
    {
        self::$gatewayMode = $mode;
    }
    public static function getGatewayMode()
    {
        return self::$gatewayMode;
    }
    /**
     * Set ActiveMerchant integrations in test mode
     *
     * @param mixed $mode
     */
    public static function setIntegrationMode($mode)
    {
        self::$integrationMode = $mode;
    }
    public static function getIntegrationMode()
    {
        return self::$integrationMode;
    }
    public function getMode()
    {
        return self::$mode;
    }
    /**
     * Set both the mode of both the gateways and integrations
     *
     * @param mixed $mode
     */
    public static function mode($mode)
    {
        //@@mode = mode
        self::setGatewayMode($mode);
        self::setIntegrationMode($mode);
    }
    /**
     * Return the matching gateway for the provider
     * <tt>bogus</tt>: BogusGateway - Does nothing (for testing)
     * <tt>moneris</tt>: MonerisGateway
     * <tt>authorize_net</tt>: AuthorizeNetGateway
     * <tt>trust_commerce</tt>: TrustCommerceGateway
     *
     * @param string $name
     * @return string
     */
    public static function gateway($name)
    {
        return AkInflector::camelize(strtolower((string)$name) . '_gateway');
    }
    /**
     * Return the matching inegration module
     * <tt>bogus</tt>: BogusGateway - Does nothing (for testing)
     * <tt>chronopay</tt>: Chronopay - Does nothing (for testing)
     * <tt>paypal</tt>: Chronopay - Does nothing (for testing)
     *
     * @param string $name
     * @return string
     */
    public static function integration($name)
    {
        return AkInflector::camelize(strtolower((string)$name));
    }
    /**
     * A check to see if we're in test mode
     * 
     * @return boolean
     */
    public static function isTest()
    {
        return self::getGatewayMode() == 'test';
    }
}
