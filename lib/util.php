<?php
class ActiveMerchantUtil
{
    /**
     * generates and returns a unique id
     *
     * @return string
     */
    public static function generate_unique_id()
    {
        return md5(uniqid(rand(), true));
    }
}
