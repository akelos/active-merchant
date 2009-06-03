<?
class Bibit
{
    var $merchantCode;
    var $merchantPassword;
    var $xml;
    var $orderId;
    var $totalammount;
    var $shopperDetails;
    var $description;
    
    function Bibitstart($debug)
    {
        $this->debug = $debug;
        if($this->debug) {
            $this->url = 'https://' . $this->merchantCode . ':' . $this->merchantPassword . '@secure-test.bibit.com/jsp/merchant/xml/paymentService.jsp';
        } else {
            $this->url = 'https://' . $this->merchantCode . ':' . $this->merchantPassword . '@secure.bibit.com/jsp/merchant/xml/paymentService.jsp';
        }
    }
    function CreateConnection()
    {
        $ch = curl_init($this->url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->xml); //$xml is the xml string
        curl_setopt($ch, CURLOPT_HTTPHEADER, Array(
            'Content-Type: text/xml'
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_NOPROGRESS, 0);
        // echo 'ch: $ch<HR>' ;
        $result = curl_exec($ch); // result will contain XML reply from Bibit
        curl_close($ch);
        if($result == false) {
            print 'Curl could not retrieve page ' . $this->url . ', curl_exec returns false';
        }
        return $result;
    }
    function StartXML()
    {
        $this->xml = <<<EOT
<?xml version='1.0' encoding='UTF-8'?>
<!DOCTYPE paymentService PUBLIC '-//Bibit//DTD Bibit PaymentService v1//EN' 'http://dtd.bibit.com/paymentService_v1.dtd'>
<paymentService version='1.4' merchantCode='{$this->merchantCode}'>
  <submit>
    <order orderCode = '{$this->orderId}'>
      <description>{$this->description}</description>
      <amount value='{$this->totalammount}' currencyCode = 'EUR' exponent = '2'/>\n
EOT;
    }
    function FillDataXML($invoiceData)
    {
        $this->xml .= <<<EOT
      <orderContent>
        <![CDATA[{$invoiceData}]]>
      </orderContent>
      <paymentMethodMask>
        <include code='ALL'/>
      </paymentMethodMask>
EOT;
    }
    function FillShopperXML($shopperArray)
    {
        $this->xml .= <<<EOT
      <shopper>
        <shopperEmailAddress>{$shopperArray['email']}</shopperEmailAddress>
      </shopper>
      <shippingAddress>
        <address>
          <firstName>{$shopperArray['firstname']}</firstName>
          <lastName>{$shopperArray['lastname']}</lastName>
          <street>{$shopperArray['street']}</street>
          <postalCode>{$shopperArray['postalcode']}</postalCode>
          <city>{$shopperArray['city']}</city>
          <countryCode>{$shopperArray['countrycode']}</countryCode>
          <telephoneNumber>{$shopperArray['telephone']}</telephoneNumber>
        </address>
      </shippingAddress>\n
EOT;
    }
    function EndXML()
    {
        $this->xml .= <<<EOT
    </order>
  </submit>
</paymentService>
EOT;
    }
}