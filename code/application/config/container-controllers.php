<?php

declare(strict_types=1);

use App\Controllers\Api\LocalDnsController;
use App\Controllers\ExceptionDemoController;
use App\Controllers\HelloController;
use App\Controllers\HomeController;
use App\Controllers\LoginController;
use App\Preferences;


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
    LocalDnsController::class => function (ContainerInterface $container): LocalDnsController {
        return new LocalDnsController(
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
},*/

    LoginController::class => function (ContainerInterface $container): LoginController {
        return new LoginController(
            $container->get(Twig::class),
            $container->get(Preferences::class),
            $container->get(Session::class),
            $container->get(FusionAuth::class)
        );
    },

    HomeController::class => function (ContainerInterface $container): HomeController {
        return new HomeController(
            $container->get(Twig::class),
            $container->get(Preferences::class),
            $container->get(Session::class),
            $container->get(FusionAuth::class)
        );
    }

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
