<?php

namespace LibCloud\Compute\Providers\Rackspace;

use OpenCloud\Compute\Service as ComputeService;
use OpenCloud\Rackspace as RackspaceClient;

class RackspaceProvider
{
    const ID = 'rackspace';
    const DEFAULT_REGION = 'LON';

    protected $client;
    protected $service;
    protected $accessToken;


    public function __construct($accessToken)
    {
        $this->accessToken = $accessToken;
    }


    public function setClient(RackspaceClient $client)
    {
        $this->client = $client;
    }


    /**
     * @return \OpenCloud\Rackspace
     */
    public function getClient()
    {
        if (! $this->client) {
            $this->client = new RackspaceClient(
                RackspaceClient::UK_IDENTITY_ENDPOINT,
                $this->unserialiseCredentials($this->accessToken)
            );
        }
        return $this->client;
    }


    public function setService(ComputeService $service)
    {
        $this->service = $service;
    }


    /**
     * @return \OpenCloud\Compute\Service
     */
    public function getService()
    {
        if (! $this->service) {
            $this->getClient()->authenticate();
            $this->service = $this
                ->getClient()
                ->computeService(null, self::DEFAULT_REGION)
            ;
        }
        return $this->service;
    }


    /**
     * "username:api_key" -> ['username' => "username", 'apiKey' => "api_key"]
     *
     * @param string $accessToken
     * @return array
     */
    private function unserialiseCredentials($accessToken)
    {
        list($username, $apiKey) = explode(':', $accessToken);
        return compact('username', 'apiKey');
    }
}
