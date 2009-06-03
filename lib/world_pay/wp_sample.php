<?php
$merchantCode = "YourMerchantCode"; // Don't put this in a public readable place
$password = "YourMerchantPassword"; // Don't put this in a public readable place
$orderCode = "example" . time();

// Shopper specific details
$shopperEmailAddress = "myemail@demo.com";
$shopperID = "shopper-123456";
$firstName = "John";
$lastName = "Doe";
$shopperStreet = "11 Hereortherestreet";
$postalCode = "1234 KL";
$shopperCity = "myCiti";
$shopperTelephone = "00123456789";
$countryCode = "TP";

$url = "https://$merchantCode:$password@secure-test.bibit.com/jsp/merchant/xml/paymentService.jsp"; //it is better to keep this url outside your HTML dir which has public (internet) access


//$xml is the order string to send to bibit
$xml = "<?xml version='1.0'?>
<!DOCTYPE paymentService PUBLIC '-//Bibit/DTD Bibit PaymentService v1//EN' 'http://dtd.bibit.com/paymentService_v1.dtd'>
<paymentService version='1.0' merchantCode='DEMO'>
<submit>
<order orderCode = '" . $orderCode . "'>
<description>PDD webshop</description>
<amount value='1982' currencyCode = 'EUR' exponent = '2'/>
<orderContent>
<![CDATA[
<center><table>
<tr><td bgcolor='#CCCCCC'>Your Internet Order:</td><td colspan='2' bgcolor='#ffff00' align='right'>AY 845</td></tr>
<tr><td bgcolor='#ffff00'>Description:</td><td>14 Tulip bulbs</td><td align='right'>1,00</td></tr>
<tr><td colspan='2'>Subtotal:</td><td align='right'>14,00</td></tr>
<tr><td colspan='2'>VAT: 13%</td><td align='right'>1,82</td></tr>
<tr><td colspan='2'>Shipping and Handling:</td><td align='right'>4,00</td></tr>
<tr><td colspan='2' bgcolor='#c0c0c0'>Total cost:</td><td bgcolor='#c0c0c0' align='right'>Euro 19,82</td></tr>
<tr><td colspan='3'>&nbsp;</td></tr>
<tr><td bgcolor='#ffff00' colspan='3'>Your billing address:</td></tr>
<tr><td colspan='3'>Mr. $lastName,<br>$shopperStreet,<br>$postalCode $shopperCity,<br>Thisplace.</td></tr>
<tr><td colspan='3'>&nbsp;</td></tr>
<tr><td bgcolor='#ffff00' colspan='3'>Your shipping address:</td></tr>
<tr><td colspan='3'>Mr. $lastName,<br>$shopperStreet,<br>$postalCode $shopperCity,<br>Thisplace.</td></tr>
<tr><td colspan='3'>&nbsp;</td></tr>
<tr><td bgcolor='#ffff00' colspan='3'>Our contact information:</td></tr>
<tr><td colspan='3'>ACME Webshops Int. Inc.,<br>11 Strangewood Blv.,<br>1255 KZ Thisisit,<br>Nowhereatall.<br><br>acmeweb@acme.inc<br>(555) 1235 456</td></tr>
<tr><td colspan='3'>&nbsp;</td></tr>
<tr><td bgcolor='#c0c0c0' colspan='3'>Billing notice:</td></tr>
<tr><td colspan='3'>Your payment will be handled by Bibit Global Payments Services<br>This name may appear on your bank statement<br>http://www.bibit.com</td></tr>
</table></center>
]]>
</orderContent>
<paymentMethodMask>
<include code='ALL'/>
</paymentMethodMask>
<shopper>
<shopperEmailAddress>$shopperEmailAddress</shopperEmailAddress> <authenticatedShopperID>$shopperID</authenticatedShopperID>
</shopper>
<shippingAddress>
<address>
<firstName>$firstName</firstName>
<lastName>$lastName</lastName>
<street>$shopperStreet</street>
<postalCode>$postalCode</postalCode>
<city>$shopperCity</city>
<countryCode>$countryCode</countryCode>
<telephoneNumber>$shopperTelephone</telephoneNumber>
</address>
</shippingAddress>
</order>
</submit>
</paymentService>"; //$xml is the order string to send to bibit


//let's make the socket connection
//make socket connection with bibit
//the curl library is used. make sure you have it installed propperly
// more info: http://www.php.net/manual/en/ref.curl.php
function send()
{
    $ch = curl_init($this->url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xml); //$xml is the xml string
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_NOPROGRESS, 0);
    // echo "ch: $ch<HR>";
    $result = curl_exec($ch); // result will contain XML reply from Bibit curl_close ($ch);
    if($result == false) {
        $this->error = "Curl could not retrieve page '$this->url', curl_exec returns false";
    }
    return $result;
}

//now we have the result from bibit containing the xml answer. we need to parse this through the XML parser
// initialize parser //using SAX parser
//
//more info on SAX/PHP on these urls
// http://www.devshed.com/Server_Side/XML/XMLwithPHP/XMLwithPHP1/page1.html
// http://www.php.net/manual/en/ref.xml.php


function startElement($parser, $name, $attrs)
{
    global $currentTag, $ordercode, $referenceID, $errorcode, $url_togoto;
    $currentTag = $name;
    
    switch($name) {
        case "ERROR": 
            /*  
            THERE IS AN XML ERROR REPLY
            1 : internal error, could be everything
            2 : parse error, invalid xml
            3 : invalid number of transactions in batch
            4 : security error
            5 : invalid request
            6 : invalid content, occurs when xml is valid but content of xml not
            7 : payment details in the order element are incorrect
            */
            $errorcode = $attrs['CODE']; //example of how to catch the error code number (i.e. 1 to 7)
            $url_error = "error_order.php";
            break;
        case "REFERENCE":
            $referenceID = $attrs['ID']; //for storage in your own database
            break;
        case "ORDERSTATUS":
            $ordercode = $attrs['ORDERCODE'];
            break;
        default:
            break;
    }
}
//////////////////
function endElement($parser, $name)
{
    global $currentTag;
    $currentTag = "";
}
/////////////////
function characterData($parser, $result)
{
    global $currentTag;
    global $url_togoto;
    switch($currentTag) {
        case "REFERENCE":
            //there is a REFERENCE so there must be an url which was provided by bibit for the actual payment. echo $result;
            $url_togoto = $result;
            break;
        default:
            break;
    }
}
global $currentTag, $ordercode, $referenceID, $errorcode, $url_togoto; //define globals
$xml_parser = xml_parser_create();

// set callback functions
xml_set_element_handler($xml_parser, "startElement", "endElement");
xml_set_character_data_handler($xml_parser, "characterData");

if(!xml_parse($xml_parser, $result)) {
    die(sprintf("XML error: %s at line %d", xml_error_string(xml_get_error_code($xml_parser)), xml_get_current_line_number($xml_parser)));
}
// clean up
xml_parser_free($xml_parser);
//now we have a few important variables. Depending on what variables are set you can now perform an action like storing id's in your database or refer to a different url.
echo "ordercode=$ordercode<BR>";
echo "referenceID=$referenceID<BR>";
echo "errorcode=$errorcode<BR>";
echo "url_togoto=$url_togoto<BR>";