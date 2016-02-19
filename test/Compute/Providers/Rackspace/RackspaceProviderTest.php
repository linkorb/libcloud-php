<?php

namespace LibCloud\Test\Compute\Providers\Rackspace;

use LibCloud\Compute\ComputeFactory;
use LibCloud\Compute\Model\Node;

use OpenCloud\Tests\OpenCloudTestCase;

use Symfony\Component\HttpFoundation\ParameterBag;

class RackspaceProviderTest extends OpenCloudTestCase
{
    /**
     * @var \LibCloud\Compute\Providers\Rackspace\RackspaceProvider
     */
    protected $provider;

    /**
     * Location of the _response dir, relative to ROOT_TEST_DIR
     * @var string
     */
    protected $mockPath = 'Compute/Providers/Rackspace';

    public function setUp()
    {
        $this->provider = ComputeFactory::getProvider(
            'rackspace',
            'testuser:some_api_key'
        );

        # parent test case needs this before addMockSubscriber is called
        $this->client = $this->provider->getClient();

        # initial call to getService will perform authentication
        $this->addMockSubscriber($this->getTestFilePath('Auth'));
        $this->addMockSubscriber($this->getTestFilePath('Extensions'));
        $this->provider->getService();
    }

    public function tearDown()
    {
        $this->client = null;
        $this->provider = null;
    }

    protected function addMockSubscriber($response)
    {
        $this
            ->getClient()
            ->addSubscriber(new MockSubscriber(array($response), true))
        ;
    }

    public function testListImages()
    {
        $this->addMockSubscriber($this->getTestFilePath('Image_List'));

        $images = $this->provider->listImages();
        $image = $images[0];

        $this->assertInstanceOf(
            '\LibCloud\Compute\Model\NodeImage',
            $image,
            'listImages returns an array of NodeImage instances'
        );

        $this->assertSame(
            '28555e09-5639-43f9-b64b-9c98c78520ad',
            $image->getId(),
            'The NodeImage ID was correctly set'
        );

        $this->assertSame(
            'CoreOS (Alpha)',
            $image->getName(),
            'The NodeImage Name was correctly set'
        );

        $this->assertEquals(
            array(
                'created' => "2015-07-09T14:48:20Z",
                'minDisk' => 20,
                'minRam' => 512,
                'progress' => 100,
                'server' => null, # untested
                'status' => "ACTIVE",
                'updated' => "2015-07-09T16:12:57Z",
                'links' => array(
                    array(
                        'href' => "https://dfw.servers.api.rackspacecloud.com/v2/00000000/images/28555e09-5639-43f9-b64b-9c98c78520ad",
                        'rel' => "self"
                    ),
                    array(
                        'href' => "https://dfw.servers.api.rackspacecloud.com/00000000/images/28555e09-5639-43f9-b64b-9c98c78520ad",
                        'rel' => "bookmark"
                    ),
                    array(
                        'href' => "https://dfw.servers.api.rackspacecloud.com/images/28555e09-5639-43f9-b64b-9c98c78520ad",
                        'type' => "application/vnd.openstack.image",
                        'rel' => "alternate"
                    )
                ),
                'metadata' => array(
                    'os_distro' => "com.coreos",
                    'os_type' => "linux"
                )
            ),
            $image->getExtra(),
            'The NodeImage Extra info is a dictionary of image details'
        );

        return $images;
    }

    public function testListImagesWithUnknownId()
    {
        $this->addMockSubscriber($this->getTestFilePath('Image_None'));

        $this->assertCount(
            0,
            $this->provider->listImages('bogus-id'),
            'listImages returns an empty array because "bogus-id" is not a valid image ID'
        );
    }

    public function testListImagesWithId()
    {
        $this->addMockSubscriber($this->getTestFilePath('Image'));

        $expectedId = 'df27d481-63a5-40ca-8920-3d132ed643d9';

        $images = $this->provider->listImages($expectedId);

        $this->assertCount(
            1,
            $images,
            'listImages returns an array with exactly one item'
        );

        $this->assertSame(
            $expectedId,
            $images[0]->getId(),
            'The NodeImage ID matches the ID that was requested'
        );
    }

    public function testListLocations()
    {
        $images = $this->provider->listLocations();

        $this->assertInstanceOf(
            '\LibCloud\Compute\Model\NodeLocation',
            $images[0],
            'listLocations returns an array of NodeLocation instances'
        );

        $this->assertSame(
            'DFW',
            $images[0]->getId(),
            'The NodeLocation ID was correctly set'
        );

        $this->assertSame(
            'Dallas-Fort Worth, TX',
            $images[0]->getName(),
            'The NodeLocation Name was correctly set'
        );

        $this->assertSame(
            'USA',
            $images[0]->getCountry(),
            'The NodeLocation Country was correctly set'
        );
    }

