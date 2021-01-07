<?php


namespace App\Service;


use App\Database\Models\UnregisteredDevice;
use App\Database\Schemas\UnregisteredDeviceSchema;
use Illuminate\Support\Carbon;

class UnregisteredDeviceService
{

    public function getIpsFromDb($ipadress){
        return UnregisteredDevice::where('ext-ip', $ipadress)->get();
    }

    public function setIpInDb($intIP,$extIP,$deviceid){
        error_log("extIP $extIP");
        return UnregisteredDevice::updateOrCreate(['id' => $extIP."-".$intIP  ],[     'ext-ip' => $extIP,    'int-ip' => $intIP, 'deviceid' => $deviceid ]);
    }

    public function deleteStaleRecords(){
        UnregisteredDevice::where('updated_at', '<' , Carbon::now()->subDay())->delete();
    }

    public function createTable (){
        return UnregisteredDeviceSchema::create();
    }


    public function getHosts($ipadress){

        $array = array();
        foreach( UnregisteredDevice::where('ext-ip', $ipadress)->get() as $item){
            $array[] = str_replace(".","-",$item['int-ip']).".ssl.dockbox.nl";
        }
        return $array;
    }


}
