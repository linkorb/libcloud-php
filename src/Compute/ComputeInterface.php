<?php

namespace LibCloud\Compute;

use LibCloud\Compute\Model\Node;
use LibCloud\Compute\Model\NodeSize;
use Symfony\Component\HttpFoundation\ParameterBag;

interface ComputeInterface
{
    public function createNode(ParameterBag $parameters);
    public function bootNode(Node $node);
    public function listNodes($nodeId = null);
    public function shutdownNode(Node $node);
    public function rebootNode(Node $node);
    public function cloneNode(Node $node, ParameterBag $parameters);
    public function destroyNode(Node $node);
    public function resizeNode(Node $node, NodeSize $nodeSize);
    public function updateNode(Node $node, ParameterBag $parameters);
    public function listSizes($nodeSizeId = null);
    public function listLocations($nodeLocationId = null);
    public function listImages($nodeImageId = null);
}
