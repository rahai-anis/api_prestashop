<?php
$host = 'http://127.0.0.1/public_html';
$apiKey = 'ZBPE1QZMA2MXV7KQH2M4A1R1AU8U5YJD';

$orderXml = '<?xml version="1.0" encoding="UTF-8"?>';
$orderXml .= '<prestashop>';


$orderXml .= '<cart_rule>';
$orderXml .= '<id></id>';
$orderXml .= '<id_customer></id_customer>';
$orderXml .= '<date_from>2023-07-04 22:20:06</date_from>';
$orderXml .= '<date_to>2025-07-04</date_to>';
$orderXml .= '<description></description>';
$orderXml .= '<quantity>3</quantity>';
$orderXml .= '<quantity_per_user>1</quantity_per_user>';
$orderXml .= '<priority>1</priority>';
$orderXml .= '<partial_use>1</partial_use>';
$orderXml .= '<code>123456789</code>';
$orderXml .= '<minimum_amount></minimum_amount>';
$orderXml .= '<minimum_amount_tax></minimum_amount_tax>';
$orderXml .= '<minimum_amount_currency></minimum_amount_currency>';
$orderXml .= '<minimum_amount_shipping></minimum_amount_shipping>';
$orderXml .= '<country_restriction></country_restriction>';
$orderXml .= '<carrier_restriction></carrier_restriction>';
$orderXml .= '<group_restriction></group_restriction>';
$orderXml .= '<cart_rule_restriction></cart_rule_restriction>';
$orderXml .= '<product_restriction></product_restriction>';
$orderXml .= '<shop_restriction></shop_restriction>';
$orderXml .= '<free_shipping></free_shipping>';
$orderXml .= '<reduction_percent></reduction_percent>';
$orderXml .= '<reduction_amount>1500.00</reduction_amount>';
$orderXml .= '<reduction_tax></reduction_tax>';
$orderXml .= '<reduction_currency></reduction_currency>';
$orderXml .= '<reduction_product></reduction_product>';
$orderXml .= '<reduction_exclude_special></reduction_exclude_special>';
$orderXml .= '<gift_product></gift_product>';
$orderXml .= '<gift_product_attribute></gift_product_attribute>';
$orderXml .= '<highlight></highlight>';
$orderXml .= '<active>1</active>';
$orderXml .= '<date_add></date_add>';
$orderXml .= '<date_upd></date_upd>';
$orderXml .= '<name>';
$orderXml .= '<language id="1">api test anis</language>';
$orderXml .= '<language id="2">api test anis</language>';
$orderXml .= '</name>';

$orderXml .= '</cart_rule>';


$orderXml .= '</prestashop>';


$ch = curl_init($host . '/api/cart_rules?output_format=XML&ws_key=' . $apiKey);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, $orderXml);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Content-Length: ' . strlen($orderXml)
));

$response = curl_exec($ch);
curl_close($ch);
var_dump($response);