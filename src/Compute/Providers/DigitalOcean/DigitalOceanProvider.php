<?php

namespace LibCloud\Compute\Providers\DigitalOcean;

use LibCloud\Compute\Base;
use LibCloud\Compute\Model\Node;
use LibCloud\Compute\Model\NodeImage;
use LibCloud\Compute\Model\NodeLocation;
use LibCloud\Compute\Model\NodeSize;
use LibCloud\Compute\Model\NodeState;
use Symfony\Component\HttpFoundation\ParameterBag;
use DigitalOceanV2\Adapter\GuzzleHttpAdapter;
use DigitalOceanV2\DigitalOceanV2;

class DigitalOceanProvider extends Base
{
    private $digitalocean;
    private $stateMap = ['new' => NodeState::PENDING, 'off' => NodeState::STOPPED, 'active' => NodeState::RUNNING,
        'archive' => NodeState::TERMINATED];

    public function __construct($accessToken)
    {
        parent::__construct($accessToken);
        $adapter = new GuzzleHttpAdapter($this->accessToken);
        $this->digitalocean = new DigitalOceanV2($adapter);
    }

    public function createNode(ParameterBag $parameters)
    {
        try{
            $name = $parameters->get('name'); // human readable name of the newly created droplet
            $location = $parameters->get('location')->getId(); // datacenter region id
            $size = $parameters->get('size')->getName(); // size name. digital ocean does not have id in Size
            $image = $parameters->get('image')->getId(); // image id
            return $this->toNode($this->digitalocean->droplet()->create($name, $location, $size, $image));
        }
        catch (\Exception $e)
        {
            throw $e;
        }
    }

    public function bootNode(Node $node)
    {
        $this->digitalocean->droplet()->powerOn($node->getId());
    }

    public function listNodes($nodeId = null)
    {
        if ($nodeId)
        {
            return $this->toNode($this->digitalocean->droplet()->getById($nodeId));
        }
        return array_map([$this, 'toNode'], $this->digitalocean->droplet()->getAll());
    }

    public function shutdownNode(Node $node)
    {
        $this->digitalocean->droplet()->shutdown($node->getId());
    }

    public function rebootNode(Node $node)
    {
        $this->digitalocean->droplet()->reboot($node->getId());
    }

    public function cloneNode(Node $node, ParameterBag $parameters)
    {
       throw new \Exception('cloneNode method not supported by Digital Ocean API');
    }

    public function destroyNode(Node $node)
    {
        $this->digitalocean->droplet()->delete($node->getId());
    }

    public function resizeNode(Node $node, NodeSize $nodeSize)
    {
        $this->digitalocean->droplet()->resize($node->getId(), $nodeSize->getId());
    }

    public function updateNode(Node $node, ParameterBag $parameters)
    {
        switch ($parameters->get('action'))
        {
            case 'passwordReset':
                return $this->digitalocean->droplet()->passwordReset($node->getId());
            case 'enableBackups':
                return $this->digitalocean->droplet()->enableBackups($node->getId());
            case 'disableBackups':
                return $this->digitalocean->droplet()->disableBackups($node->getId());
            case 'rename':
                return $this->digitalocean->droplet()->rename($node->getId(), $parameters->get('name'));
            case 'enableIpv6':
                return $this->digitalocean->droplet()->enableIpv6($node->getId());
            case 'enablePrivateNetworking':
                return $this->digitalocean->droplet()->enablePrivateNetworking($node->getId());
        }
    }

    public function listSizes($nodeSizeName = null)
    {
        $sizes = array_map([$this, 'toSize'], $this->digitalocean->size()->getAll());
        if ($nodeSizeName)
        {
            foreach ($sizes as $size)
            {
                if ($size->getName() == $nodeSizeName)
                    return $size;
            }
        }
        return $sizes;
    }

    public function listLocations($nodeLocationId = null)
    {
        $locations = array_map([$this, 'toLocation'], $this->digitalocean->region()->getAll());
        if ($nodeLocationId)
        {
            foreach ($locations as $location)
            {
                if ($location->getId() == $nodeLocationId)
                    return $location;
            }
        }
        return $locations;
    }

    public function listImages($nodeImageId = null)
    {
        if ($nodeImageId)
        {
            return $this->toImage($this->digitalocean->image()->getById($nodeImageId));
        }
        return array_map([$this, 'toImage'], $this->digitalocean->image()->getAll());
    }

    protected function toNode($dropletEntity)
    {
        $public_ips = $private_ips = $extra = [];

        foreach ($dropletEntity->networks as $network)
        {
            if ($network->type == 'public')
            {
                $public_ips[] = $network->ipAddress;
            }
            else
            {
                $private_ips[] = $network->ipAddress;
            }
        }

        $size = $this->toSize($dropletEntity->size);
        $image = $this->toImage($dropletEntity->image);

        return new Node($dropletEntity->id, $dropletEntity->name, $this->stateMap[$dropletEntity->status], $public_ips,
            $private_ips, 'digital_ocean', $size, $image, $extra);
    }

    protected function toSize($sizeEntity)
    {
        return new NodeSize(null, $sizeEntity->slug, $sizeEntity->memory, $sizeEntity->disk, $sizeEntity->transfer,
            $sizeEntity->priceHourly, 'digital_ocean', ['priceMonthly' => $sizeEntity->priceMonthly]);
    }

    protected function toImage($imageEntity)
    {
        return new NodeImage($imageEntity->id, $imageEntity->distribution . ' ' . $imageEntity->name, 'digital_ocean');
    }

    protected function toLocation($regionEntity)
    {
        return new NodeLocation($regionEntity->slug, $regionEntity->name, null, 'digital_ocean');
    }
}
