<?php

namespace App\ProjectCode;


class CfLocalDns {

    protected $zoneid;
    protected $dns;
    protected $zones;

    function __construct($apitoken,$zoneid)
    {
        $this->zoneid = $zoneid;
        $key     = new Cloudflare\API\Auth\APIToken ( $apitoken);
        $adapter = new Cloudflare\API\Adapter\Guzzle($key);
        $this->zones    = new Cloudflare\API\Endpoints\Zones($adapter);
        $this->dns    = new Cloudflare\API\Endpoints\DNS($adapter);

    }
    private function testIp($ipadress){
        if (!filter_var($ipadress, FILTER_VALIDATE_IP))  {
            throw new Exception( "$ipadress is not a valid IP address" );
        }
        if (filter_var($ipadress, FILTER_FLAG_NO_PRIV_RANGE ))  {
            throw new Exception( "$ipadress is outside PRIV_RANGE" );
        }
        if ( explode(".",$ipadress)[0]=="127" ){
            throw new Exception( "$ipadress is in a reserved range (127.0.0.0–127.255.255.255)" );
        }

        return $ipadress;
    }

    function addPrivateIp($ipadress){
        $this->testIp($ipadress);
        $dashedhostname = str_replace(".","-", $ipadress).".ssl";
        $this->dns->addRecord($this->zoneid, "A", $dashedhostname, $ipadress,0,false, $priority = '', $data = [] );
    }

    function listPrivateIp($ipadress){
        $this->testIp($ipadress);

        $dashedhostname = str_replace(".","-", $ipadress).".ssl";

        $x= $this->dns->listRecords($this->zoneid, '','', $ipadress);

        if(count($x->result) >0 ) {
            return $x->result[0];
        }
        throw new Exception("No results");

    }

    function deletePrivateIp($ipadress){
        $this->testIp($ipadress);

        $record = $this->listPrivateIp($ipadress);
        #$record->id

        $res = $this->dns->deleteRecord($this->zoneid,$record->id);
        return $res;
    }
}
