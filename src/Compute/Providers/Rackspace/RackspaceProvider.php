<?php

namespace LibCloud\Compute\Providers\Rackspace;

use Guzzle\Http\Exception\ClientErrorResponseException;

use LibCloud\Compute\Base;
use LibCloud\Compute\Model\Node;
use LibCloud\Compute\Model\NodeImage;
use LibCloud\Compute\Model\NodeLocation;
use LibCloud\Compute\Model\NodeSize;

use OpenCloud\Common\Metadata as OpenCloudMetadata;
use OpenCloud\Compute\Constants\ServerState;
use OpenCloud\Compute\Service as ComputeService;
use OpenCloud\Rackspace as RackspaceClient;

use Symfony\Component\HttpFoundation\ParameterBag;

class RackspaceProvider extends Base
{
    const ID = 'rackspace';
    const DEFAULT_REGION = 'LON';

    protected $client;
    protected $service;

    protected $locations = array(
        'DFW' => array('id' => 'DFW', 'name' => 'Dallas-Fort Worth, TX', 'co' => 'USA'),
        'HKG' => array('id' => 'HKG', 'name' => 'Hong Kong', 'co' => 'China'),
        'IAD' => array('id' => 'IAD', 'name' => 'Blacksburg, VA', 'co' => 'USA'),
        'LON' => array('id' => 'LON', 'name' => 'London', 'co' => 'England'),
        'SYD' => array('id' => 'SYD', 'name' => 'Sydney', 'co' => 'Australia'),
    );


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


    public function createNode(ParameterBag $parameters)
    {
        if (null === $parameters->get('name')) {
            throw new \UnexpectedValueException(
                'A name is required for the creation of a node'
            );
        }
        if (null === $parameters->get('image')) {
            throw new \UnexpectedValueException(
                'A NodeImage is required for the creation of a node'
            );
        }
        if (null === $parameters->get('size')) {
            throw new \UnexpectedValueException(
                'A NodeSize is required for the creation of a node'
            );
        }

        $creationParameters = array_merge_recursive(
            array(
                'name' => $parameters->get('name'),
                'imageId' => $parameters->get('image')->getId(),
                'flavorId' => $parameters->get('size')->getId(),
            ),
            $parameters->get('extra', array())
        );

        $server = $this->toServer();
        $response = $server->create($creationParameters);

        # request more info about the node
        $nodes = $this->listNodes($server->id);
        $node = $nodes[0];

        # fill in some image and size detail
        $node->setSize($parameters->get('size'));
        $node->setImage($parameters->get('image'));

        return $node;
    }


    /**
     * @throws \OpenCloud\Common\Exceptions\UnsupportedExtensionError
     */
    public function bootNode(Node $node)
    {
        return $this->toServer($node)->start();
    }

    public function listNodes($nodeId = null)
    {
        $servers = array();

        if ($nodeId) {
            try {
                $servers[] = $this->getService()->server($nodeId);
            } catch (ClientErrorResponseException $e) {
                if (404 !== $e->getResponse()->getStatusCode()) {
                    throw $e;
                }
            }
        } else {
            foreach ($this->getService()->serverList() as $server) {
                $servers[] = $server;
            }
        }

        return array_map(array($this, 'toNode'), $servers);
    }


    /**
     * @throws \OpenCloud\Common\Exceptions\UnsupportedExtensionError
     */
    public function shutdownNode(Node $node)
    {
        return $this->toServer($node)->stop();
    }


    /**
     * @param Node $node
     *
     * @return \Guzzle\Http\Message\Response
     */
    public function rebootNode(Node $node)
    {
        return $this->toServer($node)->reboot(ServerState::REBOOT_STATE_SOFT);
    }


    public function cloneNode(Node $node, ParameterBag $parameters)
    {
        throw new \RuntimeException('This method has not yet been implemented');
    }


    /**
     * @param Node $node
     *
     * @return \Guzzle\Http\Message\Response
     */
    public function destroyNode(Node $node)
    {
        return $this->toServer($node)->delete();
    }


    /**
     * @param Node $node
     * @param NodeSize $nodeSize
     *
     * @return \Guzzle\Http\Message\Response
     */
    public function resizeNode(Node $node, NodeSize $nodeSize)
    {
        return $this->toServer($node)->resize($this->toFlavor($nodeSize));
    }


