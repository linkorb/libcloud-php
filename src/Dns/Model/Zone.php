<?php

namespace LibCloud\Dns\Model;

class Zone {

    /**
     * @var string $id Zone id
     */
    protected $id;

    /**
     * @var string $domain The name of the domain
     */
    protected $domain;

    /**
     * @var string $type Zone type (master, slave)
     */
    protected $type;

    /**
     * @var int $ttl Default TTL for records in this zone (in seconds)
     */
    protected $ttl;

    /**
     * @var string $provider DNS provider name
     */
    protected $provider;

    /**
     * @var array $extra (optional) Extra attributes (driver specific)
     */
    protected $extra;

    public function __construct($id, $domain, $type, $ttl, $provider, $extra = [])
    {
        $this->id = $id;
        $this->domain = $domain;
        $this->type = $type;
        $this->ttl = $ttl;
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
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param string $domain
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return int
     */
    public function getTtl()
    {
        return $this->ttl;
    }

    /**
     * @param int $ttl
     */
    public function setTtl($ttl)
    {
        $this->ttl = $ttl;
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
     * @return mixed
     */
    public function getExtra()
    {
        return $this->extra;
    }

    /**
     * @param mixed $extra
     */
    public function setExtra($extra)
    {
        $this->extra = $extra;
    }
}