    public function testListLocationsWithUnknownId()
    {
        $this->assertCount(
            0,
            $this->provider->listLocations('ZED'),
            'listLocations returns an empty array because "ZED" is not a valid location ID'
        );
    }

    public function testListLocationsWithId()
    {
        $expectedId = 'HKG';

        $images = $this->provider->listLocations($expectedId);

        $this->assertCount(
            1,
            $images,
            'listLocations returns an array with exactly one item'
        );

        $this->assertSame(
            $expectedId,
            $images[0]->getId(),
            'The NodeLocation ID matches the ID that was requested'
        );
    }

    public function testListSizes()
    {
        $this->addMockSubscriber($this->getTestFilePath('Flavor_List'));

        $sizes = $this->provider->listSizes();
        $size = $sizes[7];

        $this->assertInstanceOf(
            '\LibCloud\Compute\Model\NodeSize',
            $size,
            'listSizes returns an array of NodeSize instances'
        );

        $this->assertSame(
            'compute1-15',
            $size->getId(),
            'The NodeSize ID was correctly set'
        );

        $this->assertSame(
            '15 GB Compute v1',
            $size->getName(),
            'The NodeSize Name was correctly set'
        );

        $this->assertSame(
            15360,
            $size->getRam(),
            'The NodeSize RAM was correctly set'
        );

        return $sizes;
    }

    public function testListSizesWithUnknownId()
    {
        $this->addMockSubscriber($this->getTestFilePath('Flavor_None'));

        $this->assertCount(
            0,
            $this->provider->listSizes('ZED'),
            'listSizes returns an empty array because "ZED" is not a valid size ID'
        );
    }

    public function testListSizesWithId()
    {
        $this->addMockSubscriber($this->getTestFilePath('Flavor'));

        $expectedId = 'compute1-15';

        $sizes = $this->provider->listSizes($expectedId);

        $this->assertCount(
            1,
            $sizes,
            'listSizes returns an array with exactly one item'
        );

        $this->assertSame(
            $expectedId,
            $sizes[0]->getId(),
            'The NodeSize ID matches the ID that was requested'
        );
    }

    public function testListNodes()
    {
        $this->addMockSubscriber($this->getTestFilePath('Server_List'));
        $this->addMockSubscriber($this->getTestFilePath('Server_Meta'));
        # Another Server_Meta response is required here.
        # Why?  Because:-
        # 1) RackspaceProvider.listNodes calls Service->serverList which fetches
        #    a list of servers from the API and returns PaginatedIterator (which
        #    extends ResourceIterator)
        # 2) RackspaceProvider.listNodes then iterates PaginatedIterator
        # 3) ResourceIterator->constructResource() is called N+1 times during
        #    iteration (where N is count of items to iterate)
        # 4) constructResource calls Service->server with stdClass
        # 5) Service->server calls Server->__construct(stdClass) to populate the
        #    Server with stdClass properties
        # 6) Server->__construct then makes an API request for ServerMetadata
        #
        # The bug is definitely 3), but I reckon metadata should not be fetched
        # in 6) if it already existed in 1)
        $this->addMockSubscriber($this->getTestFilePath('Server_Meta'));

        $nodes = $this->provider->listNodes();
        $node = $nodes[0];

        $this->assertInstanceOf(
            '\LibCloud\Compute\Model\Node',
            $node,
            'listNodes returns an array of Node instances'
        );

        $this->assertSame(
            '2e1a538c-5238-4bd8-af7d-3727ef06d7b2',
            $node->getId(),
            'The Node ID was correctly set'
        );

        $this->assertSame(
            'test-2-saucy',
            $node->getName(),
            'The Node Name was correctly set'
        );

        $this->assertEquals(
            array(
                array(
                    'version' => 4,
                    'addr' => '203.0.113.41'
                ),
                array(
                    'version' => 6,
                    'addr' => '2001:DB8:1e76:4eff:ae10:7801:fe08:c61'
                ),
            ),
            $node->getPublicIps(),
            'The Node Public IP addresses were correctly set'
        );

        $this->assertEquals(
            array(
                array(
                    'version' => 4,
                    'addr' => '192.0.2.104'
                ),
            ),
            $node->getPrivateIps(),
            'The Node Private IP addresses were correctly set'
        );

        $this->assertEquals(
            array(
                'updated' => '2016-02-17T11:17:35Z',
                'hostId' => 'a84cf03b27075fd56bc308ade42030cbdb257e3c6b0761c6f520389c',
                'links' => array(
                    array(
                        'href' => 'https://lon.servers.api.rackspacecloud.com/v2/00000000/servers/2e1a538c-5238-4bd8-af7d-3727ef06d7b2',
                        'rel' => 'self',
                    ),
                    array(
                        'href' => 'https://lon.servers.api.rackspacecloud.com/00000000/servers/2e1a538c-5238-4bd8-af7d-3727ef06d7b2',
                        'rel' => 'bookmark',
                    ),
                ),
                'image' => array(
                    'id' => 'df27d481-63a5-40ca-8920-3d132ed643d9',
                    'links' => array(
                        array(
                            'href' => 'https://lon.servers.api.rackspacecloud.com/00000000/images/df27d481-63a5-40ca-8920-3d132ed643d9',
                            'rel' => 'bookmark',
                        ),
                    )
                ),
                'volume' => null,
                'volumeDeleteOnTermination' => null,
                'flavor' => array(
                    'id' => '2',
                    'links' => array(
                        array(
                            'href' => 'https://lon.servers.api.rackspacecloud.com/00000000/flavors/2',
                            'rel' => 'bookmark',
                        ),
                    )
                ),
                'networks' => array(),
                'security_groups' => Array (),
                'user_id' => 'a931a436ed1045faac0eb4c7186b2282',
                'created' => '2016-02-17T11:15:49Z',
                'tenant_id' => '00000000',
                'accessIPv4' => '203.0.113.41',
                'accessIPv6' => '2001:DB8:1e76:4eff:ae10:7801:fe08:c61',
                'progress' => 100,
                'adminPass' => null,
                'metadata' => array(
                    'rax_service_level_automation' => 'Complete',
                    'sdk' => 'OpenCloud/1.14.2 Guzzle/3.9.3 cURL/7.35.0 PHP/5.5.9-1ubuntu4.14',
                ),
                'extendedStatus' => 'active',
                'taskStatus' => null,
                'powerStatus' => 1,
                'availabilityZone' => null,
                'keypair' => null,
                'user_data' => null,
            ),
            $node->getExtra(),
            'The Node extra info was correctly set'
        );

        return $nodes;
    }

