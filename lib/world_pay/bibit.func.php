<?
function StartElement($parser, $name, $attrs)
{
    global $resultArray;
    $resultArray['currentTag'] = $name;
    switch($name) {
        case 'ERROR':
            $resultArray['errorcode'] = $attrs['CODE']; //example of how to catch the error code number (i.e. 1 to 7)
            // $url_error = 'error_order.php';
            break;
        case 'REFERENCE':
            $resultArray['referenceID'] = $attrs['ID']; //for storage in your own database
            break;
        case 'ORDERSTATUS':
            $resultArray['ordercode'] = $attrs['ORDERCODE'];
            break;
        default:
            break;
    }
}
function EndElement($parser, $name)
{
    global $resultArray;
    $resultArray['currentTag'] = '';
}
function CharacterData($parser, $result)
{
    global $resultArray;
    switch($resultArray['currentTag']) {
        case 'REFERENCE':
            //there is a REFERENCE so there must be an url which was provided by bibit for the actual payment. echo $result;
            $resultArray['url_togoto'] = $result;
            break;
        default:
            break;
    }
}
function ParseXML($bibitResult)
{
    $xml_parser = xml_parser_create();
    // set callback functions
    xml_set_element_handler($xml_parser, 'startElement', 'endElement');
    xml_set_character_data_handler($xml_parser, 'characterData');
    if(!xml_parse($xml_parser, $bibitResult)) {
        die(sprintf('XML error: %s at line %d', xml_error_string(xml_get_error_code($xml_parser)), xml_get_current_line_number($xml_parser)));
    }
    // clean up
    xml_parser_free($xml_parser);
}