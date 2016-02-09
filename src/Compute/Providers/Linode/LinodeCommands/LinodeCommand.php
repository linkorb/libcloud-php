<?php

namespace LibCloud\Compute\Providers\Linode\LinodeCommands;

use Hampel\Linode\Commands\LinodeCommand as HampelLinodeCommand;

class LinodeCommand extends HampelLinodeCommand
{
    protected $allowed_parameters = [
        'linodeid', // numeric
        'configid', // numeric
        'datacenterid', // required - The DatacenterID from avail.datacenters() where you wish to place this new Linode
        'planid', // required - The desired PlanID available from avail.LinodePlans()
        'paymentterm', // optional - One of: 1, 12, or 24

        // update method parameters
        'label', // string (optional) This Linode's label
        'lpm_displayGroup', //string (optional) Display group in the Linode list inside the Linode Manager
        'Alert_cpu_enabled', // - boolean (optional) Enable the cpu usage email alert
        'Alert_cpu_threshold', // numeric (optional) CPU Alert threshold, percentage 0-800
        'Alert_diskio_enabled', //boolean (optional) Enable the disk IO email alert
        'Alert_diskio_threshold', // numeric (optional) IO ops/sec
        'Alert_bwin_enabled', // boolean (optional) Enable the incoming bandwidth email alert
        'Alert_bwin_threshold', // numeric (optional) Mb/sec
        'Alert_bwout_enabled', // boolean (optional) Enable the outgoing bandwidth email alert
        'Alert_bwout_threshold', // numeric (optional) Mb/sec
        'Alert_bwquota_enabled', // boolean (optional) Enable the bw quote email alert
        'Alert_bwquota_threshold', // numeric (optional) Percentage of monthly bw quota
        'backupWindow', // numeric (optional) backupWeeklyDay - numeric (optional)
        'watchdog', // boolean (optional) Enable the Lassie shutdown watchdog
        'ms_ssh_disabled', // boolean (optional) ms_ssh_user - string (optional)
        'ms_ssh_ip', // string (optional) ms_ssh_port - numeric (optional)
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
