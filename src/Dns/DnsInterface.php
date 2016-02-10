<?php

namespace LibCloud\Dns;

use LibCloud\Dns\Model\Zone;
use LibCloud\Dns\Model\Record;
use Symfony\Component\HttpFoundation\ParameterBag;

interface DnsInterface
{
    public function listZones();
    public function listRecords(Zone $zone);
    public function createRecord(Zone $zone, ParameterBag $parameters);
    public function getZone($zoneDomain = null);
    public function updateRecord(Record $record, ParameterBag $parameters);
    public function getRecord(Zone $zone, $recordId);
}
