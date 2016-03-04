<?php

require_once('linode.php');

$nodes = $provider->listNodes();
print_r($nodes);
//exit();
// to clone we must provide Node, size (plan in linode)
// and location (datacenter in linode). optional parameter is paymentterm = 1, 12 or 24

$clonedNode = $provider->cloneNode($node, new ParameterBag(['size' => $sizes[0], 'location' => $locations[0]]));

// update
// linode node update method supports next parameters https://www.linode.com/api/linode/linode.update
// for example
$provider->updateNode($node, new ParameterBag(['Label' => 'new-node-label']));