    /**
     * @param Node $node
     * @param ParameterBag $parameters
     *
     * @return \Guzzle\Http\Message\Response
     *
     * @throws \UnexpectedValueException when trying to update zero parameters
     *                                   or any prohibited parameters
     */
    public function updateNode(Node $node, ParameterBag $parameters)
    {
        $allowableParamKeys = array('name', 'accessIPv4', 'accessIPv6');
        $requestedParamKeys = $parameters->keys();

        if (0 == sizeof($requestedParamKeys)) {
            throw new \UnexpectedValueException(sprintf(
                'Expected values for any of the following properties, but got none: "%s".',
                implode(', ', $allowableParamKeys)
            ));
        }

        $permittedKeys = array_intersect(
            $requestedParamKeys,
            $allowableParamKeys
        );
        $prohibitedKeys = array_diff($requestedParamKeys, $permittedKeys);

        if (sizeof($prohibitedKeys)) {
            throw new \UnexpectedValueException(sprintf(
                'Expected values for any of the following properties "%s". '
                . 'The following requested properties are not allowed: "%s".',
                implode(', ', $allowableParamKeys),
                implode(', ', $prohibitedKeys)
            ));
        }

        $updates = array();
        foreach ($permittedKeys as $k) {
            $updates[$k] = $parameters->get($k);
        }

        $response = $this->toServer($node)->update($updates);

        # It would be nice to have an updateNodeFromServer so that the caller
        # gets the updates (which $server now has) for their Node. For now, just
        # send some more API requests...
        $nodes = $this->listNodes($node->getId());
        # and update the few properties that might have changed
        $node->setName($nodes[0]->getName());
        $node->setPublicIps($nodes[0]->getPublicIps());
        $node->setState($nodes[0]->getState());
        $node->setExtra($nodes[0]->getExtra());

        return $response;
    }


    public function listSizes($nodeSizeId = null)
    {
        $sizes = array();

        if ($nodeSizeId) {
            try {
                $sizes[] = $this->getService()->flavor($nodeSizeId);
            } catch (ClientErrorResponseException $e) {
                if (404 !== $e->getResponse()->getStatusCode()) {
                    throw $e;
                }
            }
        } else {
            foreach ($this->getService()->flavorList() as $size) {
                $sizes[] = $size;
            }
        }

        return array_map(array($this, 'toSize'), $sizes);
    }


    public function listLocations($nodeLocationId = null)
    {
        $locations = array();

        if (! $nodeLocationId) {
            foreach ($this->locations as $_ => $loc) {
                $locations[] = $loc;
            }
        } else if (array_key_exists($nodeLocationId, $this->locations)) {
            $locations[] = $this->locations[$nodeLocationId];
        }

        return array_map(array($this, 'toLocation'), $locations);
    }


    public function listImages($nodeImageId = null)
    {
        $images = array();

        if ($nodeImageId) {
            try {
                $images[] = $this->getService()->image($nodeImageId);
            } catch (ClientErrorResponseException $e) {
                if (404 !== $e->getResponse()->getStatusCode()) {
                    throw $e;
                }
            }
        } else {
            foreach ($this->getService()->imageList() as $image) {
                $images[] = $image;
            }
        }

        return array_map(array($this, 'toImage'), $images);
    }


    /**
     * Get a server instance represented by the supplied Node, or an empty
     * server instance if a Node is not supplied.
     *
     * @param Node|null $node
     *
     * @return \OpenCloud\Compute\Resource\Server
     */
    protected function toServer(Node $node = null)
    {
        return $this->service->server($node ? $node->getId() : null);
    }


    /**
     * Get a flavor instance represented by the supplied NodeSize.
     *
     * @param NodeSize $nodeSize
     *
     * @return \OpenCloud\Compute\Resource\Flavor
     */
    protected function toFlavor(NodeSize $nodeSize)
    {
        return $this->service->flavor($nodeSize->getId());
    }


