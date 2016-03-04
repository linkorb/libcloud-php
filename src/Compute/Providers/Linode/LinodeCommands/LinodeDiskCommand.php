<?php

namespace LibCloud\Compute\Providers\Linode\LinodeCommands;

use Hampel\Linode\Commands\LinodeDiskCommand as HampelLinodeDiskCommand;

class LinodeDiskCommand extends HampelLinodeDiskCommand
{
    protected $allowed_parameters = [
        'linodeid', // numeric
        'datacenterid', // required - The DatacenterID from avail.datacenters() where you wish to place this new Linode
        ''
    ];

    public function getAllowedParameters()
    {
        return $this->allowed_parameters;
    }

    public function setOptions($options)
    {
        $this->options = $options;
    }
}
