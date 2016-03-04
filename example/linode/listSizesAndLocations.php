<?php

require_once('linode.php');

$sizes = $provider->listSizes(); // list linode plans
print_r($sizes);

$locations = $provider->listLocations(); // list linode datacenters
print_r($locations);
