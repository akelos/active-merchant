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