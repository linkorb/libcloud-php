<?php

namespace LibCloud\Compute\Model;

class NodeLocation
{
    /**
     * @var string $id Location ID
     */
    protected $id;

    /**
     * @var string $name Location name
     */
    protected $name;

    /**
     * @var string $country Location coutnry
     */
    protected $country;

    /**
     * @var string $provider Provider this location belongs to
     */
    protected $provider;

    public function __construct($id, $name = null, $country = null, $provider = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->country = $country;
        $this->provider = $provider;
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
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param string $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
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
}
