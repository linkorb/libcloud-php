<?php

use LibCloud\Compute\ComputeFactory;
use Symfony\Component\HttpFoundation\ParameterBag;

require_once(__DIR__ . '/../common.php');

/**
 * Digital Ocean
 */
$provider = ComputeFactory::getProvider('digital_ocean', '');

// get all nodes
$nodes = $provider->listNodes();

// we can get one node by providing nodeId to listNodes() method
$node = $provider->listNodes(123123);

// we can perform next actions with Node
$provider->shutdownNode($node); //
$provider->bootNode($node); //
$provider->rebootNode($node); //
$provider->destroyNode($node);

// clone method not supported by digital ocean api

// create Node we must provide size (size in digitalocean), 
// location (region in digitalocean) and image (image in digitalocean)
$sizes = $provider->listSizes(); // list digitalocean sizes
$locations = $provider->listLocations(); // list digitalocean regions
$images = $provider->listImages(); // list digitalocean images
$createdNode = $provider->createNode(
    new ParameterBag(['size' => $sizes[0], 'location' => $locations[0], 'image' => $images[0]])
);

// resize. parameters Node and new size
$provider->resizeNode($createdNode, $sizes[1]);

// update
// digital ocean api has a few more methods to work with Node
$provider->updateNode($node, new ParameterBag(['action' => 'rename', 'name' => 'new-droplet-label']));
$provider->updateNode($node, new ParameterBag(['action' => 'passwordReset']));
$provider->updateNode($node, new ParameterBag(['action' => 'enableBackups']));
$provider->updateNode($node, new ParameterBag(['action' => 'disableBackups']));
$provider->updateNode($node, new ParameterBag(['action' => 'enableIpv6']));
$provider->updateNode($node, new ParameterBag(['action' => 'enablePrivateNetworking']));
