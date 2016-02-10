<?php


require '../vendor/autoload.php';

use LibCloud\Dns\DnsFactory;

/**
 * Go Daddy
 */
$provider = DnsFactory::getProvider('go_daddy', '', '');
$zones = $provider->listZones();
$zone = $zones[0];
echo '<pre>';
print_r($zones);
print_r($provider->listRecords($zone));
print_r($provider->getRecord($zone, 'www:CNAME'));
echo '</pre>';
