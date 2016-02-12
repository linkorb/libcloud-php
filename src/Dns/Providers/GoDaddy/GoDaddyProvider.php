<?php

namespace LibCloud\Dns\Providers\GoDaddy;

use LibCloud\Dns\Base;
use LibCloud\Dns\Model\Record;
use LibCloud\Dns\Model\RecordType;
use LibCloud\Dns\Model\Zone;
use Symfony\Component\HttpFoundation\ParameterBag;
use GuzzleHttp\Client;

class GoDaddyProvider extends Base
{
    private $apiUrl = 'https://api.godaddy.com/{version}/';
    private $httpClient;

    public function __construct($accessToken, $accessTokenSecret)
    {
        parent::__construct($accessToken, $accessTokenSecret);
        $this->httpClient = new Client([
            'base_url' => [$this->apiUrl, ['version' => 'v1']],
            'defaults' => [
                'headers' => [
                    'Authorization' => 'sso-key ' . $this->accessToken . ':' . $this->accessTokenSecret,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ]
            ]
        ]);
    }

    public function listZones()
    {
        try {
            $response = $this->httpClient->get('domains');
            if ($response->getStatusCode() == 200) {
                return array_map([$this, 'toZone'], json_decode($response->getBody(), true));
            }
        } catch (\Exception $e) {
            return [];
        }
    }

    public function listRecords(Zone $zone)
    {
        try {
            $response = $this->httpClient->get("domains/{$zone->getDomain()}/records");
            if ($response->getStatusCode() == 200) {
                $response = json_decode($response->getBody(), true);
                $records = [];
                foreach ($response as $r) {
                    $records[] = $this->toRecord($r, $zone);
                }
                return $records;
            }
        } catch (\Exception $e) {
            return [];
        }
    }

    public function createRecord(Zone $zone, ParameterBag $parameters)
    {
        try {
            $newRecord = $this->formatRecord($parameters);
            $this->httpClient->patch("domains/{$zone->getDomain()}/records/", ['body' => json_encode([$newRecord])]);

            $id = $newRecord['name'] . ':' . $newRecord['type'];
            return new Record($id, $newRecord['name'], $newRecord['type'], $newRecord['data'], $zone, 'go_daddy',
                $newRecord['ttl']);
        } catch (\Exception $e) {

        }
    }

    public function getZone($zoneDomain = null)
    {
        try {
            $response = $this->httpClient->get("domains/{$zoneDomain}");
            if ($response->getStatusCode() == 200) {
                return $this->toZone(json_decode($response->getBody(), true));
            }
        } catch (\Exception $e) {

        }
    }

    public function updateRecord(Record $record, ParameterBag $parameters)
    {
        try {
            $newRecord = $this->formatRecord($parameters);
            $this->httpClient->put("domains/{$record->getZone()->getDomain()}/records/{$record->getType()}/{$record->getName()}",
                ['body' => json_encode([$newRecord])]);

            $id = $newRecord['name'] . ':' . $newRecord['type'];
            return new Record($id, $newRecord['name'], $newRecord['type'], $newRecord['data'], $record->getZone(),
                'go_daddy', $newRecord['ttl']);
        } catch (\Exception $e) {
        }
    }

    public function putRecords(Zone $zone, ParameterBag $parameters)
    {
        try {
            $records = [];
            foreach ($parameters->get('records') as $r)
            {
                $records[] = $this->formatRecord($r);
            }
            $this->httpClient->put("domains/{$zone->getDomain()}/records",
                ['body' => json_encode($records)]);
        } catch (\Exception $e) {
        }
    }

    public function getRecord(Zone $zone, $recordId)
    {
        try {
            $recordId = explode(':', $recordId);
            $response = $this->httpClient->get("domains/{$zone->getDomain()}/records/{$recordId[1]}/{$recordId[0]}");
            if ($response->getStatusCode() == 200) {
                return $this->toRecord(json_decode($response->getBody(), true)[0], $zone);
            }
        } catch (\Exception $e) {
        }
    }

    protected function toZone($response)
    {
        return new Zone($response['domainId'], $response['domain'], 'master', null, 'go_daddy',
            $response);
    }

    protected function toRecord($response, Zone $zone)
    {
        $type = strtoupper($response['type']);
        $name = $response['name'];
        $id = $name . ':' . $type;
        return new Record($id, $name, $type, $response['data'], $zone, 'go_daddy', $response['ttl']);
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
