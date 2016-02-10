<?php

namespace LibCloud\Dns\Model;

class RecordType
{
    const A = 'A';
    const AAAA = 'AAAA';
    const ALIAS = 'ALIAS';
    const MX = 'MX';
    const NS = 'NS';
    const CNAME = 'CNAME';
    const DNAME = 'DNAME';
    const HINFO = 'HINFO';
    const TXT = 'TXT';
    const PTR = 'PTR';
    const SOA = 'SOA';
    const SPF = 'SPF';
    const SRV = 'SRV';
    const SSHFP = 'SSHFP';
    const RP = 'RP';
    const NAPTR = 'NAPTR';
    const REDIRECT = 'REDIRECT';
    const GEO = 'GEO';
    const URL = 'URL';
    const WKS = 'WKS';
    const LOC = 'LOC';

    public static function getList()
    {
        $class = new \ReflectionClass(__CLASS__);
        return $class->getConstants();
    }
}
