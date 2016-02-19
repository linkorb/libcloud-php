<?php


require realpath(__DIR__ . '/../vendor/autoload.php');

use LibCloud\Compute\ComputeFactory;
use Symfony\Component\HttpFoundation\ParameterBag;

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

// create Node we must provide size (size in digitalocean), location (region in digitalocean) and image (image in digitalocean)
$sizes = $provider->listSizes(); // list digitalocean sizes
$locations = $provider->listLocations(); // list digitalocean regions
$images = $provider->listImages(); // list digitalocean images
$createdNode = $provider->createNode(new ParameterBag(['size' => $sizes[0], 'location' => $locations[0], 'image' => $images[0]]));

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


/**
 * Linode
 */
$provider = ComputeFactory::getProvider('linode', '');

$node = $provider->listNodes(123123);
// to clone we must provide Node, size (plan in linode) and location (datacenter in linode). optional parameter is paymentterm = 1, 12 or 24
$sizes = $provider->listSizes(); // list linode plans
$locations = $provider->listLocations(); // list linode datacenters
$clonedNode = $provider->cloneNode($node, new ParameterBag(['size' => $sizes[0], 'location' => $locations[0]]));

// to create we must provide size (plan in linode) and location (datacenter in linode). optional parameter is paymentterm = 1, 12 or 24
$createdNode = $provider->createNode(new ParameterBag(['size' => $sizes[0], 'location' => $locations[0]]));

// update
// linode node update method supports next parameters https://www.linode.com/api/linode/linode.update
// for example
$provider->updateNode($node, new ParameterBag(['Label' => 'new-node-label']));


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
