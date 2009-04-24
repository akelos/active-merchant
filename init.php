<?php

class ActiveMerchantPlugin extends AkPlugin
{
    function load()
    {
        define('AK_ACTIVE_MERCHANT_DIR', $this->getPath());
        define('AK_ACTIVE_MERCHANT_LIB_DIR', AK_ACTIVE_MERCHANT_DIR . DS . 'lib');
        require_once AK_ACTIVE_MERCHANT_LIB_DIR . DS . 'active_merchant.php';
    }
}