<?php


namespace App\Database\Schemas;

use Illuminate\Database\Capsule\Manager as Capsule;


class LocalDnsSchema
{
    function create(){

        Capsule::schema()->create('local_dns', function ($table) {
            $table->increments('id');
            $table->string('ip')->unique();
            $table->timestamps();
        });

    }
}