    public function testListNodesWithUnknownId()
    {
        $this->addMockSubscriber($this->getTestFilePath('Server_None'));

        $this->assertCount(
            0,
            $this->provider->listNodes('not-a-server-id'),
            'listNodes returns an empty array because "not-a-server-id" is not a valid node ID'
        );
    }

    public function testListNodesWithId()
    {
        $this->addMockSubscriber($this->getTestFilePath('Server'));
        $this->addMockSubscriber($this->getTestFilePath('Server_Meta'));

        $expectedId = '2e1a538c-5238-4bd8-af7d-3727ef06d7b2';

        $nodes = $this->provider->listNodes($expectedId);
        $node = $nodes[0];

        $this->assertCount(
            1,
            $nodes,
            'listNodes returns an array with exactly one item'
        );

        $this->assertSame(
            $expectedId,
            $node->getId(),
            'The Node ID matches the ID that was requested'
        );

        return $node;
    }

    /**
     * @depends testListNodesWithId
     */
    public function testRebootNode($node)
    {
        $this->addMockSubscriber($this->getTestFilePath('Server'));
        $this->addMockSubscriber($this->getTestFilePath('Server_Meta'));
        $this->addMockSubscriber($this->getTestFilePath('Server_Reboot'));

        $r = $this->provider->rebootNode($node);

        $this->assertSame(
            202,
            $r->getStatusCode(),
            'rebootNode returns the API response for a successful reboot'
        );
    }

    /**
     * @depends testListNodesWithId
     */
    public function testBootNode($node)
    {
        $this->addMockSubscriber($this->getTestFilePath('Server'));
        $this->addMockSubscriber($this->getTestFilePath('Server_Meta'));

        $this->setExpectedException(
            'OpenCloud\Common\Exceptions\UnsupportedExtensionError',
            'os-server-start-stop extension is not installed'
        );

        $this->provider->bootNode($node);
    }

    /**
     * @depends testListNodesWithId
     */
    public function testShutdownNode($node)
    {
        $this->addMockSubscriber($this->getTestFilePath('Server'));
        $this->addMockSubscriber($this->getTestFilePath('Server_Meta'));

        $this->setExpectedException(
            'OpenCloud\Common\Exceptions\UnsupportedExtensionError',
            'os-server-start-stop extension is not installed'
        );

        $this->provider->shutdownNode($node);
    }

