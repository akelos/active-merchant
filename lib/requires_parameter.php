<?php
class ActiveMerchantRequiresParameter
{
    /**
     * Checks if the given array of hash has all the array of "key params" defined
     *
     * @param array $hash
     * @param array $params
     * @throws Exception
     */
    public static function requires(Array $hash = array(), Array $params = array())
    {
        foreach($params as $param) {
            if(is_array($param)) {
                if(!isset($param[0]) || !isset($hash[$param[0]])) {
                    throw new Exception('Missing required parameter: ' . $param[0]);
                }
                $valid_options = array_slice($param, 1);
                if(!in_array($hash[$param[0]], $valid_options)) {
                    throw new Exception('Parameter: ' . $param[0] . ' must be one of ' . implode(' or ', $valid_options));
                }
            } else {
                if(!isset($hash[$param])) {
                    throw new Exception('Missing required parameter: ' . $param);
                }
            }
        }
    }
}
