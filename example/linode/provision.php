<?php

use Symfony\Component\HttpFoundation\ParameterBag;
use LibCloud\Compute\Model\NodeSize;
use LibCloud\Compute\Model\NodeLocation;

require_once('linode.php');

$size = new NodeSize(1); // 1024
$location = new NodeLocation(10); // Frankfurt

$paymentTermId = 1;
// to create we must provide size (plan in linode)
// and location (datacenter in linode).
// optional parameter is paymentterm = 1, 12 or 24
$createdNode = $provider->provisionNode(
    new ParameterBag(
        [
            'size' => $size,
            'location' => $location,
            'paymentterm' => $paymentTermId,
            'stackscriptid' => 17150,
            'distributionid' => 124,
            'label' => 'Cool label',
            'disksize' => '24000',
            'rootpassword' => 'w00t',
            'StackScriptUDFResponses' =>
                '{"fqdn": "walter.members.oplexing.com", "puppetmaster": "puppet.oplexing.com"}'
        ]
    )
);

print_r($createdNode);
