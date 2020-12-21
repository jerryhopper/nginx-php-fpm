<?php


namespace App\Database\Schemas;


use Illuminate\Database\Capsule\Manager as Capsule;

class RegisteredDeviceSchema
{
    function create(){
        Capsule::schema()->create('registered_devices', function ($table) {
            $table->string('id')->unique(); // deviceID
            $table->string('owner');
            $table->string('int-ip');
            $table->timestamps();
        });

    }
}
