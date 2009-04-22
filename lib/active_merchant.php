<?php

defined('ACTIVE_MERCHANT_LIB_DIR') ? null : define('ACTIVE_MERCHANT_LIB_DIR', dirname(__FILE__));

class ActiveMerchant
{
    function import()
    {
        $args = func_get_args();
        $args = is_array($args[0]) ? $args[0] : (func_num_args() > 1 ? $args : Ak::stringToArray($args[0]));
        $models = array();
        foreach ($args as $arg){
            $model_name = AkInflector::camelize($arg);
            if (class_exists($model_name)){
                $models[] = $model_name;
                continue;
            }
            $model = ACTIVE_MERCHANT_LIB_DIR.DS.AkInflector::underscore($model_name).'.php';
            if (file_exists($model)){
                $models[] = $model_name;
                include_once($model);
                continue;
            }
        }
        return $models;
    }
}
