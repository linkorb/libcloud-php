<?php

namespace LibCloud\Compute\Model;


class NodeImage
{
    /**
     * @var string $id Image ID
     */
    protected $id;

    /**
     * @var string $name Image name
     */
    protected $name;

    /**
     * @var string $provider Provider this image belongs to
     */
    protected $provider;

    /**
     * @var array $extra Optional provided specific attributes associated with this image
     */
    protected $extra;

    public function __construct($id, $name, $provider, $extra = [])
    {
        $this->id = $id;
        $this->name = $name;
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
