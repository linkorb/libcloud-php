<?php

namespace LibCloud\Compute\Providers\Linode\LinodeCommands;

use Hampel\Linode\Commands\LinodeConfigCommand as HampelLinodeConfigCommand;

class LinodeConfigCommand extends HampelLinodeConfigCommand
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
