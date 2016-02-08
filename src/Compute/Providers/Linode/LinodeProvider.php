<?php

namespace Linkorb\LibCloud\Compute\Providers\Linode;

use Hampel\Linode\Commands\AvailCommand;
use Hampel\Linode\Commands\LinodeIpCommand;
use Linkorb\LibCloud\Compute\Base;
use Linkorb\LibCloud\Compute\Model\Node;
use Linkorb\LibCloud\Compute\Model\NodeImage;
use Linkorb\LibCloud\Compute\Model\NodeLocation;
use Linkorb\LibCloud\Compute\Model\NodeSize;
use Linkorb\LibCloud\Compute\Model\NodeState;
use Linkorb\LibCloud\Compute\Providers\Linode\LinodeCommands\LinodeCommand;
use Symfony\Component\HttpFoundation\ParameterBag;
use Hampel\Linode\Linode;

class LinodeProvider extends Base
{
    private $linode;
    private $stateMap = [-2 => NodeState::UNKNOWN, -1 => NodeState::PENDING, 0 => NodeState::PENDING,
        1 => NodeState::RUNNING, 2 => NodeState::TERMINATED,
        3 => NodeState::REBOOTING, 4 => NodeState::UNKNOWN
    ];

    public function __construct($accessToken)
    {
        parent::__construct($accessToken);
        $this->linode = Linode::make($this->accessToken);
    }

    public function create_node(ParameterBag $parameters)
    {
        $options = [];
        try {
            $command = new LinodeCommand('create');
            $options['planid'] = $parameters->get('size')->getId();
            $options['datacenterid'] = $parameters->get('location')->getId();
            $parameters->get('paymentterm') ? $options['paymentterm'] = $parameters->get('paymentterm') : null;

            $command->setOptions($options);
            $response = $this->linode->execute($command)['LinodeID'];
            return $this->toNode($this->list_nodes($response));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function boot_node(Node $node)
    {
        try {
            return $this->linode->execute(new LinodeCommand('boot', [
                'linodeid' => $node->getId()
            ]));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function list_nodes($nodeId = null)
    {
        $args = $list = [];
        try {
            if ($nodeId)
            {
                $args = ['linodeid' => $nodeId];
            }
            return array_map([$this, 'toNode'], $this->linode->execute(new LinodeCommand('list', $args)));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function shutdown_node(Node $node)
    {
        try {
            return $this->linode->execute(new LinodeCommand('shutdown', [
                'linodeid' => $node->getId()
            ]));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function reboot_node(Node $node)
    {
        try {
            return $this->linode->execute(new LinodeCommand('reboot', [
                'linodeid' => $node->getId()
            ]));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function clone_node(Node $node, ParameterBag $parameters)
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
            return $this->toNode($this->list_nodes($response));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function destroy_node(Node $node)
    {
        try {
            return $this->linode->execute(new LinodeCommand('delete', [
                'linodeid' => $node->getId()
            ]));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function resize_node(Node $node, NodeSize $nodeSize)
    {
        try {
            return $this->linode->execute(new LinodeCommand('resize', [
                'linodeid' => $node->getId(), 'planid' => $nodeSize->getId()
            ]));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function update_node(Node $node, ParameterBag $parameters)
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

    public function list_sizes($nodeSizeId = null)
    {
        $args = [];
        try {
            if ($nodeSizeId)
            {
                 $args = ['planid' => $nodeSizeId];
            }
            return array_map([$this, 'toSize'], $this->linode->execute(new AvailCommand('linodeplans', $args)));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function list_locations($nodeLocationId = null)
    {
        $args = [];
        try {
            if ($nodeLocationId)
            {
                $args = ['datacenterid' => $nodeLocationId];
            }
            return array_map([$this, 'toLocation'], $this->linode->execute(new AvailCommand('datacenters', $args)));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function list_images($nodeImageId = null)
    {
        $args = [];
        try {
            if ($nodeImageId)
            {
                $args = ['distributionid' => $nodeImageId];
            }
            return array_map([$this, 'toImage'], $this->linode->execute(new AvailCommand('distributions', $args)));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function list_ips($linodeId)
    {
        $args = $list = [];
        try {
            if ($linodeId)
            {
                $args = ['linodeid' => $linodeId];
            }
            $response = $this->linode->execute(new LinodeIpCommand('list', $args));
            foreach ($response as $item)
            {
                $list[] = ['nodeId' => $item['LINODEID'], 'public' => $item['ISPUBLIC'], 'ipAddress' => $item['IPADDRESS']];
            }
            return $list;
        } catch (\Exception $e) {
            throw $e;
        }
    }


    protected function toNode($response)
    {
        $public_ips = $private_ips = $extra = [];

        $ips = $this->list_ips($response['LINODEID']);
        foreach ($ips as $ip)
        {
            if ($ip['public'])
            {
                $public_ips[] = $ip['ipAddress'];
            }
            else
            {
                $private_ips[] = $ip['ipAddress'];
            }
        }

        $size = $this->list_sizes($response['PLANID']);
        $image = new NodeImage(null, null, 'linode');

        return new Node($response['LINODEID'], $response['LABEL'], $this->stateMap[$response['STATUS']], $public_ips,
            $private_ips, 'linode', $size, $image, ['distributionvendor' => $response['DISTRIBUTIONVENDOR']]);
    }

    protected function toSize($response)
    {
        return new NodeSize($response['PLANID'], $response['LABEL'], $response['RAM'], $response['DISK'], $response['XFER'],
            $response['HOURLY'], 'linode', ['priceMonthly' => $response['PRICE']]);
    }

    protected function toImage($response)
    {
        return new NodeImage($response['DISTRIBUTIONID'], $response['LABEL'], 'linode');
    }

    protected function toLocation($response)
    {
        return new NodeLocation($response['DATACENTERID'], $response['LOCATION'], null, 'linode');
    }
}
