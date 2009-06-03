<?
error_reporting(E_ALL);
ini_set('display_errors', true);

/* Set to true while testing */
$debug = true;

/* While testing use the TEST merchantcode and password */
$merchantCode = 'yourmerchantcode';
$merchantPassword = 'yourmerchantpassword';

include('bibit.func.php');
include('bibit.lib.php');

$_bibit = new Bibit($debug);
$_bibit->merchantCode = $merchantCode;
$_bibit->merchantPassword = $merchantPassword;

$_bibit->Bibitstart(true);
$_bibit->orderId = time(); /* Generate a unique orderid, bibit only accepts unique orders  */
$_bibit->totalammount = "1982"; /* Standard 2 exponents, 1982 means EUR 19,82 (if using EUR) */
$_bibit->description = "ACME Webshops Int. Inc."; /* Description of the shop */

// shopperArray contains shopper information. countrycode should be a bibit country code
$shopperArray = array(
    "email" => "your@emailaddress.com", 
    "firstname" => "yourfirstname", 
    "lastname" => "yourlastname", 
    "street" => "yourstreet", 
    "postalcode" => "yourpostalcode", 
    "city" => "yourcity", 
    "telephone" => "yourtelephone", 
    "countrycode" => "NL"
);
// orderContent contains the HTML invoice
$orderContent = <<<EOT
<center><table>
<tr><td bgcolor='#CCCCCC'>Your Internet Order:</td><td colspan='2' bgcolor='#ffff00' align='right'>AY 845</td></tr>
<tr><td bgcolor='#ffff00'>Description:</td><td>14 Tulip bulbs</td><td align='right'>1,00</td></tr>
<tr><td colspan='2'>Subtotal:</td><td align='right'>14,00</td></tr>
<tr><td colspan='2'>VAT: 13%</td><td align='right'>1,82</td></tr>
<tr><td colspan='2'>Shipping and Handling:</td><td align='right'>4,00</td></tr>
<tr><td colspan='2' bgcolor='#c0c0c0'>Total cost:</td><td bgcolor='#c0c0c0' align='right'>Euro 19,82</td></tr>
<tr><td colspan='3'>&nbsp;</td></tr>
<tr><td bgcolor='#ffff00' colspan='3'>Your billing address:</td></tr>
<tr><td colspan='3'>Mr. {$shopperArray['lastname']},<br>{$shopperArray['street']},<br>{$shopperArray['postalcode']} {$shopperArray['city']},<br>Thisplace.</td></tr>
<tr><td colspan='3'>&nbsp;</td></tr>
<tr><td bgcolor='#ffff00' colspan='3'>Your shipping address:</td></tr>
<tr><td colspan='3'>Mr. {$shopperArray['lastname']},<br>{$shopperArray['street']},<br>{$shopperArray['postalcode']} {$shopperArray['city']},<br>Thisplace.</td></tr>
<tr><td colspan='3'>&nbsp;</td></tr>
<tr><td bgcolor='#ffff00' colspan='3'>Our contact information:</td></tr>
<tr><td colspan='3'>ACME Webshops Int. Inc.,<br>11 Strangewood Blv.,<br>1255 KZ Thisisit,<br>Nowhereatall.<br><br>acmeweb@acme.inc<br>(555) 1235 456</td></tr>
<tr><td colspan='3'>&nbsp;</td></tr>
<tr><td bgcolor='#c0c0c0' colspan='3'>Billing notice:</td></tr>
<tr><td colspan='3'>Your payment will be handled by Bibit Global Payments Services<br>This name may appear on your bank statement<br>http://www.bibit.com</td></tr>
</table></center>
EOT;

$_bibit->StartXML();
$_bibit->FillDataXML($orderContent);
$_bibit->FillShopperXML($shopperArray);
$_bibit->EndXML();
$_bibit->xml = utf8_encode($_bibit->xml);
$bibitResult = $_bibit->CreateConnection();

$resultArray = array(
    "currentTag" => "", 
    "orderCode" => "", 
    "referenceID" => "", 
    "errorcode" => "", 
    "url_togoto" => ""
);
ParseXML($bibitResult);
print "<p style=\"font-weight: bold;\">Reply from the bibit server:</p>";
echo "<pre>";
print_r($resultArray);
echo "</pre>";

/**
  THERE IS AN XML ERROR REPLY
  1 : internal error, could be everything
  2 : parse error, invalid xml
  3 : invalid number of transactions in batch
  4 : security error
  5 : invalid request
  6 : invalid content, occurs when xml is valid but content of xml not
  7 : payment details in the order element are incorrect
 */