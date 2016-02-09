<?php

namespace LibCloud\Compute\Model;


class Node
{
    /**
     * @var int $id Node id
     */
    protected $id;

    /**
     * @var string $name Node name
     */
    protected $name;

    /**
     * @var int $state Node state. one of the NodeState constants
     */
    protected $state;

    /**
     * @var array $public_ips Node public ips
     */
    protected $public_ips;

    /**
     * @var array $private_ips Node private ips
     */
    protected $private_ips;

    /**
     * @var string $provider Node provider name
     */
    protected $provider;

    /**
     * @var NodeSize $size
     */
    protected $size;

    /**
     * @var NodeImage $image
     */
    protected $image;

    /**
     * @var array $extra Node extra parameters
     */
    protected $extra;

    public function __construct($id, $name, $state, $public_ips, $private_ips, $provider, $size, $image, $extra = [])
    {
        $this->id = $id;
        $this->name = $name;
        $this->state = $state;
        $this->public_ips = $public_ips;
        $this->private_ips = $private_ips;
        $this->provider = $provider;
        $this->size = $size;
        $this->image = $image;
        $this->extra = $extra;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
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
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param int $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @return array
     */
    public function getPublicIps()
    {
        return $this->public_ips;
    }

    /**
     * @param array $public_ips
     */
    public function setPublicIps($public_ips)
    {
        $this->public_ips = $public_ips;
    }

    /**
     * @return array
     */
    public function getPrivateIps()
    {
        return $this->private_ips;
    }

    /**
     * @param array $private_ips
     */
    public function setPrivateIps($private_ips)
    {
        $this->private_ips = $private_ips;
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
     * @return NodeSize
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param NodeSize $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    /**
     * @return NodeImage
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param NodeImage $image
     */
    public function setImage($image)
    {
        $this->image = $image;
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
