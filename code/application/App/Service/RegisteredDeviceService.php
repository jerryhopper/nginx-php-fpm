<?php


namespace App\Service;


use App\Database\Models\RegisteredDevice;
use App\Database\Models\UnregisteredDevice;
use App\Database\Schemas\UnregisteredDeviceSchema;
use Illuminate\Support\Carbon;

class RegisteredDeviceService
{

    public function getIpsFromDb($ipadress){
        return RegisteredDevice::where('ext-ip', $ipadress)->get();
    }

    public function setIpInDb($intIP,$extIP,$deviceid){
        return RegisteredDevice::updateOrCreate([    'id' => $extIP."-".$intIP,    'ext-ip' => $extIP,    'int-ip' => $intIP, 'deviceid' => $deviceid ]);
    }

    public function deleteStaleRecords(){
        RegisteredDevice::where('updated_at', '<' , Carbon::now()->subDay())->delete();
    }

    public function createTable (){
        return RegisteredDevice::create();
    }


    public function getHosts($ipadress){

        $array = array();
        foreach( RegisteredDevice::where('ext-ip', $ipadress)->get() as $item){
            $array[] = str_replace(".","-",$item['int-ip']).".ssl.dockbox.nl";
        }
        return $array;
    }


}
