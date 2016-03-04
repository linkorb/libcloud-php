<?php

namespace LibCloud\Compute\Providers\Linode;

use Hampel\Linode\Commands\AvailCommand;
use LibCloud\Compute\Base;
use LibCloud\Compute\Model\Node;
use LibCloud\Compute\Model\NodeImage;
use LibCloud\Compute\Model\NodeLocation;
use LibCloud\Compute\Model\NodeSize;
use LibCloud\Compute\Model\NodeState;
use LibCloud\Compute\Providers\Linode\LinodeCommands\LinodeCommand;
use LibCloud\Compute\Providers\Linode\LinodeCommands\LinodeDiskCommand;
use LibCloud\Compute\Providers\Linode\LinodeCommands\LinodeConfigCommand;
use LibCloud\Compute\Providers\Linode\LinodeCommands\LinodeIpCommand;
use Symfony\Component\HttpFoundation\ParameterBag;
use Hampel\Linode\Linode;

class LinodeProvider extends Base
{
    private $linode;
    private $stateMap = [
        -2 => NodeState::UNKNOWN,
        -1 => NodeState::PENDING,
        0 => NodeState::PENDING,
        1 => NodeState::RUNNING,
        2 => NodeState::TERMINATED,
        3 => NodeState::REBOOTING,
        4 => NodeState::UNKNOWN
    ];

    public function __construct($accessToken)
    {
        parent::__construct($accessToken);
        $this->linode = Linode::make($this->accessToken);
    }

