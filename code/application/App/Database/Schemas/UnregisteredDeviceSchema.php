<?php


namespace App\Database\Schemas;


use Illuminate\Database\Capsule\Manager as Capsule;

class UnregisteredDeviceSchema
{
    function create(){

        Capsule::schema()->create('unregistered_devices', function ($table) {
            $table->string('id')->unique();
            $table->string('ext-ip');
            $table->string('int-ip');
            $table->string('deviceid');
            $table->timestamps();
        });

    }
}
