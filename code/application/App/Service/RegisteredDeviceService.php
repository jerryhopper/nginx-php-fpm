<?php


namespace App\Service;


use App\Database\Models\RegisteredDevice;
use App\Database\Models\UnregisteredDevice;
use App\Database\Schemas\RegisteredDeviceSchema;
use App\Database\Schemas\UnregisteredDeviceSchema;
use Illuminate\Support\Carbon;

class RegisteredDeviceService
{

    public function getIpsFromDb($ownerId){
        return RegisteredDevice::where('id', $ownerId)->get();
    }

    public function setIpInDb($intIP,$owner,$deviceid){
        #deviceid,owner,intip
        return RegisteredDevice::updateOrCreate(['id'=>$deviceid, 'owner'=>$owner, 'int-ip'=>$intIP ]);
    }

    public function deleteStaleRecords(){
        RegisteredDevice::where('updated_at', '<' , Carbon::now()->subDay())->delete();
    }

    public function createTable (){
        return RegisteredDeviceSchema::create();
    }


    public function getHosts($ownerId){

        $array = array();
        foreach( RegisteredDevice::where('owner', $ownerId)->get() as $item){
            $array[] = str_replace(".","-",$item['int-ip']).".ssl.dockbox.nl";
        }
        return $array;
    }


}