    /**
     * Convert OpenCloud\Compute\Resource\Server to
     * LibCloud\Compute\Model\Node.
     *
     * Note that it is the responsibility of calling methods to populate the
     * resulant Node with its NodeImage and NodeSize.
     *
     * @return \LibCloud\Compute\Model\Node
     */
    protected function toNode($response)
    {
        $extra = $this->toNodeModelExtra(
            $response,
            array('id', 'name', 'status')
        );

        $public_addr = array();
        $private_addr = array();
        if (isset($extra['addresses'])) {
            if (isset($extra['addresses']['public'])) {
                $public_addr = $extra['addresses']['public'];
            }
            if (isset($extra['addresses']['private'])) {
                $private_addr = $extra['addresses']['private'];
            }
            unset($extra['addresses']);
        }

        return new Node(
            $response->id,
            $response->name,
            $response->status,
            $public_addr,
            $private_addr,
            self::ID,
            null,
            null,
            $extra
        );
    }


    /**
     * Convert OpenCloud\Compute\Resource\Flavor to
     * LibCloud\Compute\Model\NodeSize.
     *
     * @return \LibCloud\Compute\Model\NodeSize
     */
    protected function toSize($response)
    {
        return new NodeSize(
            $response->id,
            $response->name,
            $response->ram,
            $response->disk,
            $response->rxtx_factor,
            null,
            self::ID,
            $this->toNodeModelExtra($response, array(
                'id', 'name', 'ram', 'disk', 'rxtx_factor'
            ))
        );
    }


    /**
     * Convert OpenCloud\Compute\Resource\Image to
     * LibCloud\Compute\Model\NodeImage.
     *
     * @return \LibCloud\Compute\Model\NodeImage
     */
    protected function toImage($response)
    {
        return new NodeImage(
            $response->id,
            $response->name,
            self::ID,
            $this->toNodeModelExtra($response, array('id', 'name'))
        );
    }


    /**
     * Convert array to LibCloud\Compute\Model\NodeLocation.
     *
     * @return \LibCloud\Compute\Model\NodeLocation
     */
    protected function toLocation($response)
    {
        return new NodeLocation(
            $response['id'],
            $response['name'],
            $response['co'],
            self::ID
        );
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


    /**
     * Populate an array, suitable for Compute\Model\Node*.extra, with the
     * public properties of an OpenCloud\Compute\Resource entity.
     *
     * @param object $response one of the OpenCloud\Compute\Resource entities
     * @param array|null $exclude an optional subset of the public properties to
     *                            be exluded from the returned array
     *
     * @return array
     */
    private function toNodeModelExtra($response, $exclude = null)
    {
        $extra = array();

        $rc = new \ReflectionClass($response);
        $public_props = array_map(
            function($x){return $x->name;},
            $rc->getProperties(\ReflectionProperty::IS_PUBLIC)
        );

        # desired properties are copied into the returned array
        $desired_properties = $public_props;
        if (is_array($exclude)) {
            $desired_properties = array_diff($public_props, $exclude);
        }

        # convert fields known to contain (possibly nested) stdClass objects
        $obj_properies = array_intersect(
            array('links', 'addresses', 'image', 'flavor'),
            $desired_properties
        );
        foreach ($obj_properies as $prop) {
            if (is_array($response->$prop)) {
                $extra[$prop] = array();
                foreach ($response->$prop as $k => $obj) {
                    $extra[$prop][$k] = $this->getObjectVarsRecursively($obj);
                }
            } else {
                $extra[$prop] = $this->getObjectVarsRecursively($response->$prop);
            }
            unset($desired_properties[array_search($prop, $desired_properties)]);
        }

        # convert metadata
        if (in_array('metadata', $desired_properties)) {
            if ($response->metadata instanceof OpenCloudMetadata) {
                $extra['metadata'] = $response->metadata->toArray();
            }
            unset($desired_properties[array_search('metadata', $desired_properties)]);
        }

        # get the rest of the desired properties
        foreach ($desired_properties as $propName) {
            $extra[$propName] = $response->$propName;
        }

        return $extra;
    }

    private function getObjectVarsRecursively($var)
    {
        $ret = null;
        if (is_scalar($var) || null === $var) {
            return $var;
        } else if (is_object($var)) {
            $ret = get_object_vars($var);
        } else {
            $ret = $var;
        }
        foreach ($ret as $k => $v) {
            $ret[$k] = $this->getObjectVarsRecursively($v);
        }
        return $ret;
    }
}