    public function createNode(ParameterBag $parameters)
    {
        $options = [];
        try {
            $command = new LinodeCommand('create');
            $options['planid'] = $parameters->get('size')->getId();
            $options['datacenterid'] = $parameters->get('location')->getId();
            $parameters->get('paymentterm') ? $options['paymentterm'] = $parameters->get('paymentterm') : null;

            $command->setOptions($options);
            $response = $this->linode->execute($command)['LinodeID'];
            return $this->toNode($this->listNodes($response));
        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    public function provisionNode(ParameterBag $parameters)
    {
        $options = [];
        
        $label = $parameters->get('label');
        $stackScriptId = $parameters->get('stackscriptid');
        $distributionId = $parameters->get('distributionid');
        $diskSize = $parameters->get('disksize');
        $swapSize = $parameters->get('swapsize');
        $rootPassword = $parameters->get('rootpassword');
        $stackScriptUDFResponses = $parameters->get('StackScriptUDFResponses');

        // ==================
        $command = new LinodeCommand('create');
        $options['planid'] = $parameters->get('size')->getId();
        $options['datacenterid'] = $parameters->get('location')->getId();
        $parameters->get('paymentterm') ? $options['paymentterm'] = $parameters->get('paymentterm') : null;

        $command->setOptions($options);
        $response = $this->linode->execute($command);
        $nodeId = $response['LinodeID'];
        
        // ================
        $command = new LinodeDiskCommand('createfromstackscript');
        $options['linodeid'] = $nodeId;
        $options['StackScriptID'] = $stackScriptId;
        $options['DistributionID'] = $distributionId;
        $options['Label'] = "OS " . $label;
        $options['Size'] = $diskSize;
        $options['RootPass'] = $rootPassword;
        $options['StackScriptUDFResponses'] = $stackScriptUDFResponses;
        
        $command->setOptions($options);
        $response = $this->linode->execute($command);
        $osDiskId = $response['DiskID'];

        // ==========
        $command = new LinodeDiskCommand('create');
        $options['linodeid'] = $nodeId;
        $options['Label'] = "SWAP " . $label;
        $options['Size'] = $swapSize;
        $options['Type'] = 'swap';

        $command->setOptions($options);
        $response = $this->linode->execute($command);
        //print_r($response);
        $swapDiskId = $response['DiskID'];

        // ==========
        $command = new LinodeConfigCommand('create');
        $options['linodeid'] = $nodeId;
        $options['KernelID'] = 138;
        $options['Label'] = "Config " . $label;
        $options['DiskList'] = $osDiskId . ',' . $swapDiskId;

        $command->setOptions($options);
        $response = $this->linode->execute($command);
        $configId = $response['ConfigID'];

        // ==========
        $command = new LinodeIpCommand('addprivate');
        $options['linodeid'] = $nodeId;

        $command->setOptions($options);
        $response = $this->linode->execute($command);
        $privateIpId = $response['IPADDRESSID'];
        $privateIpAddress = $response['IPADDRESS'];

        $command = new LinodeCommand('update');
        $options['linodeid'] = $nodeId;
        $options['Label'] = str_replace(' ', '_', $label);
        
        $command->setOptions($options);
        $response = $this->linode->execute($command);

        
    }

    public function bootNode(Node $node)
    {
        try {
            return $this->linode->execute(new LinodeCommand('boot', [
                'linodeid' => $node->getId()
            ]));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function listNodes($nodeId = null)
    {
        $args = $list = [];
        try {
            if ($nodeId) {
                $args = ['linodeid' => $nodeId];
            }
            $list = array_map([$this, 'toNode'], $this->linode->execute(new LinodeCommand('list', $args)));
            if ($nodeId) {
                return $list[0];
            }
            return $list;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function shutdownNode(Node $node)
    {
        try {
            return $this->linode->execute(new LinodeCommand('shutdown', [
                'linodeid' => $node->getId()
            ]));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function rebootNode(Node $node)
    {
        try {
            return $this->linode->execute(new LinodeCommand('reboot', [
                'linodeid' => $node->getId()
            ]));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function cloneNode(Node $node, ParameterBag $parameters)
    {
        $options = [];
        try {
            $command = new LinodeCommand('clone');
            $options['linodeid'] = $node->getId();
            $options['planid'] = $parameters->get('size')->getId();
            $options['datacenterid'] = $parameters->get('location')->getId();
            $parameters->get('paymentterm') ? $options['paymentterm'] = $parameters->get('paymentterm') : null;

            $command->setOptions($options);
            $response = $this->linode->execute($command)['LinodeID'];
            return $this->toNode($this->listNodes($response));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function destroyNode(Node $node)
    {
        try {
            return $this->linode->execute(new LinodeCommand('delete', [
                'linodeid' => $node->getId()
            ]));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function resizeNode(Node $node, NodeSize $nodeSize)
    {
        try {
            return $this->linode->execute(new LinodeCommand('resize', [
                'linodeid' => $node->getId(), 'planid' => $nodeSize->getId()
            ]));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function updateNode(Node $node, ParameterBag $parameters)
    {
        try {
            $command = new LinodeCommand('update');
            $options = ['linodeid' => $node->getId()];
            foreach ($command->getAllowedParameters() as $parameter)
            {
                $parameters->get($parameter) ? $options[$parameter] = $parameters->get($parameter) : null;
            }
            $command->setOptions($options);
            return $this->linode->execute($command);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function listSizes($nodeSizeId = null)
    {
        $args = [];
        try {
            if ($nodeSizeId) {
                 $args = ['planid' => $nodeSizeId];
            }
            return array_map([$this, 'toSize'], $this->linode->execute(new AvailCommand('linodeplans', $args)));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function listLocations($nodeLocationId = null)
    {
        $args = [];
        try {
            if ($nodeLocationId) {
                $args = ['datacenterid' => $nodeLocationId];
            }
            return array_map(
                [$this, 'toLocation'],
                $this->linode->execute(
                    new AvailCommand('datacenters', $args)
                )
            );
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function listImages($nodeImageId = null)
    {
        $args = [];
        try {
            if ($nodeImageId) {
                $args = ['distributionid' => $nodeImageId];
            }
            return array_map(
                [$this, 'toImage'],
                $this->linode->execute(
                    new AvailCommand('distributions', $args)
                )
            );
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function listIps($linodeId)
    {
        $args = $list = [];
        try {
            if ($linodeId) {
                $args = ['linodeid' => $linodeId];
            }
            $response = $this->linode->execute(new LinodeIpCommand('list', $args));
            foreach ($response as $item) {
                $list[] = [
                    'nodeId' => $item['LINODEID'],
                    'public' => $item['ISPUBLIC'],
                    'ipAddress' => $item['IPADDRESS']
                ];
            }
            return $list;
        } catch (\Exception $e) {
            throw $e;
        }
    }


    protected function toNode($response)
    {
        //print_r($response);
        $public_ips = $private_ips = $extra = [];

        $ips = $this->listIps($response->getId());
        foreach ($ips as $ip) {
            if ($ip['public']) {
                $public_ips[] = $ip['ipAddress'];
            } else {
                $private_ips[] = $ip['ipAddress'];
            }
        }

        $size = $this->listSizes($response['PLANID']);
        $image = new NodeImage(null, null, 'linode');

        return new Node(
            $response['LINODEID'],
            $response['LABEL'],
            NodeState::toString($this->stateMap[$response['STATUS']]),
            $public_ips,
            $private_ips,
            'linode',
            $size,
            $image,
            ['distributionvendor' => $response['DISTRIBUTIONVENDOR']]
        );
    }

    protected function toSize($response)
    {
        return new NodeSize(
            $response['PLANID'],
            $response['LABEL'],
            $response['RAM'],
            $response['DISK'],
            $response['XFER'],
            $response['HOURLY'],
            'linode',
            ['priceMonthly' => $response['PRICE']]
        );
    }

    protected function toImage($response)
    {
        return new NodeImage(
            $response['DISTRIBUTIONID'],
            $response['LABEL'],
            'linode'
        );
    }

    protected function toLocation($response)
    {
        return new NodeLocation(
            $response['DATACENTERID'],
            $response['LOCATION'],
            null,
            'linode'
        );
    }
}
