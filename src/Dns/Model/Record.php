<?php

namespace LibCloud\Dns\Model;

class Record {

    /**
     * @var string $id Record id
     */
    protected $id;

    /**
     * @var string $name Hostname or FQDN
     */
    protected $name;

    /**
     * @var RecordType $type DNS record type (A, AAAA, ...)
     */
    protected $type;

    /**
     * @var string $data data for the record
     */
    protected $data;

    /**
     * @var Zone $zone Zone instance
     */
    protected $zone;

    /**
     * @var string $provider DNS provider name
     */
    protected $provider;

    /**
     * @var int $ttl Record ttl
     */
    protected $ttl;

    /**
     * @var array $extra (optional) Extra attributes (driver specific)
     */
    protected $extra;

    public function __construct($id, $name, $type, $data, $zone, $provider, $ttl, $extra = [])
    {
        $this->id = $id;
        $this->name = $name;
        $this->type = $type;
        $this->data = $data;
        $this->zone = $zone;
        $this->provider = $provider;
        $this->ttl = $ttl;
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
     * @return RecordType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param RecordType $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return Zone
     */
    public function getZone()
    {
        return $this->zone;
    }

    /**
     * @param Zone $zone
     */
    public function setZone($zone)
    {
        $this->zone = $zone;
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
