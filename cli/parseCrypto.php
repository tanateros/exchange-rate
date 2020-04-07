<?php

require_once __DIR__ . "/../config/constants.php";
$ch = curl_init("http:/" . DOMAIN_ADDRESS . '/api/crypto/get-rates');
curl_exec($ch);
curl_close($ch);