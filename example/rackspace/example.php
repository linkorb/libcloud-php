<?php

use LibCloud\Compute\ComputeFactory;
use Symfony\Component\HttpFoundation\ParameterBag;

require_once(__DIR__ . '/../common.php');

/**
 * Rackspace
 */
$provider = ComputeFactory::getProvider('rackspace', 'some_username:some_api_key');

// get all nodes
$nodes = $provider->listNodes();

// we can get one node by providing nodeId to listNodes() method
$node = $provider->listNodes('123-123');

// we can perform next actions with Node
$provider->rebootNode($node); //
$provider->destroyNode($node);

// create Node - we must provide a name, size (flavor in rackspace) and image
$sizes = $provider->listSizes(); // list rackspace flavors
$locations = $provider->listLocations(); // list rackspace regions
$images = $provider->listImages(); // list rackspace images
$createdNode = $provider->createNode(new ParameterBag(['name' => 'SomeName', 'size' => $sizes[0], 'image' => $images[0]]));

// resize. parameters Node and new size
$provider->resizeNode($createdNode, $sizes[1]);

// update
// rackspace api allows only a few properties to be updated
$provider->updateNode($node, new ParameterBag(['name' => 'SomeOtherName', 'accessIPv4' => '203.0.113.5']));
$provider->updateNode($node, new ParameterBag(['accessIPv6' => '2001:DB8::1337']));
