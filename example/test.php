<?php


require '../vendor/autoload.php';

$provider = \Linkorb\LibCloud\Compute\ComputeFactory::getProvider('digital_ocean', '');

$nodes = $provider->list_nodes();
echo '<pre>';
print_r($nodes);
print_r($provider->list_sizes());
echo '</pre>';
// $node = $nodes[0];
//$provider->shutdown_node($node);


$provider = \Linkorb\LibCloud\Compute\ComputeFactory::getProvider('linode', '');
echo '<pre>';
//print_r($node);
print_r($provider->list_nodes());
echo '</pre>';