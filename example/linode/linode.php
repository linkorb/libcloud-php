<?php

use LibCloud\Compute\ComputeFactory;

require_once(__DIR__ . '/../common.php');

$apiKey = getenv('LIBCLOUD_LINODE_APIKEY');
echo "APIKEY: " . $apiKey . "\n";
/**
 * Linode
 */
$provider = ComputeFactory::getProvider('linode', $apiKey);
