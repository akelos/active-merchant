<?php
class ActiveMerchantResponse
{
    public $params, $message, $test, $authorization;
    public $success, $fraud_review;
    //public $avs_result, $cvv_result;
    public function isSuccess()
    {
        return $this->success;
    }
    public function getMessage()
    {
        return $this->message;
    }
    public function isTest()
    {
        return $this->test;
    }
    public function isFraudReviewed()
    {
        return $this->fraud_review;
    }
    public function __construct($success, $message, $params = array(), $options = array())
    {
        $this->success = $success;
        $this->message = $message;
        $this->params = $params;
        
        $this->test = isset($options['test']) ? $options['test'] : false;
        $this->authorization = isset($options['authorization']) ? $options['authorization'] : false;
        $this->fraud_review = isset($options['fraud_review']) ? $options['fraud_review'] : false;
        
    /*
        $avs = new AVSResult($options['avs_result']);
        $this->avs_result = $avs->to_hash();
        $cvv = new CVVResult($options['cvv_result']);
        $this->cvv_result = $cvv->to_hash();
    */
    }
}