<?php

namespace LibCloud\Test\Compute\Providers\Rackspace;

use LibCloud\Compute\ComputeFactory;

use OpenCloud\Tests\OpenCloudTestCase;

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

    public function testNothing()
    {
        $this->markTestIncomplete('This test is not yet implemented.');
    }
}
