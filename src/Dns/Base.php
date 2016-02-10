<?php

namespace LibCloud\Dns;

use LibCloud\Dns\Model\Zone;

abstract class Base implements DnsInterface
{
    protected $accessToken;
    protected $accessTokenSecret;

    public function __construct($accessToken, $accessTokenSecret)
    {
        $this->accessToken = $accessToken;
        $this->accessTokenSecret = $accessTokenSecret;
    }

    abstract protected function toZone($response);
    abstract protected function toRecord($response, Zone $zone);
}