    /**
     * @depends testListNodesWithId
     */
    public function testDestroyNode($node)
    {
        $this->addMockSubscriber($this->getTestFilePath('Server'));
        $this->addMockSubscriber($this->getTestFilePath('Server_Meta'));
        $this->addMockSubscriber($this->getTestFilePath('Server_Delete'));

        $r = $this->provider->destroyNode($node);

        $this->assertSame(
            204,
            $r->getStatusCode(),
            'destroyNode returns the API response for a successful reboot'
        );
    }

    /**
     * @depends testListSizes
     * @depends testListImages
     */
    public function testCreateNode($sizes, $images)
    {
        $this->addMockSubscriber($this->getTestFilePath('Server_Create'));
        $this->addMockSubscriber($this->getTestFilePath('Server'));
        $this->addMockSubscriber($this->getTestFilePath('Server_Meta'));

        $expectedCreatedNodeId = '2e1a538c-5238-4bd8-af7d-3727ef06d7b2';

        $parameters = new ParameterBag(array(
            'name' => 'test-2-saucy',
            'size' => $sizes[0],
            'image' => $images[0],
        ));

        $node = $this->provider->createNode($parameters);

        $this->assertInstanceOf(
            Node::class,
            $node,
            'createNode returns an instance of a Node'
        );

        $this->assertSame(
            $expectedCreatedNodeId,
            $node->getId(),
            'createNode successfully created a Node'
        );

        $this->assertSame(
            $parameters->get('size'),
            $node->getSize(),
            'The Node was created with the requested size'
        );

        $this->assertSame(
            $parameters->get('image'),
            $node->getImage(),
            'The Node was created with the requested image'
        );
    }

    /**
     * @depends testListNodesWithId
     * @depends testListSizes
     */
    public function testResizeNode($node, $sizes)
    {
        $this->addMockSubscriber($this->getTestFilePath('Server'));
        $this->addMockSubscriber($this->getTestFilePath('Server_Meta'));
        $this->addMockSubscriber($this->getTestFilePath('Flavor_ForResize'));
        $this->addMockSubscriber($this->getTestFilePath('Server_Resize'));

        $r = $this->provider->resizeNode($node, $sizes[1]);

        $this->assertSame(
            202,
            $r->getStatusCode(),
            'resizeNode returns the API response for the successful initiation of a resize operation'
        );
    }

    /**
     * @depends testListNodesWithId
     */
    public function testUpdateNodeWithZeroProperties($node)
    {
        $this->setExpectedException('UnexpectedValueException');
        $this->provider->updateNode($node, new ParameterBag());
    }

    /**
     * @depends testListNodesWithId
     */
    public function testUpdateNodeWithProhibitedProperties($node)
    {
        $this->setExpectedException('UnexpectedValueException');

        $this->provider->updateNode(
            $node,
            new ParameterBag(array(
                'name' => 'test-2-sauuucy',
                'accessIPv4' => '203.0.113.187',
                'accessIPv6' => '2001:DB8:dead:bea7:dead:900d:dead:1337',
                'adminPass' => 'sneaky-pa55w0rd',
            ))
        );
    }

    /**
     * @depends testListNodesWithId
     */
    public function testUpdateNodeWithAllowedProperties($node)
    {
        $this->addMockSubscriber($this->getTestFilePath('Server'));
        $this->addMockSubscriber($this->getTestFilePath('Server_Meta'));
        $this->addMockSubscriber($this->getTestFilePath('Server_Update'));
        $this->addMockSubscriber($this->getTestFilePath('Server_Update'));
        $this->addMockSubscriber($this->getTestFilePath('Server_Meta'));

        $r = $this->provider->updateNode(
            $node,
            new ParameterBag(array(
                'name' => 'test-2-sauuucy',
                'accessIPv4' => '203.0.113.187',
                'accessIPv6' => '2001:DB8:dead:bea7:dead:900d:dead:1337',
            ))
        );

        $this->assertSame(
            200,
            $r->getStatusCode(),
            'updateNode returns the API response for the successful update operation'
        );

        $properties = json_decode($r->getBody(true));

        $this->assertSame(
            'test-2-sauuucy',
            $node->getName(),
            'updateNode successfully updated the Node name'
        );
        $this->assertEquals(
            array(
                array(
                    'version' => 4,
                    'addr' => '203.0.113.187'
                ),
                array(
                    'version' => 6,
                    'addr' => '2001:DB8:dead:bea7:dead:900d:dead:1337'
                ),
            ),
            $node->getPublicIps(),
            'updateNode successfully updated the Node publicIps'
        );
    }
}
