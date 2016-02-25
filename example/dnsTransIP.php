<?php

//define('AUTOLOADER_DEBUG', 1) ;

require 'vendor/autoload.php';

use LibCloud\Dns\DnsFactory;

/**
 * TransIP
 */
$provider = DnsFactory::getProvider('TransIP', 'PUT_YOUR_LOGIN_HERE', 'PUT_YOUR_PRIVATE_KEY_HERE');

$zones = $provider->listZones();
var_dump($zones) ;

foreach($zones as $k => $v) {
    $records = $provider->listRecords($v) ;
    var_dump($records) ;
}

$zone = $provider->getZone($zones[0]->getDomain()) ;
var_dump($zone) ;

/*
use Symfony\Component\HttpFoundation\ParameterBag;
$param = new ParameterBag(['name' => 'test-alias', 'type' => 'CNAME', 'ttl' => 3600, 'data' => '@']) ;
$res = $provider->createRecord($zone, $param) ;
var_dump($res) ;
$records = $provider->listRecords($zone) ;
var_dump($records) ;


$record = $provider->getRecord($zone, "test-alias:CNAME") ;
use Symfony\Component\HttpFoundation\ParameterBag;
// we want update only ttl field
$param = new ParameterBag(['name' => 'test-alias', 'type' => 'CNAME', 'ttl' => 7200]) ;
$res = $provider->updateRecord($record, $param) ;
var_dump($res) ;
$records = $provider->listRecords($zone) ;
var_dump($records) ;
*/