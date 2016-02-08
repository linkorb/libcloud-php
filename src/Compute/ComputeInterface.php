<?php

namespace Linkorb\LibCloud\Compute;

use Linkorb\LibCloud\Compute\Model\Node;
use Linkorb\LibCloud\Compute\Model\NodeSize;
use Symfony\Component\HttpFoundation\ParameterBag;

interface ComputeInterface
{
    public function create_node(ParameterBag $parameters);
    public function boot_node(Node $node);
    public function list_nodes($nodeId = null);
    public function shutdown_node(Node $node);
    public function reboot_node(Node $node);
    public function clone_node(Node $node, ParameterBag $parameters);
    public function destroy_node(Node $node);
    public function resize_node(Node $node, NodeSize $nodeSize);
    public function update_node(Node $node, ParameterBag $parameters);
    public function list_sizes($nodeSizeId = null);
    public function list_locations($nodeLocationId = null);
    public function list_images($nodeImageId = null);
}
