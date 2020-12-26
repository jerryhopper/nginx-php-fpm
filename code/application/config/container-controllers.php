<?php

declare(strict_types=1);

use App\Controllers\Api\LocalDnsController;
use App\Controllers\Api\RegisteredDeviceController;
use App\Controllers\Api\StatusController;
use App\Controllers\Api\UnregisteredDeviceController;
use App\Controllers\ExceptionDemoController;
use App\Controllers\HelloController;
use App\Controllers\HomeController;
use App\Controllers\LoginController;
use App\Controllers\TestController;
use App\Preferences;


use App\Runtime;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Slim\Views\Twig;

use League\OAuth2\Client\Provider\GenericProvider;
use Symfony\Component\HttpFoundation\Session\Session;

use JerryHopper\OAuth2\Client\Provider\FusionAuth;

use Illuminate\Database\Capsule\Manager as Capsule;

return array(
    ExceptionDemoController::class => function (ContainerInterface $container): ExceptionDemoController {
        return new ExceptionDemoController();
    },

    StatusController::class => function (ContainerInterface $container): StatusController {
        return new StatusController(
            $container->get(Twig::class),
            $container->get(Runtime::class),
            $container->get(Capsule::class)
        );
    },

    /*
    TestController::class => function (ContainerInterface $container): TestController {
        error_log("TestController::class | return new TestController(...)");
        return new TestController(
            $container->get(Twig::class),
            $container->get(Runtime::class)
        );
    },









/*
    TestController::class => function (ContainerInterface $container): TestController {
        return new TestController(
            $container->get(Twig::class),
            $container->get(Preferences::class),
            $container->get(Session::class),
            $container->get(FusionAuth::class)
        );
    },


    LocalDnsController::class => function (ContainerInterface $container): LocalDnsController {
        return new LocalDnsController(
            $container->get(Twig::class),
            $container->get(Preferences::class),
            $container->get(Session::class),
            $container->get(FusionAuth::class),
            $container->get(Capsule::class)
        );
    },

    UnregisteredDeviceController::class => function (ContainerInterface $container): UnregisteredDeviceController {
        return new UnregisteredDeviceController(
            $container->get(Twig::class),
            $container->get(Preferences::class),
            $container->get(Session::class),
            $container->get(FusionAuth::class),
            $container->get(Capsule::class)
        );
    },
    RegisteredDeviceController::class => function (ContainerInterface $container): RegisteredDeviceController {
        return new RegisteredDeviceController(
            $container->get(Twig::class),
            $container->get(Preferences::class),
            $container->get(Session::class),
            $container->get(FusionAuth::class),
            $container->get(Capsule::class)
        );
    },


    /*
HelloController::class => function (ContainerInterface $container): HelloController {
    return new HelloController(
        $container->get(Twig::class),
        $container->get(LoggerInterface::class),
        $container->get(Session::class),
        $container->get(FusionAuth::class)
    );
},* /

    LoginController::class => function (ContainerInterface $container): LoginController {
        return new LoginController(
            $container->get(Twig::class),
            $container->get(Preferences::class),
            $container->get(Session::class),
            $container->get(FusionAuth::class)
        );
    },
*/


    /*,

    OauthCallbackController::class => function (ContainerInterface $container): OauthCallbackController {
        return new OauthCallbackController(
            $container->get(Twig::class),
            $container->get(Preferences::class),
            $container->get(Session::class),
            $container->get(FusionAuth::class)
        );
        //,$container->get(OauthclientProvider::class)
        //,$container->get(GenericProvider::class)
        //
    } */


);
