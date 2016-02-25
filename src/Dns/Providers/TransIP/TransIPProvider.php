<?php

namespace LibCloud\Dns\Providers\TransIP;

use LibCloud\Dns\Base;
use LibCloud\Dns\Model\Record;
use LibCloud\Dns\Model\RecordType;
use LibCloud\Dns\Model\Zone;
use Symfony\Component\HttpFoundation\ParameterBag;
//use GuzzleHttp\Client;
use LibCloud\Dns\DnsInterface ;

use Transip_ApiSettings ;
use Transip_DomainService ;
use Transip_DnsEntry ;


class TransIPProvider extends Base implements DnsInterface 
{
    public function __construct($accessToken, $accessTokenSecret)
    {
        //echo "Set TransIP login\n" ;
        Transip_ApiSettings::$login = $accessToken ;
        Transip_ApiSettings::$privateKey = $accessTokenSecret ;
    }
    
    public function listZones()
    {
        $res = array() ;
        $domainList = Transip_DomainService::getDomainNames();
        foreach ($domainList as $k => $v) {
            //$r2 = Transip_DomainService::getInfo($v) ;
            //print_r($r2) ;
            // Small cheat: we don't know true domainId, use array index
            $res[] = $this->toZone(["domainId" => $k, "domain" => $v]) ;
        }
        return $res ;
    }
    
    public function listRecords(Zone $zone)
    {
        $res = false ;
        $info = Transip_DomainService::getInfo($zone->getDomain());
        $res = array() ;
        foreach ($info->dnsEntries as $k => $v) {
            $res[] = $this->toRecord($v, $zone);
        }
        return $res ;
    }
    
    public function createRecord(Zone $zone, ParameterBag $parameters)
    {
        $res = false ;
        $domain = $zone->getDomain() ;
        $info = Transip_DomainService::getInfo($domain);
        $recList = $info->dnsEntries ;
        $tmpRec = $this->formatRecord($parameters);
        $recList[] = new Transip_DnsEntry($tmpRec['name'], $tmpRec['ttl'], $tmpRec['type'], $tmpRec['data']) ;
        Transip_DomainService::setDnsEntries($domain, $recList) ;
        $res = true ;
        return $res ;
    }

    public function updateRecord(Record $record, ParameterBag $parameters)
    {
        $res = false ;
        $domain = $record->getZone()->getDomain() ;
        $info = Transip_DomainService::getInfo($domain);
        $recList = $info->dnsEntries ;
        $tmpRec = $this->formatRecord($parameters);
        $need_add = true ;
        foreach ($recList as &$pv) {
            if (($pv->name == $tmpRec['name']) && ($pv->type == $tmpRec['type'])) {
                if(isset($tmpRec['ttl']) && $tmpRec['ttl'] > 0) {
                    $pv->expire = $tmpRec['ttl'] ;
                }
                if(isset($tmpRec['data']) && strlen($tmpRec['data']) > 0) {
                    $pv->content = $tmpRec['data'] ;
                }
                $need_add = false ;
                break ;
            }
        }
        unset($pv) ;
        if ($need_add) {
            $recList[] = new Transip_DnsEntry($tmpRec['name'], $tmpRec['ttl'], $tmpRec['type'], $tmpRec['data']) ;
        }
        Transip_DomainService::setDnsEntries($domain, $recList) ;
        $res = true ;
        return $res ;
    }

    public function getZone($zoneDomain = null)
    {
        if (!is_null($zoneDomain)) {
            $zoneDomain = strtolower($zoneDomain) ;
            $domainList = Transip_DomainService::getDomainNames();
            foreach ($domainList as $k => $v) {
                if (strtolower($v) == $zoneDomain) {
                    return $this->toZone(["domainId" => $k, "domain" => $v]) ;
                }
            }
        }
        return false ;
    }

    public function getRecord(Zone $zone, $recordId)
    {
        $records = $this->listRecords($zone) ;
        foreach ($records as $k => $v) {
            if($recordId == $v->getId()) {
                return $v ;
            }
        }
        return false ;
    }

    protected function toZone($response)
    {
        return new Zone($response['domainId'], $response['domain'], 'master', null, 'transip');
    }

    protected function toRecord($response, Zone $zone)
    {
        $type = strtoupper($response->type);
        $name = $response->name;
        $id = $name . ':' . $type;
        return new Record($id, $name, $type, $response->content, $zone, 'transip', $response->expire);
    }

    private function formatRecord(ParameterBag $parameters)
    {
        $name = $parameters->get('name');
        $type = $parameters->get('type');
        $data = $parameters->get('data');
        $ttl = $parameters->get('ttl');

        $newRecord = [
            'type' => $type,
            'name' => $name,
            'data' => $data,
            'ttl' => (int)$ttl
        ];

        if ($type == RecordType::SRV) {
            $newRecord = $newRecord + [
                    'priority' => 1,
                    'service' => '',
                    'protocol' => '',
                    'port' => '',
                    'weight' => 1
                ];
        }

        return $newRecord;
    }
}
