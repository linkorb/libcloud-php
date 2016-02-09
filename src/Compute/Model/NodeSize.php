<?php

namespace LibCloud\Compute\Model;

class NodeSize
{
    /**
     * @var string $id Size ID
     */
    protected $id;

    /**
     * @var string $name Size name
     */
    protected $name;

    /**
     * @var int $ram Amount of memory (in MB) provided by this size
     */
    protected $ram;

    /**
     * @var int $disk Amount of disk storage (in GB) provided by this image
     */
    protected $disk;

    /**
     * @var int $bandwidth Amount of bandiwdth included with this size
     */
    protected $bandwidth;

    /**
     * @var float $price Price (in US dollars) of running this node for an hour
     */
    protected $price;

    /**
     * @var string $provider Driver this size belongs to
     */
    protected $provider;

    /**
     * @var array $extra Optional provider specific attributes associated with
     */
    protected $extra;

    public function __construct($id, $name, $ram, $disk, $bandwidth, $price, $provider, $extra = [])
    {
        $this->id = $id;
        $this->name = $name;
        $this->ram = $ram;
        $this->disk = $disk;
        $this->bandwidth = $bandwidth;
        $this->price = $price;
        $this->provider = $provider;
        $this->extra = $extra;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getRam()
    {
        return $this->ram;
    }

    /**
     * @param int $ram
     */
    public function setRam($ram)
    {
        $this->ram = $ram;
    }

    /**
     * @return int
     */
    public function getDisk()
    {
        return $this->disk;
    }

    /**
     * @param int $disk
     */
    public function setDisk($disk)
    {
        $this->disk = $disk;
    }

    /**
     * @return int
     */
    public function getBandwidth()
    {
        return $this->bandwidth;
    }

    /**
     * @param int $bandwidth
     */
    public function setBandwidth($bandwidth)
    {
        $this->bandwidth = $bandwidth;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @return string
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @param string $provider
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;
    }

    /**
     * @return array
     */
    public function getExtra()
    {
        return $this->extra;
    }

    /**
     * @param array $extra
     */
    public function setExtra($extra)
    {
        $this->extra = $extra;
    }
}
